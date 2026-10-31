<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MpesaService
{
    protected $baseUrl;
    protected $consumerKey;
    protected $consumerSecret;
    protected $passkey;
    protected $shortcode;
    protected $apiKey; // Added for IntaSend

    public function __construct($config = [])
    {
        $mode = $config['mpesa_setup_mode'] ?? 'aggregator';
        $userId = $config['user_id'] ?? null;

        // 1. Load User-Specific Integration if available
        $userConfig = [];
        if ($userId) {
            $integration = \App\Models\Integration::where('user_id', $userId)
                ->where('provider', 'mpesa')
                ->first();
            
            if ($integration && !empty($integration->settings)) {
                $userConfig = $integration->settings;
                $mode = $userConfig['mpesa_setup_mode'] ?? $mode;
            }
        }

        $this->baseUrl = ($mode === 'aggregator')
            ? (config('services.mpesa.env') === 'live' ? 'https://payment.intasend.com/api/v1' : 'https://sandbox.intasend.com/api/v1')
            : (config('services.mpesa.env') === 'live' ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke');

        // Prefer User-specific keys, fallback to Global 
        $this->consumerKey    = $userConfig['consumer_key']    ?? config('services.mpesa.key');
        $this->consumerSecret = $userConfig['consumer_secret'] ?? config('services.mpesa.secret');
        $this->passkey        = $userConfig['passkey']         ?? config('services.mpesa.passkey');
        $this->shortcode      = $userConfig['shortcode']       ?? config('services.mpesa.shortcode');
        
        if ($mode === 'aggregator') {
            $this->apiKey        = env('INTASEND_PUBLISHABLE_KEY');
            $this->apiToken      = env('INTASEND_SECRET_TOKEN');
        } else {
            $this->apiKey        = null; 
            $this->apiToken      = null;
        }
    }

    /**
     * Entry point to initiate payment based on provider
     */
    public function stkPush($phone, $amount, $reference, $description = 'Voice Agent Payment')
    {
        if ($this->apiKey) {
            return $this->initiateIntasendStkPush($phone, $amount, $reference);
        }
        return $this->initiateDarajaStkPush($phone, $amount, $reference, $description);
    }

    /**
     * IntaSend Transfer (Payout/B2C)
     * Moves money from your wallet to a specific phone number.
     */
    public function transfer($phone, $amount, $narrative = 'Payment Settlement')
    {
        if (!$this->apiToken) return ['error' => 'API Token not configured for payouts'];

        $payload = [
            'wallet_id' => env('INTASEND_WALLET_ID'),
            'currency'  => 'KES',
            'transactions' => [
                [
                    'account'   => $this->normalizePhone($phone),
                    'amount'    => $amount,
                    'narrative' => $narrative
                ]
            ]
        ];

        $response = Http::timeout(5)->withToken($this->apiToken, 'Bearer')
            ->post($this->baseUrl . '/transfers/mpesa/', $payload);

        if ($response->failed()) {
            Log::error('IntaSend Transfer Failed: ' . $response->body());
            return ['error' => 'Transfer failed', 'details' => $response->json()];
        }

        return $response->json();
    }

    /**
     * Safaricom Daraja STK Push Implementation
     */
    public function initiateDarajaStkPush($phone, $amount, $reference, $description = 'Voice Agent Payment')
    {
        $token = $this->getAccessToken();
        if (!$token) return ['error' => 'Authentication failed'];

        $timestamp = Carbon::now()->format('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        
        $phone = $this->normalizePhone($phone);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => config('services.mpesa.type') === 'till' ? 'CustomerBuyGoodsOnline' : 'CustomerPayBillOnline',
            'Amount'            => (int) $amount,
            'PartyA'            => $phone,
            'PartyB'            => $this->shortcode,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => config('services.mpesa.callback_url'),
            'AccountReference'  => $reference,
            'TransactionDesc'   => $description,
        ];

        $response = Http::timeout(5)->withToken($token)
            ->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', $payload);

        if ($response->failed()) {
            Log::error('M-Pesa STK Push Failed: ' . $response->body());
            return ['error' => 'Request failed', 'details' => $response->json()];
        }

        return $response->json();
    }

    /**
     * Query STK Push Status
     */
    public function queryStatus($checkoutRequestId)
    {
        $token = $this->getAccessToken();
        if (!$token) return ['error' => 'Authentication failed'];

        $timestamp = Carbon::now()->format('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId,
        ];

        $response = Http::timeout(5)->withToken($token)
            ->post($this->baseUrl . '/mpesa/stkpushquery/v1/query', $payload);

        return $response->json();
    }

    /**
     * Normalize phone to 254XXXXXXXXX
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
