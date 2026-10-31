<?php

namespace App\Services;

use App\Models\Integration;
use Google\Client;
use Google\Service\Calendar;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(config('services.google_calendar.client_id'));
        $this->client->setClientSecret(config('services.google_calendar.client_secret'));
        $this->client->setRedirectUri(config('services.google_calendar.redirect'));
        $this->client->addScope(Calendar::CALENDAR_EVENTS);
        $this->client->addScope(Calendar::CALENDAR_READONLY);
        $this->client->setAccessType('offline');
        $this->client->setApprovalPrompt('force');

        $this->client->setHttpClient(\App\Support\HttpClientFactory::make());
    }

    /**
     * Get a configured Google Calendar Service for a specific integration.
     */
    public function getServiceForIntegration(Integration $integration)
    {
        $this->client->setAccessToken($integration->access_token);

        // Handle Token Refreshing
        if ($this->client->isAccessTokenExpired()) {
            if ($integration->refresh_token) {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($integration->refresh_token);
                
                if (isset($newToken['error'])) {
                    Log::error("Google Calendar Refresh Error: " . json_encode($newToken));
                    $integration->update(['status' => 'error']);
                    throw new \Exception("Auth session expired. Please reconnect.");
                }

                $integration->update([
                    'access_token' => $newToken['access_token'],
                    'last_synced_at' => now(),
                ]);
            } else {
                $integration->update(['status' => 'error']);
                throw new \Exception("Refresh token missing. Please reconnect.");
            }
        }

        return new Calendar($this->client);
    }

    /**
     * List primary calendar metadata to verify connection.
     */
    public function verifyConnection(Integration $integration)
    {
        try {
            $service = $this->getServiceForIntegration($integration);
            $calendar = $service->calendars->get('primary');
            
            // Update settings with calendar name for UI
            $settings = $integration->settings;
            $settings['calendar_name'] = $calendar->getSummary();
            $settings['timezone'] = $calendar->getTimeZone();
            $integration->update(['settings' => $settings, 'status' => 'active']);

            return true;
        } catch (\Exception $e) {
            Log::error("Google Calendar Verify Failed: " . $e->getMessage());
            return false;
        }
    }
}
