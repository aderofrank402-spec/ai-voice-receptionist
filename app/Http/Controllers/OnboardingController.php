<?php

namespace App\Http\Controllers;

use App\Models\OnboardingSession;
use App\Services\InterviewerAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use App\Services\ComplianceAgent;
use App\Services\ProcessAgent;
use App\Services\PromptGeneratorAgent; // The Builder
use App\Models\Agent;
use App\Services\AgentContextService; // The Brain
use App\Models\BusinessProfile;

class OnboardingController extends Controller
{
    protected $agent;
    protected $compliance;
    protected $process;
    protected $prompter;
    protected $contextService;

    public function __construct(
        InterviewerAgent $agent,
        ComplianceAgent $compliance,
        ProcessAgent $process,
        PromptGeneratorAgent $prompter,
        AgentContextService $contextService
    ) {
        $this->agent = $agent;
        $this->compliance = $compliance;
        $this->process = $process;
        $this->prompter = $prompter;
        $this->contextService = $contextService;
    }

    /**
     * Display the onboarding interface (The Builder Agent).
     */
    public function index(): View
    {
        // Find or create a session for the current user
        $session = OnboardingSession::firstOrCreate(
            ['user_id' => Auth::id()],
            ['status' => 'active', 'transcript' => []]
        );

        return view('onboarding.index', ['history' => $session->transcript ?? []]);
    }

    /**
     * Handle chat messages from the user.
     */
    public function chat(Request $request): JsonResponse
    {
        // Increase time limit for Deep Scraping (5 minutes)

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Auto-recover session if it was deleted (e.g. by truncation) logic
        $session = OnboardingSession::firstOrCreate(
            ['user_id' => Auth::id()],
            ['status' => 'active', 'transcript' => []]
        );

        try {
            // SILENT MODE: Bypass Agent for URLs
            // User wants "System" behavior, not "Chatbot" behavior for raw data
            // SILENT MODE: Bypass Agent for URLs
            // Check for loose URL existence (http, https, or www)
            if (preg_match('/(https?:\/\/[^\s]+)|(www\.[^\s]+)/i', $request->message, $matches)) {
                $url = $matches[0];
                if (!str_starts_with($url, 'http')) {
                    $url = 'https://' . $url;
                }

                if ($url) {
                    \App\Jobs\ScrapeWebsiteJob::dispatch($url, $session->id);
                    // Return special flag or empty reply to trigger frontend terminal focus
                    return response()->json([
                        'reply' => '', // Silent
                        'silent_start' => true
                    ]);
                }
            }

            $reply = $this->agent->reply($session, $request->message);

            if (str_contains($reply, '[INTERVIEW_COMPLETE]')) {
                // 1. Run Compliance Check (Gatekeeper)
                $validation = $this->compliance->validate($session->fresh()->transcript ?? []);

                if (!$validation['approved']) {
                    // REJECTED: Turn the car around
                    // Inject system refusal and ask AI to clarify with user
                    $refusalNote = "COMPLIANCE ALERT: The interview cannot be completed yet. Reason: " . $validation['reason'] . ". Ask the user to clarify or fix this.";

                    $followUp = $this->agent->continueConversation($session, $refusalNote);

                    return response()->json(['reply' => $followUp]);
                }

                // APPROVED: Proceed to Process Agent (Data Transformer)
                // Extract Blueprint
                $blueprint = $this->process->extractBlueprint($session->fresh()->transcript ?? []);

                $session->update([
                    'status' => 'review_pending',
                    'extracted_data' => $blueprint
                ]);

                // SECURITY: Strip magic string server-side and return a flag
                return response()->json([
                    'reply' => str_replace('[INTERVIEW_COMPLETE]', '', $reply),
                    'completed' => true,
                    'redirect' => route('onboarding.review')
                ]);
            }

            return response()->json(['reply' => $reply]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Chat Error: ' . $e->getMessage());
            // Return JSON error so the frontend doesn't choke on HTML "Unexpected token"
            return response()->json(['error' => 'AI Service Temporarily Unavailable. Please try again or skip the link.'], 200);
        }
    }

    /**
     * Get the current session status (checklist + partial data).
     * Note: Real-time crawler updates now use WebSocket broadcasting.
     */
    public function status(Request $request): JsonResponse
    {
        $session = OnboardingSession::where('user_id', Auth::id())->first();
        if (!$session) {
            return response()->json(['checklist' => [], 'partial_data' => []]);
        }

        // Get checklist data (operating hours, services, etc. discovered during crawl)
        $partialData = \Illuminate\Support\Facades\Cache::get("onboarding_data_{$session->id}", []);

        return response()->json([
            'checklist' => $session->checklist ?? [],
            'partial_data' => $partialData,
        ]);
    }

    /**
     * Display the configuration review page.
     */
    public function review()
    {
        $session = OnboardingSession::where('user_id', Auth::id())->firstOrFail();

        if (empty($session->extracted_data)) {
            return redirect()->route('onboarding.index');
        }

        return view('onboarding.review', ['data' => $session->extracted_data]);
    }

    /**
     * Display the manual onboarding form.
     */
    public function form(): View
    {
        return view('onboarding.form');
    }

    /**
     * Handle manual form submission.
     */
    public function submitForm(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'services' => 'required|string',
            'pricing' => 'required|string',
            'hours' => 'required|string',
            'missed_call_action' => 'required|string|in:take_message,forward_call',
        ]);

