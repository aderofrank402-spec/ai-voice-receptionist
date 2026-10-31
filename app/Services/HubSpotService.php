<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HubSpotService
{
    protected $accessToken;
    protected $baseUrl = "https://api.hubapi.com/crm/v3";

    public function __construct($accessToken = null)
    {
        $this->accessToken = $accessToken ?? \App\Models\SystemCredential::getValue('hubspot_access_token');
    }

    /**
     * Set access token dynamically (for user-specific integrations)
     */
    public function setToken($token)
    {
        $this->accessToken = $token;
        return $this;
    }

    /**
     * Create or Update a contact and add a call outcome note.
     */
    public function syncVoiceLead($phone, $name = 'Voice Lead', $outcome = '', $summary = '')
    {
        if (!$this->accessToken) {
            return ['error' => 'HubSpot Access Token not configured.'];
        }

        // 1. Upsert Contact
        $contactResponse = Http::timeout(5)->withToken($this->accessToken)
            ->patch("{$this->baseUrl}/objects/contacts/{$phone}?idProperty=phone", [
                'properties' => [
                    'firstname' => $name,
                    'phone'     => $phone,
                    'lifecyclestage' => 'lead'
                ]
            ]);

        // If patch fails by phone, try a standard post (new contact)
        if ($contactResponse->status() === 404) {
            $contactResponse = Http::timeout(5)->withToken($this->accessToken)
                ->post("{$this->baseUrl}/objects/contacts", [
                    'properties' => [
                        'firstname' => $name,
                        'phone'     => $phone,
                        'lifecyclestage' => 'lead'
                    ]
                ]);
        }

        if ($contactResponse->failed()) {
            Log::error("HubSpot Contact Sync Failed: " . $contactResponse->body());
            return ['error' => 'Failed to sync contact', 'details' => $contactResponse->json()];
        }

        $contactId = $contactResponse->json('id');

        // 2. Add a Note/Engagement for the call
        if ($outcome || $summary) {
            $noteBody = "<b>Voice Agent Outcome:</b> {$outcome}<br><br><b>Summary:</b> {$summary}";
            
            $noteResponse = Http::timeout(5)->withToken($this->accessToken)
                ->post("{$this->baseUrl}/objects/notes", [
                    'properties' => [
                        'hs_timestamp' => now()->toIso8601String(),
                        'hs_note_body' => $noteBody
                    ]
                ]);

            if ($noteResponse->successful()) {
                $noteId = $noteResponse->json('id');
                // Associate note with contact
                Http::timeout(5)->withToken($this->accessToken)
                    ->put("{$this->baseUrl}/objects/notes/{$noteId}/associations/contact/{$contactId}/note_to_contact");
            }
        }

        return ['status' => 'success', 'contact_id' => $contactId];
    }
}
