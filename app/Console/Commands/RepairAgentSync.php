<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Agent;
use App\Models\BusinessProfile;
use App\Models\OnboardingSession;
use App\Services\AgentContextService;

class RepairAgentSync extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'agent:repair-sync';

    /**
     * The console command description.
     */
    protected $description = 'Repairs agents that were launched without a synced business profile.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting Agent Repair Sync...");

        // Find agents that have the error message in their prompt
        $agents = Agent::where('system_prompt', 'like', '%No business profile found%')->get();

        if ($agents->isEmpty()) {
            $this->info("No broken agents found.");
            return;
        }

        $this->info("Found " . $agents->count() . " broken agents.");

        foreach ($agents as $agent) {
            $this->info("Repairing Agent: {$agent->name} (User ID: {$agent->user_id})");

            // 1. Find Onboarding Session data
            $session = OnboardingSession::where('user_id', $agent->user_id)->first();

            if (!$session || empty($session->extracted_data)) {
                $this->error("  No onboarding data found for user {$agent->user_id}. Skipping.");
                continue;
            }

            $config = $session->extracted_data;

            // 2. Create the missing BusinessProfile
            BusinessProfile::updateOrCreate(
                ['user_id' => $agent->user_id],
                [
                    'active_crawl_version' => 'ai_v1_repair',
                    'business_name'        => $config['business_name'] ?? $agent->name,
                    'location'             => $config['location'] ?? null,
                    'hours_raw'            => is_array($config['hours'] ?? null) ? json_encode($config['hours']) : ($config['hours'] ?? null),
                    'hours_structured'     => is_array($config['hours'] ?? null) ? $config['hours'] : ['raw' => $config['hours'] ?? 'Consultation only'],
                    'contacts'             => $config['contacts'] ?? [],
                    'services'             => $config['services'] ?? [],
                    'confidence_score'     => 1.0,
                    'needs_review'         => false
                ]
            );

            $this->info("  Success: BusinessProfile created.");

            // 3. Refresh Agent System Prompt
            $contextService = app(AgentContextService::class);
            $newPrompt = $contextService->getSystemPrompt($agent->user_id);

            $agent->update([
                'system_prompt' => $newPrompt
            ]);

            $this->info("  Success: Agent System Prompt updated.");
        }

        $this->info("Repair Complete.");
    }
}
