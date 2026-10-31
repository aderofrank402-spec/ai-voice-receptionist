<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Call;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    /**
     * Check available slots for a given date.
     */
    public function checkAvailability(Request $request)
    {
        // Mock Data: In real app, query appointments table
        $date = $request->input('date', date('Y-m-d'));

        return response()->json([
            'status' => 'success',
            'date'   => $date,
            'slots'  => [
                '09:00 AM',
                '11:30 AM',
                '02:00 PM',
                '04:30 PM',
            ],
        ]);
    }

    /**
     * Book an appointment.
     */
    public function book(Request $request)
    {
        $request->validate([
            'name'   => 'required|string',
            'date'   => 'required|date',
            'time'   => 'required|string',
            'reason' => 'nullable|string',
        ]);

        // Mock Booking Logic — will be replaced when appointments table is wired
        return response()->json([
            'status'     => 'success',
            'message'    => 'Appointment confirmed.',
            'booking_id' => 'BK-' . strtoupper(uniqid()),
            'details'    => $request->all(),
        ]);
    }

    /**
     * Get Agent Configuration for the Voice Engine.
     *
     * Called by voice-engine/src/services/VoiceOrchestrator.ts on every call start.
     * Returns the real system_prompt and structured configuration from the agents table.
     *
     * Usage: GET /api/v1/agents/config?agent_id=1
     */
    public function getConfig(Request $request)
    {
        $agentId = $request->input('agent_id');

        if (!$agentId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'agent_id is required',
            ], 400);
        }

        $agent = Agent::find($agentId);

        if (!$agent) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Agent not found',
            ], 404);
        }

        if ($agent->status !== 'active') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Agent is not active (status: ' . $agent->status . ')',
            ], 403);
        }

        if ($agent->user->isSuspended()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Agent owner account has been suspended',
            ], 403);
        }

        $configuration = $agent->configuration ?? [];

        return response()->json([
            'status'   => 'success',
            'agent_id' => $agent->id,
            'config'   => [
                // The compiled golden system prompt — the LLM persona
                'prompt'              => $agent->system_prompt,
                // Structured fields for reference and future tool expansions
                'business_name'       => $agent->name,
                'services'            => $configuration['services']            ?? [],
                'hours'               => $configuration['hours']               ?? null,
                'missed_call_action'  => $configuration['missed_call_action']  ?? null,
                'landmark_directions' => $configuration['landmark_directions'] ?? null,
                'status'              => $agent->status,
                'phone_number'        => $agent->phone_number,
            ],
        ]);
    }

    /**
     * Get Agent Configuration by Phone Number.
     *
     * Called by the telephony server (telephony/src/app.ts) on incoming SIP INVITE
     * to resolve which agent owns the dialed Zadarma DID number.
     *
     * Usage: GET /api/v1/agents/by-number?number=254712345678
     */
    public function getAgentByNumber(Request $request)
    {
        $number = $request->input('number');

        if (!$number) {
            return response()->json([
                'status'  => 'error',
                'message' => 'number is required',
            ], 400);
        }

        // Normalise the incoming number: strip leading +, spaces, dashes
        // so +254712345678, 254712345678, and 0712345678 all have a chance to match
        $stripped = preg_replace('/[\s\-]/', '', $number);
        $noPlus   = ltrim($stripped, '+');

        $agent = Agent::where('status', 'active')
            ->where(function ($q) use ($stripped, $noPlus) {
                $q->where('phone_number', $stripped)
                  ->orWhere('phone_number', '+' . $noPlus)
                  ->orWhere('phone_number', $noPlus);
            })
            ->first();

        if ($agent && $agent->user->isSuspended()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Agent owner account has been suspended',
            ], 403);
        }

        if (!$agent) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No active agent found for number: ' . $number,
            ], 404);
        }

        $configuration = $agent->configuration ?? [];

        return response()->json([
            'status'   => 'success',
            'agent_id' => $agent->id,
            'config'   => [
                'prompt'              => $agent->system_prompt,
                'business_name'       => $agent->name,
                'services'            => $configuration['services']            ?? [],
                'hours'               => $configuration['hours']               ?? null,
                'missed_call_action'  => $configuration['missed_call_action']  ?? null,
                'landmark_directions' => $configuration['landmark_directions'] ?? null,
                'status'              => $agent->status,
                'phone_number'        => $agent->phone_number,
            ],
        ]);
    }

    /**
     * Probes the Drachtio server to see if it's alive.
     */
    public function getSystemHealth()
    {
        $host = config('services.drachtio.host', '127.0.0.1');
        $port = config('services.drachtio.port', 9022);
        
        $online = false;
        try {
            $connection = @fsockopen($host, $port, $errno, $errstr, 1);
            if (is_resource($connection)) {
                $online = true;
                fclose($connection);
            }
        } catch (\Exception $e) {
            $online = false;
        }

        return response()->json([
            'status' => $online ? 'online' : 'offline',
            'drachtio' => $online,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Log Call Data from the Voice Engine.
     *
     * Called by voice-engine/src/services/VoiceOrchestrator.ts in endCall().
     * Records the full call — duration, transcript, cost, outcome — to the calls table.
     */
    public function logCall(Request $request)
    {
        $validated = $request->validate([
            'call_id'            => 'required|string',
            'agent_id'           => 'nullable|integer',
            'caller_number'      => 'nullable|string',
            'status'             => 'required|string',
            'duration'           => 'required|integer',
            'transcript'         => 'nullable|string',
            'outcome'            => 'nullable|string',
            'cost'               => 'nullable|numeric',
            'stt_cost_usd'       => 'nullable|numeric',
            'llm_cost_usd'       => 'nullable|numeric',
            'tts_cost_usd'       => 'nullable|numeric',
            'telephony_cost_usd' => 'nullable|numeric',
            'total_cost_kes'     => 'nullable|numeric',
            'tokens_used'        => 'nullable|integer',
            'characters_used'    => 'nullable|integer',
            'metadata'           => 'nullable|array',
        ]);

        $summary = $request->input('summary');
        if ($summary) {
            $metadata = $validated['metadata'] ?? [];
            $metadata['summary'] = $summary;
            $validated['metadata'] = $metadata;
        }

        // Professional Economics Precision (v7)
        $economics = \App\Services\EconomicsService::calculate(
            (int) $validated['duration'],
            (int) ($validated['tokens_used'] ?? 0),
            (int) ($validated['characters_used'] ?? 0),
            $validated['caller_number'] ?? null,
            isset($validated['stt_seconds']) ? (int) $validated['stt_seconds'] : null
        );

        // Fix: If transcript is a string (JSON), decode it so Laravel's array cast handles it correctly.
        // This prevents "double-stringified" JSON in the database.
        if (isset($validated['transcript']) && is_string($validated['transcript'])) {
            $decoded = json_decode($validated['transcript'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $validated['transcript'] = $decoded;
            }
        }

        $call = Call::updateOrCreate(
            ['call_id' => $validated['call_id']],
            array_merge($validated, [
                'stt_cost_usd'       => $economics['costs']['stt'],
                'llm_cost_usd'       => $economics['costs']['llm'],
                'tts_cost_usd'       => $economics['costs']['tts'],
                'telephony_cost_usd' => $economics['costs']['telephony'],
                'cost'               => $economics['total_revenue_usd'], // For existing DB 'cost' column used as billing
                'total_cost_kes'     => $economics['total_revenue_kes'],
            ])
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Call logged with Economics v4',
            'call_id' => $call->id,
            'summary' => $economics
        ]);
    }

    /**
     * Get credentials for Voice Engine at call start
     */
    public function getCredentials(Request $request)
    {
        $secret = $request->header('X-Crawler-Secret');
        if ($secret !== config('services.crawler.secret')) {
            Log::warning("Unauthorized system/credentials access attempt.");
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $creds = [];
        try {
            $allCreds = \App\Models\SystemCredential::all();
            foreach ($allCreds as $item) {
                try {
                    $creds[$item->name] = $item->value;
                } catch (\Illuminate\Contracts\Encryption\DecryptException $de) {
                    Log::error("Decryption failed for key: {$item->name}. Payload probably plain text.");
                    $creds[$item->name] = 'not_configured_or_corrupt';
                } catch (\Exception $e) {
                    $creds[$item->name] = 'not_configured_or_corrupt';
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to fetch credentials: " . $e->getMessage());
            return response()->json(['error' => 'Server error during credential fetch'], 500);
        }

        return response()->json([
            'status' => 'success',
            'credentials' => $creds
        ]);
    }

    /**
     * Store a performance trace from Voice Engine
     */
    public function saveTrace(Request $request)
    {
        $secret = $request->header('X-Crawler-Secret');
        if ($secret !== config('services.crawler.secret')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info('[Telemetry Audit] Incoming trace:', $request->all());

        try {
            $validated = $request->validate([
            'provider' => 'required|string',
            'action' => 'required|string',
            'latency_ms' => 'required|numeric',
            'status_code' => 'required|integer',
            'is_success' => 'required|boolean',
            'metadata' => 'nullable|array',
        ]);

            \App\Models\ApiRequestLog::create($validated);
            return response()->json(['status' => 'logged']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('[Telemetry Audit] Validation failed:', $e->errors());
            throw $e;
        } catch (\Exception $e) {
            Log::error('[Telemetry Audit] Save failed:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}
