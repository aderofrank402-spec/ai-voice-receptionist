<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Agent;
use Laravel\Socialite\Facades\Socialite;

class IntegrationsController extends Controller
{
    /**
     * Show the integrations page.
     */
    public function index()
    {
        $user = Auth::user();
        $agent = Agent::where('user_id', $user->id)->firstOrFail();

        // Fetch active connections
        $integrations = \App\Models\Integration::where('user_id', $user->id)
            ->get()
            ->keyBy('provider');

        // ── Real-time Diagnostics (Cached 2 min) ────────────────────────────
        $diagnostics = \Illuminate\Support\Facades\Cache::remember(
            "integrations_diagnostics:{$user->id}",
            120,
            function () use ($agent) {
                $logs = \Illuminate\Support\Facades\DB::table('api_request_logs')
                    ->where('created_at', '>', now()->subDay());

                $totalLogs = (clone $logs)->count();
                $successLogs = (clone $logs)->where('is_success', true)->count();
                $uptime = $totalLogs > 0 ? round(($successLogs / $totalLogs) * 100, 2) : 99.99;
                $latency = round(\Illuminate\Support\Facades\DB::table('api_request_logs')->latest()->take(50)->avg('latency_ms') ?? 0);

                $activeHooks = 0;
                if (!empty($agent->configuration['integrations']['webhook_url']))
                    $activeHooks++;

                return [
                    'latency' => $latency,
                    'uptime' => $uptime,
                    'hooks_count' => $activeHooks,
                ];
            }
        );

        return view('integrations.index', [
            'agent' => $agent,
            'integrations' => $integrations,
            'diagnostics' => $diagnostics
        ]);
    }

    /**
     * Initiate OAuth flow for a provider.
     */
    public function connect($provider)
    {
        if ($provider === 'google_calendar') {
            // Dynamically swap the global google config with the calendar-specific credentials
            config(['services.google' => config('services.google_calendar')]);

            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver('google');

            return $driver->scopes([
                    'https://www.googleapis.com/auth/calendar.events',
                    'https://www.googleapis.com/auth/calendar.readonly'
                ])
                ->with(['access_type' => 'offline', 'prompt' => 'consent'])
                ->redirect();
        }

        // Placeholder for other providers
        return redirect()->back()->with('info', "Connection flow for {$provider} will be available once API credentials are provided.");
    }

    /**
     * Handle OAuth callback from a provider.
     */
    public function callback($provider)
    {
        try {
            if ($provider === 'google_calendar') {
                config(['services.google' => config('services.google_calendar')]);
            }

            $driver = Socialite::driver('google');
            $driver->setHttpClient(\App\Support\HttpClientFactory::make());

            /** @var \Laravel\Socialite\Two\User $socialUser */
            $socialUser = $driver->user();
            $user = Auth::user();

            // Store or Update Integration
            $integration = \App\Models\Integration::updateOrCreate(
                ['user_id' => $user->id, 'provider' => $provider],
                [
                    'provider_id' => $socialUser->getId(),
                    'access_token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken, // Only provided on first consent
                    'status' => 'active',
                    'settings' => [
                        'email' => $socialUser->getEmail(),
                        'name' => $socialUser->getName(),
                        'avatar' => $socialUser->getAvatar(),
                    ],
                    'last_synced_at' => now(),
                ]
            );

            // SYNC: Pull primary calendar details immediately for the UI
            if ($provider === 'google_calendar') {
                app(\App\Services\GoogleCalendarService::class)->verifyConnection($integration);
            }

            return redirect()->route('integrations.index')->with('success', "Successfully connected " . ucfirst(str_replace('_', ' ', $provider)) . ".");
        } catch (\Exception $e) {
            \Log::error("OAuth Callback Error ({$provider}): " . $e->getMessage());
            return redirect()->route('integrations.index')->with('error', "Failed to connect to " . ucfirst($provider) . ".");
        }
    }

    /**
     * Disconnect an integration.
     */
    public function disconnect($id)
    {
        $integration = \App\Models\Integration::where('user_id', Auth::id())->findOrFail($id);
        $integration->delete();

        return redirect()->back()->with('success', 'Integration disconnected successfully.');
    }

