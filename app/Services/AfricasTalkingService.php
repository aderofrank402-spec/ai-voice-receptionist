<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfricasTalkingService
{
    protected $username;
    protected $apiKey;
    protected $baseUrl;

    public function __construct($config = [])
    {
        // Prioritize: 1. Passed config, 2. DB Global, 3. Default env
        $this->username = $config['username'] ?? \App\Models\SystemCredential::getValue('at_username', env('AT_USERNAME'));
        $this->apiKey   = $config['api_key'] ?? \App\Models\SystemCredential::getValue('at_api_key', env('AT_API_KEY'));
        
        $this->baseUrl = "https://api.africastalking.com/version1/messaging";
    }

    /**
     * Send an SMS via Africa's Talking
     */
    public function sendSms($to, $message)
    {
        if (!$this->username || !$this->apiKey) {
            return ['error' => 'Africa\'s Talking credentials not configured.'];
        }

        $toFormat = $this->normalizePhone($to);

        $response = Http::timeout(5)->withHeaders([
                'ApiKey' => $this->apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ])
            ->asForm()
            ->post($this->baseUrl, [
                'username' => $this->username,
                'to'       => $toFormat,
                'message'  => $message,
            ]);

        if ($response->failed()) {
            Log::error("Africa's Talking SMS Failed: " . $response->body());
            return ['error' => 'Failed to send SMS', 'details' => $response->json()];
        }

        return $response->json();
    }

    /**
     * Normalize phone to E.164 (e.g., +2547XXXXXXXX)
     */
    protected function normalizePhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '7') || str_starts_with($phone, '1')) {
            $phone = '254' . $phone;
        }

        return "+" . $phone;
    }
}
