<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Agent;
use App\Models\KnowledgeSource;
use App\Services\PromptGeneratorAgent;
use Illuminate\Support\Facades\Storage;

class KnowledgeBaseController extends Controller
{
    /**
     * Show the main Knowledge Hub.
     */
    public function index()
    {
        $user = Auth::user();
        $agent = Agent::where('user_id', $user->id)->firstOrFail();
        
        // Auto-sync onboarding data to KB if this is the first time
        $this->ensureCoreKnowledgeSynced($agent);

        $sources = $agent->knowledgeSources()->orderBy('created_at', 'desc')->get();
        
        return view('agent.knowledge', [
            'agent' => $agent,
            'sources' => $sources
        ]);
    }

    /**
     * Store a new knowledge source.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $agent = Agent::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'type' => 'required|in:file,link,snippet',
            'title' => 'required|string|max:255',
            'file' => 'required_if:type,file|file|mimes:pdf,txt,docx|max:10240',
            'url' => 'required_if:type,link|url',
            'content' => 'required_if:type,snippet|string'
        ]);

        $filePath = null;
        $extractedContent = $request->content;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('knowledge_sources', 'public');
            
            if ($file->getClientOriginalExtension() === 'pdf') {
                try {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile(Storage::disk('public')->path($filePath));
                    $extractedContent = $pdf->getText();
                } catch (\Exception $e) {
                    \Log::error("PDF Extraction Failed [{$filePath}]: " . $e->getMessage());
                    $extractedContent = "Content could not be indexed automatically. Error: " . $e->getMessage();
                }
            } else {
                $extractedContent = file_get_contents($file->getRealPath());
            }
        }

        $source = $agent->knowledgeSources()->create([
            'user_id' => $user->id,
            'agent_id' => $agent->id,
            'type' => $request->type,
            'title' => $request->title,
            'content' => $extractedContent,
            'file_path' => $filePath,
            'status' => 'ready',
            'metadata' => [
                'url' => $request->url,
                'original_name' => $request->hasFile('file') ? $request->file('file')->getClientOriginalName() : null,
                'indexed_at' => now()->toIso8601String()
            ]
        ]);

        $this->refreshAgentPrompt($agent);

        return back()->with('success', "'{$request->title}' indexed successfully.");
    }

    /**
     * Delete a source.
     */
    public function destroy(KnowledgeSource $source)
    {
        if ($source->user_id !== Auth::id()) {
            abort(403);
        }

        if ($source->file_path) {
            Storage::disk('public')->delete($source->file_path);
        }

        $agent = $source->agent;
        $source->delete();

        $this->refreshAgentPrompt($agent);

        return back()->with('success', 'Source removed.');
    }

    /**
     * Internal helper to sync onboarding data to the new KB table.
     */
    private function ensureCoreKnowledgeSynced(Agent $agent)
    {
        $hasCore = $agent->knowledgeSources()->where('type', 'core')->exists();
        
        if (!$hasCore) {
            $agent->knowledgeSources()->create([
                'user_id' => $agent->user_id,
                'agent_id' => $agent->id,
                'type' => 'core',
                'title' => 'Core Business Knowledge',
                'content' => "Summary of configuration: " . json_encode($agent->configuration),
                'status' => 'ready',
                'metadata' => ['source' => 'onboarding']
            ]);
        }
    }

    /**
     * Re-generate the full system prompt based on ALL sources.
     */
    private function refreshAgentPrompt(Agent $agent)
    {
        $prompter = app(PromptGeneratorAgent::class);
        
        $knowledge = $agent->knowledgeSources()
            ->where('status', 'ready')
            ->get()
            ->map(function ($source) {
                return [
                    'title' => $source->title,
                    'content' => mb_strimwidth($source->content, 0, 2000, '...') 
                ];
            })
            ->toArray();
        
        $payload = $agent->configuration;
        $payload['knowledge'] = $knowledge;

        $agent->update([
            'system_prompt' => $prompter->generate($payload)
        ]);
    }
}
