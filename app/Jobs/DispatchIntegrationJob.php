<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchIntegrationJob implements ShouldQueue
{
    use Queueable;

    public $callId;
    public $isSuccess;

    /**
     * Create a new job instance.
     */
    public function __construct(int $callId, bool $isSuccess)
    {
        $this->callId = $callId;
        $this->isSuccess = $isSuccess;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $call = \App\Models\Call::with('agent')->find($this->callId);
        if (!$call) return;

        $userId = $call->agent->user_id ?? null;
        if (!$userId) return;

        $integrations = \App\Models\Integration::where('user_id', $userId)->get();
        
        foreach ($integrations as $integration) {
            try {
                // 1. WhatsApp Notification - Only for Handled/Booked calls
                if ($integration->provider === 'whatsapp' && $this->isSuccess) {
                    $whatsAppService = new \App\Services\WhatsAppService(['user_id' => $userId]);
                    $whatsAppService->sendMessage(
                        $call->caller_number, 
                        "Hello! This is {$call->agent->name}. Your request for '{$call->outcome}' has been received and confirmed. Thank you for calling!"
                    );
                }

                // 2. HubSpot Lead Sync
                if ($integration->provider === 'hubspot') {
                    $hubSpotService = new \App\Services\HubSpotService($integration->access_token);
                    $hubSpotService->syncVoiceLead(
                        $call->caller_number,
                        'Voice Lead: ' . ($call->metadata['name'] ?? 'Anonymous'),
                        $call->outcome,
                        $call->metadata['summary'] ?? ''
                    );
                }
            } catch (\Exception $e) {
                \Log::error("Async Integration Failed [{$integration->provider}]: " . $e->getMessage());
            }
        }

        // 3. Global Event Registry (Webhook)
        $webhookUrl = $call->agent->configuration['integrations']['webhook_url'] ?? null;
        if ($webhookUrl) {
            try {
                // Even in a job, we keep the timeout for network resilience
                \Illuminate\Support\Facades\Http::timeout(10)->post($webhookUrl, [
                    'event' => 'call.completed',
                    'timestamp' => now()->toIso8601String(),
                    'data' => [
                        'call_id' => $call->id,
                        'agent_name' => $call->agent->name,
                        'customer_number' => $call->caller_number,
                        'outcome' => $call->outcome,
                        'summary' => $call->metadata['summary'] ?? '',
                        'transcript' => $call->transcript ?? '',
                        'duration' => $call->duration,
                    ]
                ]);
            } catch (\Exception $e) {
                \Log::warning("Async Event Broadcast Failed: " . $e->getMessage());
            }
        }
    }
}
