<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Call;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $agent = Agent::where('user_id', $user->id)->first();

        // ── Aggregated stats from the Snapshot Table (O(1) Dashboard) ────────
        if ($agent) {
            $agentId = $agent->id;

            // Fetch compiled metrics for all time
            $metricsQuery = \App\Models\DailyAgentMetric::where('agent_id', $agentId);
            
            $totalCalls       = (int) $metricsQuery->sum('total_calls');
            $totalSeconds     = (int) $metricsQuery->sum('total_seconds');
            $minutesUsed      = (int) round($totalSeconds / 60);
            $successCount     = (int) $metricsQuery->sum('success_count');
            $afterHoursCount  = (int) $metricsQuery->sum('after_hours_count');
            
            $successRate = $totalCalls > 0 ? round(($successCount / $totalCalls) * 100) : 0;

            $stats = [
                'total_calls'   => $totalCalls,
                'minutes_used'  => $minutesUsed,
                'success_count' => $successCount,
                'success_rate'  => $successRate,
                'after_hours'   => $afterHoursCount,
            ];

            // ── Call volume chart — last 7 days ──────────────────────────────
            // Build an array keyed by date for the last 7 days, defaulting all counts to 0
            $last7 = [];
            $labels = [];
            for ($i = 6; $i >= 0; $i--) {
                $date          = now()->subDays($i)->format('Y-m-d');
                $label         = now()->subDays($i)->format('D'); // Mon, Tue…
                $labels[]      = $label;
                $last7[$date]  = 0;
            }

            // Fill in the actual call counts from the DB (Cached 3 min)
            $callCounts = \Illuminate\Support\Facades\Cache::remember(
                "agent_dash_chart:{$agentId}",
                180,
                function () use ($agentId) {
                    return Call::where('agent_id', $agentId)
                        ->where('created_at', '>=', now()->subDays(7)->startOfDay())
                        ->select(DB::raw('DATE(created_at) as call_date'), DB::raw('COUNT(*) as total'))
                        ->groupBy('call_date')
                        ->get()
                        ->pluck('total', 'call_date');
                }
            );

            foreach ($callCounts as $date => $count) {
                if (isset($last7[$date])) {
                    $last7[$date] = (int) $count;
                }
            }

            $chartData = [
                'labels' => $labels,
                'volume' => array_values($last7),
            ];

            // ── Recent calls feed — paginated ────────────────────────────────────
            $recentCalls = Call::where('agent_id', $agentId)
                ->latest()
                ->paginate(10)
                ->through(function ($call) {
                    // Map DB status to the display format the blade template expects
                    $displayStatus = match ($call->status) {
                        'completed'   => 'success',
                        'failed'      => 'missed',
                        'in_progress' => 'success',
                        default       => 'voicemail',
                    };

                    // Format duration as "Xm Ys"
                    $sec      = (int) ($call->duration ?? 0);
                    $duration = $sec > 0
                        ? sprintf('%dm %ds', intdiv($sec, 60), $sec % 60)
                        : '0s';

                    // Human-friendly "time ago"
                    $time = $call->created_at
                        ? $call->created_at->timezone('Africa/Nairobi')->diffForHumans()
                        : 'Unknown';

                    // Parse structured AI summary if available for the dashboard feed
                    $summaryText = 'No summary available.';
                    $rawSummary = $call->metadata['summary'] ?? null;
                    if ($rawSummary && is_string($rawSummary)) {
                        $decoded = json_decode($rawSummary, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $summaryText = $decoded['professional_summary'] ?? $rawSummary;
                        } else {
                            $summaryText = $rawSummary;
                        }
                    }

                    return [
                        'id'            => $call->id,
                        'status'        => $displayStatus,
                        'caller'        => $call->caller_number ?? 'Unknown',
                        'caller_name'   => 'Unknown',     // Caller ID reverse-lookup is Phase 4
                        'time'          => $time,
                        'duration'      => $duration,
                        'outcome'       => $call->outcome ?? ucfirst($call->status ?? 'Handled'),
                        'summary'       => $summaryText,
                        'recording_url' => null,          // Call recording is Phase 4
                    ];
                });
        } else {
            // No agent yet — show empty zeros, send user to onboarding
            $stats = [
                'total_calls'  => 0,
                'minutes_used' => 0,
                'avg_duration' => '0s',
                'saved_time'   => '0 mins',
            ];
            $chartData   = [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'volume' => [0, 0, 0, 0, 0, 0, 0],
            ];
            $recentCalls = collect();
        }

        // ── Readiness & Integration Sync ─────────────────────────────────────
        $integrations = \App\Models\Integration::where('user_id', $user->id)
            ->get()
            ->keyBy('provider');

        $hasCalendar = isset($integrations['google_calendar']) && $integrations['google_calendar']->status === 'active';
        $hasKnowledge = !empty($agent->configuration['business_name']) || !empty($agent->configuration['services']);
        // If agent has a phone number and system prompt, the core voice/telephony is 'Ready'
        $hasVoice = !empty($agent->phone_number) || !empty($agent->system_prompt);

        $readiness = 0;
        if ($hasKnowledge) $readiness += 33;
        if ($hasVoice) $readiness += 33;
        if ($hasCalendar) $readiness += 34;

        if ($readiness > 100) $readiness = 100;
        // Absolute precision for 'Done' state
        if ($hasKnowledge && $hasVoice && $hasCalendar) $readiness = 100;

        $readinessData = [
            'score' => $readiness,
            'has_calendar' => $hasCalendar,
            'has_knowledge' => $hasKnowledge,
            'has_voice' => $hasVoice,
        ];

        return view('dashboard', [
            'agent'       => $agent,
            'stats'       => $stats,
            'chartData'   => $chartData,
            'recentCalls' => $recentCalls,
            'readiness'   => $readinessData,
        ]);
    }

    /**
     * Export the current user's call logs to CSV.
     */
    public function exportCalls()
    {
        $user = Auth::user();
        $agentIds = Agent::where('user_id', $user->id)->pluck('id');

        $calls = Call::whereIn('agent_id', $agentIds)
            ->latest()
            ->get();

        $csv = "Call ID,Agent,Duration (s),Status,Outcome,Summary,Date\n";
        
        foreach ($calls as $call) {
            $csv .= sprintf(
                '"%s","%s",%d,"%s","%s","%s","%s"' . "\n",
                $call->call_id ?? 'N/A',
                $call->agent->name ?? 'AI Agent',
                $call->duration ?? 0,
                $call->status ?? 'completed',
                $call->outcome ?? 'N/A',
                str_replace('"', '""', $call->metadata['summary'] ?? 'N/A'),
                $call->created_at->format('Y-m-d H:i:s')
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="my-calls-' . now()->format('Y-m-d') . '.csv"');
    }

    /**
     * Get call volume data for analytics charts (AJAX).
     */
    public function getVolumeData(Request $request)
    {
        $user = Auth::user();
        $agent = Agent::where('user_id', $user->id)->first();
        
        if (!$agent) {
            return response()->json(['labels' => [], 'volume' => []]);
        }

        $days = (int) $request->get('days', 7);
        if ($days > 30) $days = 30;
        if ($days < 1) $days = 1;

        $results = [];
        $labels = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $label = $days > 7 ? now()->subDays($i)->format('M d') : now()->subDays($i)->format('D');
            $labels[] = $label;
            $results[$date] = 0;
        }

        // Optimized Snapshot Lookup
        $callCounts = \App\Models\DailyAgentMetric::where('agent_id', $agent->id)
            ->where('date', '>=', now()->subDays($days)->format('Y-m-d'))
            ->get()
            ->pluck('total_calls', 'date');

        foreach ($callCounts as $date => $count) {
            if (isset($results[$date])) {
                $results[$date] = (int) $count;
            }
        }

        return response()->json([
            'labels' => $labels,
            'volume' => array_values($results),
        ]);
    }

    /**
     * Display a full paginated archive of all calls for the user's agent.
     */
    public function calls(Request $request)
    {
        $user  = Auth::user();
        $agent = Agent::where('user_id', $user->id)->first();
        $isAdmin = (bool) $user->hasAdminAccess();

        if (!$agent) {
            return redirect()->route('onboarding.index');
        }

        $query = Call::where('agent_id', $agent->id);

        // ── Search by phone number ──────────────────────────────────────────
        if ($request->filled('search')) {
            $safe = str_replace(['%', '_', '\\'], ['\%', '\_', '\\\\'], $request->search);
            $query->where('caller_number', 'like', '%' . $safe . '%');
        }

        // ── Filter by specific outcomes ──────────────────────────────────────
        if ($request->filled('outcome')) {
            $safeOutcome = str_replace(['%', '_', '\\'], ['\%', '\_', '\\\\'], $request->outcome);
            $query->where('outcome', 'like', '%' . $safeOutcome . '%');
        }

        $calls = $query->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(function ($call) use ($isAdmin) {
                // Formatting matches dashboard for consistency
                $displayStatus = match ($call->status) {
                    'completed'   => 'success',
                    'failed'      => 'missed',
                    'in_progress' => 'success',
                    default       => 'voicemail',
                };

                $sec = (int) ($call->duration ?? 0);
                
                // Parse structured AI summary if available
                $summaryData = null;
                $rawSummary = $call->metadata['summary'] ?? null;
                if ($rawSummary && is_string($rawSummary)) {
                    $decoded = json_decode($rawSummary, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $summaryData = $decoded;
                    }
                }

                // Transcript Redaction for privacy
                $transcript = $call->transcript ?? [];
                if (!empty($transcript)) {
                    $transcript = array_map(function($line) {
                        if (is_array($line)) {
                            if (isset($line['text'])) {
                                $line['text'] = $this->redact($line['text']);
                            }
                            if (isset($line['content'])) {
                                $line['content'] = $this->redact($line['content']);
                            }
                        }
                        return $line;
                    }, (array) $transcript);
                }

                return [
                    'id'            => $call->id,
                    'status'        => $displayStatus,
                    'caller'        => $call->caller_number ?? 'Unknown',
                    'caller_name'   => 'Unknown',
                    'date'          => $call->created_at->timezone('Africa/Nairobi')->format('M d, Y H:i:s'),
                    'duration'      => $sec > 0 ? sprintf('%dm %ds', intdiv($sec, 60), $sec % 60) : '0s',
                    'outcome'       => $call->outcome ?? ucfirst($call->status ?? 'Handled'),
                    
                    // Structured Data for the new UI
                    'summary_text'  => $summaryData['professional_summary'] ?? ($call->metadata['summary'] ?? 'No summary available.'),
                    'sentiment'     => $call->metadata['sentiment'] ?? ($summaryData['sentiment'] ?? 'Neutral'),
                    'intent'        => $call->primary_intent ?? ($summaryData['primary_intent'] ?? 'Inquiry'),
                    'lead_score'    => $call->lead_score ?? 5, // Default for demo if not in DB
                    'action_items'  => $summaryData['action_items'] ?? ($call->metadata['action_items'] ?? []),
                    'recording_url' => $call->recording_url,
                    'transcript'    => $transcript,
                    
                    // SaaS Value Logic
                    'time_saved'    => round($sec / 60, 1) . ' mins',
                    
                    // Admin Financials (Only passed if admin)
                    'costs' => $isAdmin ? [
                        'total_usd' => $call->cost,
                        'total_kes' => $call->total_cost_kes,
                        'stt'       => $call->stt_cost_usd,
                        'tts'       => $call->tts_cost_usd,
                        'llm'       => $call->llm_cost_usd,
                        'telephony' => $call->telephony_cost_usd,
                    ] : null,
                ];
            });

        // Calculate High-Level Archive Metrics — Cached for 3 minutes per agent
        $stats = \Illuminate\Support\Facades\Cache::remember(
            "calls_archive_stats:{$agent->id}",
            180,
            function () use ($agent) {
                $todayStart = now()->startOfDay();
                $baseQuery = Call::where('agent_id', $agent->id);

                $totalCount = (clone $baseQuery)->count();
                $successCount = (clone $baseQuery)->where('status', 'completed')->count();

                return [
                    'volume_today'      => (clone $baseQuery)->where('created_at', '>=', $todayStart)->count(),
                    'total_time_saved'  => round(((clone $baseQuery)->sum('duration') ?: 0) / 3600, 1) . ' hrs',
                    'avg_lead_score'    => round((clone $baseQuery)->avg('lead_score') ?: 0, 1),
                    'success_rate'      => ($totalCount > 0 ? round(($successCount / $totalCount) * 100, 0) : 0) . '%',
                ];
            }
        );

        return view('calls.index', [
            'agent'   => $agent,
            'calls'   => $calls,
            'isAdmin' => $isAdmin,
            'stats'   => $stats,
        ]);
    }

    /**
     * Redact sensitive information from text strings.
     */
    private function redact($text)
    {
        if (!is_string($text)) return $text;

        $patterns = [
            '/\b\d{4}[- ]?\d{4}[- ]?\d{4}[- ]?\d{4}\b/' => '****-****-****-****', // CC
            '/\b[\w\.-]+@[\w\.-]+\.\w{2,4}\b/'          => '[email protected]',      // Email
            '/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/'           => '***-***-****',       // Phone
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $text);
    }
}
