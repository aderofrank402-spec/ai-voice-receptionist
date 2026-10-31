<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $sid;
    protected $token;
    protected $from;
    protected $baseUrl;

    public function __construct($config = [])
    {
        // 1. Get Base Credentials (System Level)
        $this->sid   = $config['sid'] ?? \App\Models\SystemCredential::getValue('twilio_sid', env('TWILIO_SID'));
        $this->token = $config['token'] ?? \App\Models\SystemCredential::getValue('twilio_token', env('TWILIO_TOKEN'));

        // 2. Identify the Dynamic "From" Number (User Level Integration)
        $userId = $config['user_id'] ?? null;
        if ($userId) {
            $integration = \App\Models\Integration::where('user_id', $userId)
                ->where('provider', 'whatsapp')
                ->first();
            
            if ($integration && !empty($integration->settings['business_number'])) {
                $this->from = $integration->settings['business_number'];
            }
        }

        // Fallback to Global
        if (empty($this->from)) {
            $this->from = $config['from'] ?? \App\Models\SystemCredential::getValue('twilio_from', env('TWILIO_WHATSAPP_NUMBER'));
        }
        
        $this->baseUrl = "https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json";
    }

    /**
     * Send a WhatsApp message
     */
    public function sendMessage($to, $message)
    {
        if (!$this->sid || !$this->token) {
            return ['error' => 'Twilio credentials not configured.'];
        }

        $toFormat = $this->normalizePhone($to);
        $fromFormat = "whatsapp:" . $this->from;

        $response = Http::timeout(5)->asForm()
            ->withBasicAuth($this->sid, $this->token)
            ->post($this->baseUrl, [
                'To'   => "whatsapp:" . $toFormat,
                'From' => $fromFormat,
                'Body' => $message,
            ]);

        if ($response->failed()) {
            Log::error("WhatsApp Send Failed: " . $response->body());
            return ['error' => 'Failed to send message', 'details' => $response->json()];
        }

        return $response->json();
    }

    /**
     * Normalize phone to E.164 WITHOUT the 'whatsapp:' prefix
     */
    protected function normalizePhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '7') || str_starts_with($phone, '1')) {
            $phone = '254' . $phone;
        }

        return $phone;
    }
}