    /**
     * Update integration settings.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $agent = Agent::where('user_id', $user->id)->firstOrFail();

        // 1. Handle Specialized Settings
        if ($request->has('mpesa_setup_mode')) {
            return $this->saveMpesa($request);
        }
        if ($request->has('whatsapp_setup')) {
            return $this->saveWhatsApp($request);
        }
        if ($request->has('at_setup')) {
            return $this->saveAfricasTalking($request);
        }
        if ($request->has('hubspot_setup')) {
            return $this->saveHubSpot($request);
        }
        if ($request->has('maps_setup')) {
            return $this->saveGoogleMaps($request);
        }

        // 2. Handle Generic Webhooks
        $validated = $request->validate([
            'webhook_url' => 'nullable|url|max:500',
        ]);

        $config = $agent->configuration ?? [];
        if (!isset($config['integrations']))
            $config['integrations'] = [];
        $config['integrations']['webhook_url'] = $validated['webhook_url'];

        $agent->update(['configuration' => $config]);

        return redirect()->back()->with('success', 'Integration settings saved.');
    }

    /**
     * Save WhatsApp specific status (Platform Managed).
     */
    protected function saveWhatsApp(Request $request)
    {
        $validated = $request->validate([
            'business_number' => 'required|string|max:20',
        ]);

        \App\Models\Integration::updateOrCreate(
            ['user_id' => Auth::id(), 'provider' => 'whatsapp'],
            ['status' => 'active', 'settings' => $validated, 'last_synced_at' => now()]
        );

        return redirect()->back()->with('success', 'WhatsApp messaging enabled via Platform.');
    }

    /**
     * Save Africa's Talking status (Platform Managed).
     */
    protected function saveAfricasTalking(Request $request)
    {
        \App\Models\Integration::updateOrCreate(
            ['user_id' => Auth::id(), 'provider' => 'africas_talking'],
            ['status' => 'active', 'settings' => ['managed' => true], 'last_synced_at' => now()]
        );

        return redirect()->back()->with('success', 'SMS gateway enabled via Platform.');
    }

    /**
     * Save HubSpot specific credentials.
     */
    protected function saveHubSpot(Request $request)
    {
        $validated = $request->validate([
            'access_token' => 'required|string|max:500',
        ]);

        \App\Models\Integration::updateOrCreate(
            ['user_id' => Auth::id(), 'provider' => 'hubspot'],
            [
                'status' => 'active', 
                'access_token' => $validated['access_token'], // Properly utilize core encrypted column
                'settings' => ['configured' => true],
                'last_synced_at' => now()
            ]
        );

        return redirect()->back()->with('success', 'HubSpot configuration updated.');
    }

    /**
     * Save Google Maps metadata (Business Place ID).
     */
    protected function saveGoogleMaps(Request $request)
    {
        $validated = $request->validate([
            'place_id' => 'required|string|max:255',
        ]);

        \App\Models\Integration::updateOrCreate(
            ['user_id' => Auth::id(), 'provider' => 'google_maps'],
            ['status' => 'active', 'settings' => $validated, 'last_synced_at' => now()]
        );

        return redirect()->back()->with('success', 'Business location pin updated.');
    }

    /**
     * Save M-Pesa specific credentials.
     */
    protected function saveMpesa(Request $request)
    {
        $validated = $request->validate([
            'mpesa_setup_mode' => 'required|in:aggregator,direct',
            // Aggregator fields
            'payout_number' => 'required_if:mpesa_setup_mode,aggregator|nullable|string|max:20',
            // Direct Daraja fields
            'consumer_key' => 'required_if:mpesa_setup_mode,direct|nullable|string|max:255',
            'consumer_secret' => 'required_if:mpesa_setup_mode,direct|nullable|string|max:255',
            'passkey' => 'required_if:mpesa_setup_mode,direct|nullable|string|max:255',
            'shortcode' => 'required_if:mpesa_setup_mode,direct|nullable|string|max:20',
        ]);

        \App\Models\Integration::updateOrCreate(
            ['user_id' => Auth::id(), 'provider' => 'mpesa'],
            [
                'status' => 'active',
                'settings' => $validated,
                'last_synced_at' => now(),
            ]
        );

        return redirect()->back()->with('success', 'M-Pesa configuration updated.');
    }
}