        // Safety Audit
        // Convert to virtual transcript for consistency
        $virtualTranscript = [
            ['role' => 'user', 'content' => "Business Name: {$validated['business_name']}, Services: {$validated['services']}, Pricing: {$validated['pricing']}, Hours: {$validated['hours']}"]
        ];

        $audit = $this->compliance->validate($virtualTranscript);

        if ($audit['approved'] === false) {
            return back()->withErrors(['business_name' => 'Compliance Error: ' . ($audit['reason'] ?? 'Policy Violation')])->withInput();
        }

        // If safe, verify status is 'verified' (or just completed for now, but strictly verified)
        // We will stick to 'completed' for now as per previous plan, but adding a note.

        $session = OnboardingSession::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'status' => 'completed',
                // Store as JSON in the extracted_data column
                'extracted_data' => $validated,
                // Append a manual entry note to the transcript
                'transcript' => array_merge(
                    $session->transcript ?? [],
                    [['role' => 'system', 'content' => 'User completed onboarding via Manual Form. Verified Safe.']]
                )
            ]
        );

        // Prepare structured data for BusinessProfile
        // Manual form data is simple string, we wrap it in basic structure
        $servicesArray = [['name' => 'Services (Manual Entry)', 'price' => $validated['pricing']]];
        $contactsArray = ['phone' => null, 'email' => null]; // Manual form doesn't ask for contacts yet?

        // Sync to BusinessProfile (The Single Source of Truth)
        BusinessProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'active_crawl_version' => 'manual_v1',
                'business_name' => $validated['business_name'],
                'location' => null, // Not in form
                'hours_raw' => $validated['hours'],
                'hours_structured' => ['manual' => $validated['hours']],
                'contacts' => $contactsArray,
                'services' => $servicesArray,
                'confidence_score' => 1.0, // Manual input is trusted
                'needs_review' => false
            ]
        );

        return redirect()->route('dashboard')->with('status', 'Profile created successfully!');
    }

    /**
     * Finalize and Launch the Agent.
     */
    public function launch()
    {
        $session = OnboardingSession::where('user_id', Auth::id())->firstOrFail();

        if (empty($session->extracted_data)) {
            return redirect()->route('onboarding.index')->with('error', 'No configuration found.');
        }

        $config = $session->extracted_data;

        // CRITICAL SYNC: Ensure BusinessProfile exists before generating prompt (v5)
        \App\Models\BusinessProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'active_crawl_version' => 'ai_v1',
                'business_name'        => $config['business_name'] ?? 'My Business',
                'location'             => $config['location'] ?? null,
                'hours_raw'            => is_array($config['hours'] ?? null) ? json_encode($config['hours']) : ($config['hours'] ?? null),
                'hours_structured'     => is_array($config['hours'] ?? null) ? $config['hours'] : ['raw' => $config['hours'] ?? 'Consultation only'],
                'contacts'             => $config['contacts'] ?? [],
                'services'             => $config['services'] ?? [],
                'confidence_score'     => 0.95,
                'needs_review'         => false
            ]
        );

        // Compile the Prompt using the Enterprise Pipeline (Context Service)
        try {
            // This will now find the profile we just created/updated
            $systemPrompt = $this->contextService->getSystemPrompt(Auth::id());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Context Service failed, falling back to PromptGenerator: " . $e->getMessage());
            $systemPrompt = $this->prompter->generate($config);
        }

        // Save the Agent
        $agent = Agent::create([
            'user_id' => Auth::id(),
            'name' => $config['business_name'] ?? 'My Agent',
            'configuration' => $config,
            'system_prompt' => $systemPrompt,
            'status' => 'active',
        ]);

        // TRIGGER: Zadarma Phone Number Issuance (After Interview / Launch)
        try {
            $zadarma = app(\App\Services\ZadarmaService::class);
            $zadarma->provisionForUser(Auth::user());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Zadarma Provisioning Failed: " . $e->getMessage());
            // We don't block the launch, but it will show as 'error' in the integrations hub
        }

        // Optional: Mark session as archived or delete common transcript?
        // For now, we leave it in case they want to re-edit via chat later.

        return redirect()->route('dashboard')->with('success', 'Agent Deployed Successfully!');
    }
    /**
     * Reset the onboarding session.
     */
    public function reset(Request $request)
    {
        $session = OnboardingSession::where('user_id', Auth::id())->first();

        if ($session) {
            $session->update([
                'transcript' => [],
                'status' => 'active',
                'extracted_data' => null,
                'checklist' => [],
            ]);
        }

        return redirect()->route('onboarding.index')->with('status', 'Conversation reset.');
    }
}