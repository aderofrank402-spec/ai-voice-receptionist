<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Agent;
use App\Services\PromptGeneratorAgent;

class AgentController extends Controller
{
    protected $prompter;

    public function __construct(PromptGeneratorAgent $prompter)
    {
        $this->prompter = $prompter;
    }

    /**
     * Show the detailed configuration editor.
     */
    public function edit()
    {
        $user = Auth::user();
        $agent = Agent::where('user_id', $user->id)->firstOrFail();

        return view('agents.edit', ['agent' => $agent]);
    }

    /**
     * Update the agent configuration directly.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $agent = Agent::where('user_id', $user->id)->firstOrFail();

        // 1. Validate Input
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|string|max:255',
            'agent_name' => 'required|string|max:100',
            'tone' => 'required|in:polite_admin,direct_expert',
            'language' => 'required|in:en,sw,sheng',
            'voice_id' => 'nullable|string|max:100',
            'hours' => 'required|string|max:1000',
            'services' => 'required|array',
            'services.*.name' => 'required|string|max:255',
            'services.*.price' => 'nullable|numeric|min:0',
            'services.*.description' => 'nullable|string|max:500',
            'missed_call_action' => 'required|in:voicemail,forward',
            'forward_number' => 'nullable|required_if:missed_call_action,forward|string|max:20',
        ]);

        // 2. Re-construct the Configuration JSON
        $newConfig = $agent->configuration ?? [];
        $newConfig['business_name'] = $validated['business_name'];
        $newConfig['business_type'] = $validated['business_type'];
        
        // Persona Data
        $newConfig['persona'] = [
            'agent_name' => $validated['agent_name'],
            'tone' => $validated['tone'],
            'language' => $validated['language'],
            'voice_id' => $validated['voice_id'] ?? null,
        ];

        $newConfig['hours'] = [
            'type' => 'custom', // Simplified for manual edit
            'schedule' => $validated['hours']
        ];

        // Re-structure services
        $newConfig['services'] = [];
        foreach ($validated['services'] as $svc) {
            $newConfig['services'][] = [
                'name' => $svc['name'],
                'price' => $svc['price'] ?? 0,
                'price_type' => 'fixed', // Defaulting to fixed for manual edits
                'description' => $svc['description'] ?? ''
            ];
        }

        // Missed Call Action
        $newConfig['missed_call_action'] = [
            'type' => $validated['missed_call_action'] === 'forward' ? 'forward_call' : 'save_message',
            'forward_number' => $validated['forward_number'] ?? null
        ];

        // 3. Regenerate System Prompt
        // The "Brain" needs to be updated when knowledge changes
        $newSystemPrompt = $this->prompter->generate($newConfig);

        // 4. Save to DB
        $oldName = $agent->name;
        $agent->update([
            'name' => $validated['business_name'],
            'configuration' => $newConfig,
            'system_prompt' => $newSystemPrompt
        ]);

        // Sync with Knowledge Base Core Source
        \App\Models\KnowledgeSource::updateOrCreate(
            ['agent_id' => $agent->id, 'type' => 'core'],
            [
                'user_id' => $user->id,
                'title' => 'Core Business Knowledge',
                'content' => json_encode($newConfig),
                'status' => 'ready',
                'metadata' => ['source' => 'manual_edit']
            ]
        );

        \App\Support\Audit::log('agent_modified', $agent, [
            'old_name' => $oldName,
            'new_name' => $validated['business_name'],
            'action' => 'Brain/Knowledge updated'
        ]);

        return redirect()->route('dashboard')->with('success', 'Knowledge Base updated successfully.');
    }

    /**
     * Show the Test Agent Playground.
     */
    public function test()
    {
        $user = Auth::user();
        $agent = Agent::where('user_id', $user->id)->firstOrFail();

        return view('agents.test', ['agent' => $agent]);
    }

    /**
     * Handle chat messages for the test playground.
     */
    public function chat(Request $request)
    {
        $user = Auth::user();
        $agent = Agent::where('user_id', $user->id)->firstOrFail();

        // Validate Input
        $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array'
        ]);

        $client = \App\Support\HttpClientFactory::openAiClient(timeout: 15);

        // Construct Messages
        $messages = [
            ['role' => 'system', 'content' => $agent->system_prompt]
        ];

        // Append history
        if ($request->history) {
            foreach ($request->history as $msg) {
                $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
            }
        }

        // Add user message
        $messages[] = ['role' => 'user', 'content' => $request->message];

        // Call OpenAI
        try {
            $response = $client->chat()->create([
                'model' => 'gpt-4o', // Use high quality for simulation
                'messages' => $messages,
                'temperature' => 0.6,
                'max_tokens' => 300,
            ]);

            $reply = $response->choices[0]->message->content;

            return response()->json(['reply' => $reply]);
        } catch (\Exception $e) {
            \Log::error('Test Chat Error: ' . $e->getMessage());
            return response()->json(['error' => 'AI Error: ' . $e->getMessage()], 500);
        }
    }
}
