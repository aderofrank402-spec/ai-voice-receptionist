<?php

namespace App\Services;

use App\Models\User;
use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZadarmaService
{
    protected $baseUrl = 'https://api.zadarma.com/v1/';
    protected $key;
    protected $secret;

    public function __construct()
    {
        $this->key    = config('services.zadarma.key', env('ZADARMA_KEY'));
        $this->secret = config('services.zadarma.secret', env('ZADARMA_SECRET'));
    }

    /**
     * Provision or Sync a phone number for the user after the interview.
     */
    public function provisionForUser(User $user)
    {
        // 1. Check if user already has a Zadarma integration
        $integration = Integration::firstOrNew([
            'user_id' => $user->id,
            'provider' => 'zadarma'
        ]);

        if ($integration->status === 'active' && !empty($integration->provider_id)) {
            Log::info("Zadarma: User {$user->id} already has an active number.");
            return $integration;
        }

        // 2. Logic: Issue a new number or link an existing one
        // For now, we will simulate the issuance and update the integration.
        // In a real implementation, we would call Zadarma API: /v1/tariff/ (to check balance) and /v1/sip/
        
        Log::info("Zadarma: Provisioning number for User {$user->id} after completion.");

        $integration->fill([
            'provider_id' => 'ZAD-' . strtoupper(bin2hex(random_bytes(4))),
            'status' => 'active',
            'settings' => [
                'phone_number' => '+254' . rand(700000000, 799999999),
                'sip_id' => 'sip_' . rand(1000, 9999),
                'provisioned_at' => now()->toDateTimeString(),
            ],
            'last_synced_at' => now(),
        ]);

        $integration->save();

        return $integration;
    }

    /**
     * Get the current status from Zadarma API (Heartbeat).
     */
    public function getStatus(Integration $integration)
    {
        // Placeholder for real API call to Zadarma to check SIP status
        return 'healthy';
    }
}
