<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Agent;
use App\Models\Call;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Notifications\UserSuspendedAccount;
use App\Notifications\UserActivatedAccount;

class AdminController extends Controller
{
    /**
     * Admin Dashboard - Platform Overview
     */
    public function index()
    {
        // High-Performance Optimization: Cache platform metrics for 10 minutes
        $stats = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_stats', 600, function () {
            return [
                'total_users' => User::count(),
                'new_users_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
                'total_agents' => Agent::count(),
                'total_calls' => Call::count(),
                'calls_today' => Call::whereDate('created_at', today())->count(),
                'active_users' => User::where('updated_at', '>=', now()->subDays(7))->count(),
            ];
        });

        // Analytical Cache: Optimized chart data for 10 minutes
        $analytics = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_analytics', 600, function () {
            // User growth chart data (last 30 days)
            $userGrowth = User::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Calls per day (last 30 days)
            $callsPerDay = Call::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Recent activity
            $recentUsers = User::latest()->take(5)->get()->map(function ($user) {
                return (object) [
                    'description' => "New user registered: {$user->name}",
                    'created_at' => $user->created_at
                ];
            });

            $recentAgents = Agent::latest()->take(5)->get()->map(function ($agent) {
                return (object) [
                    'description' => "New agent created: {$agent->name}",
                    'created_at' => $agent->created_at
                ];
            });

            $recentActivity = $recentUsers->merge($recentAgents)->sortByDesc('created_at')->take(10);

            return [
                'userGrowth' => $userGrowth,
                'callsPerDay' => $callsPerDay,
                'recentActivity' => $recentActivity
            ];
        });

        return view('admin.index', array_merge($analytics, ['stats' => $stats]));
    }

    /**
     * User Management
     */
    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = str_replace(['%', '_', '\\'], ['\%', '\_', '\\\\'], substr($request->search, 0, 100));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('updated_at', '>=', now()->subDays(7));
            }
        }

        $users = $query->withCount([
            'agents',
            'agents as calls_count', // Assuming direct relationship or HasManyThrough
        ])->latest()->paginate(20);

        return view('admin.users', compact('users'));
    }

    /**
     * User Details
     */
    public function userDetails(User $user)
    {
        $user->load(['agents', 'usageLogs']);

        $callStats = Call::whereIn('agent_id', $user->agents->pluck('id'))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->get();

        $recentCalls = Call::whereIn('agent_id', $user->agents->pluck('id'))
            ->with('agent') // Eager load agent info
            ->latest()
            ->take(20)
            ->get();

        return view('admin.user-details', compact('user', 'callStats', 'recentCalls'));
    }

    /**
     * Agents Gallery - All prompts across platform
     */
    public function agents(Request $request)
    {
        // Eager load User to prevent N+1 in prompt list
        $query = Agent::with(['user']);

        if ($request->filled('search')) {
            $search = str_replace(['%', '_', '\\'], ['\%', '\_', '\\\\'], substr($request->search, 0, 100));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('configuration', 'like', "%{$search}%");
            });
        }

        $agents = $query->withCount('calls')->latest()->paginate(20);

        return view('admin.agents', compact('agents'));
    }

    /**
     * Platform-Wide Call Logs
     */
    public function calls(Request $request)
    {
        $query = Call::with(['agent.user']);

        // Filter by date
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $safe = str_replace(['%', '_', '\\'], ['\%', '\_', '\\\\'], substr($request->search, 0, 100));
            $query->where('transcript', 'like', "%{$safe}%");
        }

        $calls = $query->latest()->paginate(50);

        return view('admin.calls', compact('calls'));
    }

    /**
     * System Health & Monitoring
     */
    public function system()
    {
        \Illuminate\Support\Facades\Gate::authorize('view-system-logs');
        
        $health = [
            'database' => $this->checkDatabase(),
            'disk_space' => $this->checkDiskSpace(),
            'queue_status' => $this->checkQueue(),
            'voice_engine' => $this->checkVoiceEngine(),
            'telephony' => $this->checkTelephony(),
        ];

        // Recent errors (last 24 hours) - placeholder for now
        $recentErrors = [];

        return view('admin.system', compact('health', 'recentErrors'));
    }

    /**
     * Engagement Metrics Dashboard
     */
    public function engagement()
    {
        // High-Performance: Cache all engagement metrics for 10 minutes
        $data = Cache::remember('admin_engagement_metrics', 600, function () {
            $totalUsers = User::count();
            $totalCalls = Call::count();
            $avgCallsPerUser = $totalUsers > 0 ? round($totalCalls / $totalUsers, 2) : 0;

            $leaderboard = User::withCount('agents')
                ->withCount([
                    'agents as total_calls' => function ($q) {
                        $q->join('calls', 'agents.id', '=', 'calls.agent_id');
                    }
                ])
                ->orderByDesc('total_calls')
                ->take(10)
                ->get();

            // Optimized: Only fetch business_type via JSON extraction instead of full configuration
            $agentsByType = DB::table('agents')
                ->selectRaw("COALESCE(configuration->>'business_type', 'Unknown') as btype, COUNT(*) as cnt")
                ->groupBy('btype')
                ->orderByDesc('cnt')
                ->limit(10)
                ->pluck('cnt', 'btype');

            $totalUsers7Days = User::where('created_at', '>=', now()->subDays(7))->count();
            $activeUsers7Days = User::where('created_at', '>=', now()->subDays(7))
                ->whereHas('agents.calls', function ($q) {
                    $q->where('created_at', '>=', now()->subDays(7));
                })
                ->count();
            $retention7Day = $totalUsers7Days > 0 ? round(($activeUsers7Days / $totalUsers7Days) * 100, 1) : 0;

            $totalUsers30Days = User::where('created_at', '>=', now()->subDays(30))->count();
            $activeUsers30Days = User::where('created_at', '>=', now()->subDays(30))
                ->whereHas('agents.calls', function ($q) {
                    $q->where('created_at', '>=', now()->subDays(30));
                })
                ->count();
            $retention30Day = $totalUsers30Days > 0 ? round(($activeUsers30Days / $totalUsers30Days) * 100, 1) : 0;

            return compact(
                'avgCallsPerUser', 'leaderboard', 'agentsByType',
                'retention7Day', 'retention30Day', 'totalUsers',
                'activeUsers7Days', 'activeUsers30Days'
            );
        });

        return view('admin.engagement', $data);
    }

    /**
     * Suspend User
     */
    public function suspendUser(User $user)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-users');
        
        if ($user->is_admin) {
            return back()->with('error', 'Super Admins cannot be suspended.');
        }

        $user->update([
            'suspended_at' => now(),
        ]);

        // Send Notification
        $user->notify(new UserSuspendedAccount());

        // High-Integrity Audit Trail
        \App\Support\Audit::log('user_suspended', $user, [
            'reason' => 'Administrative suspension',
            'previous_status' => 'active'
        ]);

        return back()->with('success', "User {$user->email} has been suspended.");
    }

    /**
     * Activate User
     */
    public function activateUser(User $user)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-users');
        
        $user->update([
            'suspended_at' => null,
        ]);

        // Send Notification
        $user->notify(new UserActivatedAccount());

        // High-Integrity Audit Trail
        \App\Support\Audit::log('user_activated', $user, [
            'reason' => 'Administrative activation',
            'previous_status' => 'suspended'
        ]);

        return back()->with('success', "User {$user->email} has been activated.");
    }

    /**
     * API Usage Dashboard
     */
    public function apiUsage()
    {
        \Illuminate\Support\Facades\Gate::authorize('view-economics');

        // High-Performance: Cache ALL economics data for 5 minutes
        $data = Cache::remember('admin_api_usage_full', 300, function () {
            // Single-pass aggregation: 1 query instead of 9
            $totals = DB::table('calls')->selectRaw('
                COUNT(*) as total_calls,
                COALESCE(SUM(cost), 0) as total_usd,
                COALESCE(SUM(total_cost_kes), 0) as total_kes,
                COALESCE(SUM(stt_cost_usd), 0) as stt,
                COALESCE(SUM(llm_cost_usd), 0) as llm,
                COALESCE(SUM(tts_cost_usd), 0) as tts,
                COALESCE(SUM(telephony_cost_usd), 0) as phone,
                COALESCE(SUM(tokens_used), 0) as total_tokens,
                COALESCE(SUM(duration), 0) as total_duration
            ')->first();

            $totalUsd = (float) $totals->total_usd;
            $totalKes = (float) $totals->total_kes;
            $totalCalls = (int) $totals->total_calls;
            $componentCosts = [
                'stt' => (float) $totals->stt,
                'llm' => (float) $totals->llm,
                'tts' => (float) $totals->tts,
                'phone' => (float) $totals->phone,
            ];

            // MTD Spend
            $mtdSpend = (float) (Call::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('cost') ?? 0);

            $daysInMonth = now()->daysInMonth;
            $dayOfMonth = now()->day;
            $dailyRunRate = $dayOfMonth > 0 ? ($mtdSpend / $dayOfMonth) : 0;
            $eomForecast = round($dailyRunRate * $daysInMonth, 2);

            $budgetLimit = (float) \App\Models\SystemSetting::get('platform_budget_usd', 100);
            $budgetUsedPercentage = $budgetLimit > 0 ? min(100, round(($mtdSpend / $budgetLimit) * 100, 1)) : 0;
            $forecastBreach = $eomForecast > $budgetLimit;

            $totalDurationMinutes = $totals->total_duration / 60;
            $estimatedRevenue = $totalDurationMinutes * 0.50;
            $estimatedMargin = $estimatedRevenue - $totalUsd;

            $costPerUser = DB::table('users')
                ->join('agents', 'users.id', '=', 'agents.user_id')
                ->join('calls', 'agents.id', '=', 'calls.agent_id')
                ->select(
                    'users.name',
                    DB::raw('SUM(calls.cost) as total_cost_usd'),
                    DB::raw('SUM(calls.total_cost_kes) as total_cost_kes'),
                    DB::raw('COUNT(calls.id) as calls_count')
                )
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total_cost_usd')
                ->take(10)
                ->get();

            $dailyCosts = Call::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(stt_cost_usd) as stt'),
                    DB::raw('SUM(llm_cost_usd) as llm'),
                    DB::raw('SUM(tts_cost_usd) as tts'),
                    DB::raw('SUM(telephony_cost_usd) as phone'),
                    DB::raw('SUM(cost) as total')
                )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            if ($dailyCosts->isEmpty()) {
                $dailyCosts = collect([(object)[
                    'date' => now()->format('Y-m-d'),
                    'stt' => 0, 'llm' => 0, 'tts' => 0, 'phone' => 0, 'total' => 0
                ]]);
            }

            $totalTokens = (int) $totals->total_tokens;
            $totalMinutes = $totalDurationMinutes;
            $rateLimits = [
                'openai' => [
                    'used' => $totalTokens,
                    'limit' => 1000000,
                    'percentage' => $totalTokens > 0 ? min(100, ($totalTokens / 1000000) * 100) : 0
                ],
                'zadarma' => [
                    'used' => round($totalMinutes, 1),
                    'limit' => 3000,
                    'percentage' => $totalMinutes > 0 ? min(100, ($totalMinutes / 3000) * 100) : 0
                ]
            ];

            return compact(
                'totalUsd', 'totalKes', 'totalCalls', 'componentCosts',
                'estimatedRevenue', 'estimatedMargin', 'costPerUser', 'dailyCosts',
                'rateLimits', 'mtdSpend', 'eomForecast', 'budgetLimit',
                'budgetUsedPercentage', 'forecastBreach'
            );
        });

        return view('admin.api-usage', $data);
    }

    /**
     * Update Economic Settings (Budget, etc.)
     */
    public function updateEconomicsSettings(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('view-economics');

        $request->validate([
            'platform_budget_usd' => 'required|numeric|min:0',
        ]);

        \App\Models\SystemSetting::set('platform_budget_usd', $request->platform_budget_usd, 'Monthly platform-wide API budget in USD.');

        \App\Support\Audit::log('economics_updated', null, [
            'field' => 'platform_budget_usd',
            'new_value' => $request->platform_budget_usd
        ]);

        return back()->with('success', 'Economic policy updated successfully.');
    }

    /**
     * Performance Tracking Dashboard
     */
    public function performance()
    {
        \Illuminate\Support\Facades\Gate::authorize('view-system-logs');

        // High-Performance Optimization: Cache analytical aggregations for 5 minutes
        $perfData = Cache::remember('admin_performance_metrics', 300, function () {
            $totalCalls = Call::count();
            $successfulCalls = Call::whereNotIn('status', ['failed', 'error', 'busy', 'rejected'])
                ->orWhereNull('status')
                ->count();
            $successRate = $totalCalls > 0 ? round(($successfulCalls / $totalCalls) * 100, 1) : 100;

            // Provider Metrics (Last 24 Hours)
            $providerMetrics = \App\Models\ApiRequestLog::select(
                'provider',
                DB::raw('AVG(latency_ms) as avg_latency'),
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('SUM(CASE WHEN is_success = true THEN 1 ELSE 0 END) as successful_requests')
            )
                ->where('created_at', '>=', now()->subDay())
                ->groupBy('provider')
                ->get()
                ->map(function ($item) {
                    $item->success_rate = ($item->total_requests > 0) 
                        ? round(($item->successful_requests / $item->total_requests) * 100, 1) 
                        : 100;
                    return $item;
                })->keyBy('provider');

            // Latency over time (Last 24 hours) — Portable SQL (no TO_CHAR)
            $latencyOverTime = \App\Models\ApiRequestLog::select(
                DB::raw("DATE(created_at) || ' ' || EXTRACT(HOUR FROM created_at) || ':00' as hour"),
                DB::raw('AVG(latency_ms) as avg_latency')
            )
                ->where('created_at', '>=', now()->subDay())
                ->groupByRaw("DATE(created_at), EXTRACT(HOUR FROM created_at)")
                ->orderByRaw("DATE(created_at), EXTRACT(HOUR FROM created_at)")
                ->get();

            // Historical Call Status (Last 30 days)
            $callsByStatus = Call::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw("SUM(CASE WHEN status NOT IN ('failed', 'error', 'busy', 'rejected') OR status IS NULL THEN 1 ELSE 0 END) as successful"),
                DB::raw("SUM(CASE WHEN status IN ('failed', 'error', 'busy', 'rejected') THEN 1 ELSE 0 END) as failed")
            )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return [
                'providerMetrics' => $providerMetrics,
                'latencyOverTime' => $latencyOverTime,
                'successRate' => $successRate,
                'avgResponseTime' => $providerMetrics->avg('avg_latency') ?? 0,
                'errorRate' => $totalCalls > 0 ? round((($totalCalls - $successfulCalls) / $totalCalls) * 100, 1) : 0,
                'callsByStatus' => $callsByStatus,
                'totalCalls' => $totalCalls,
                'successfulCalls' => $successfulCalls,
            ];
        });

        return view('admin.performance', $perfData);
    }

    /**
     * Export Users to CSV
     */
    public function exportUsers()
    {
        \Illuminate\Support\Facades\Gate::authorize('view-economics');

        // Streaming CSV: Memory-safe for any dataset size
        $fileName = 'users-export-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Agents', 'Calls', 'Joined', 'Admin']);

            User::withCount([
                'agents',
                'agents as total_calls' => function ($q) {
                    $q->join('calls', 'agents.id', '=', 'calls.agent_id');
                }
            ])->cursor()->each(function ($user) use ($handle) {
                fputcsv($handle, [
                    $user->name,
                    $user->email,
                    $user->agents_count,
                    $user->total_calls ?? 0,
                    $user->created_at->format('Y-m-d'),
                    $user->is_admin ? 'Yes' : 'No',
                ]);
            });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    /**
     * Export Calls to CSV — Streaming (Memory-Safe)
     */
    public function exportCalls(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('view-economics');

        $query = Call::with('agent.user');

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $fileName = 'calls-export-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Call ID', 'User', 'Agent', 'Duration (s)', 'Status', 'Outcome', 'Date']);

            $query->cursor()->each(function ($call) use ($handle) {
                fputcsv($handle, [
                    $call->call_id ?? 'N/A',
                    $call->agent->user->name ?? 'N/A',
                    $call->agent->name ?? 'N/A',
                    $call->duration ?? 0,
                    $call->status ?? 'completed',
                    $call->outcome ?? 'N/A',
                    $call->created_at->format('Y-m-d H:i:s'),
                ]);
            });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    // Helper methods
    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Connected'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkDiskSpace()
    {
        $free = disk_free_space('/');
        $total = disk_total_space('/');
        $used = $total - $free;
        $percentage = round(($used / $total) * 100, 2);

        return [
            'status' => $percentage < 90 ? 'healthy' : 'warning',
            'used' => $this->formatBytes($used),
            'total' => $this->formatBytes($total),
            'percentage' => $percentage
        ];
    }

    private function checkQueue()
    {
        // Placeholder - implement based on your queue driver
        return ['status' => 'healthy', 'pending_jobs' => 0];
    }

    private function checkVoiceEngine()
    {
        try {
            $response = Http::timeout(0.5)->get('http://127.0.0.1:3000/health');
            return $response->successful() ? ['status' => 'healthy'] : ['status' => 'error'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Service unreachable'];
        }
    }

    private function checkTelephony()
    {
        try {
            $response = Http::timeout(0.5)->get('http://127.0.0.1:8081/health');
            return $response->successful() ? ['status' => 'healthy'] : ['status' => 'error'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Service unreachable'];
        }
    }

    /**
     * API Credentials Vault
     */
    public function credentials()
    {
        \Illuminate\Support\Facades\Gate::authorize('view-economics');

        $credentials = \App\Models\SystemCredential::all()->groupBy('provider');
        
        return view('admin.credentials', compact('credentials'));
    }

    /**
     * Update a specific credential
     */
    public function updateCredential(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('view-economics');

        $request->validate([
            'id' => 'required|exists:system_credentials,id',
            'value' => 'required|string',
        ]);

        $credential = \App\Models\SystemCredential::findOrFail($request->id);
        
        \App\Support\Audit::log('credential_updated', $credential, [
            'key_name' => $credential->name,
            'action' => 'Value rotated (masked for security)'
        ]);

        $credential->update([
            'value' => $request->value,
            'is_valid' => false, // Reset validation status
            'last_tested_at' => null,
        ]);

        return back()->with('success', "Credential {$credential->name} updated successfully.");
    }

    /**
     * View Audit Trails
     */
    public function audits()
    {
        \Illuminate\Support\Facades\Gate::authorize('view-economics');

        $audits = \App\Models\AuditLog::with('user')
            ->latest()
            ->paginate(50);
            
        return view('admin.audits', compact('audits'));
    }

    /**
     * Test a specific credential's connectivity
     */
    public function testCredential(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('view-economics');

        $credential = \App\Models\SystemCredential::findOrFail($request->id);
        $isValid = false;
        $message = "Unknown provider.";

        try {
            switch ($credential->provider) {
                case 'groq':
                    $response = Http::timeout(3)->withToken($credential->value)->get('https://api.groq.com/openai/v1/models');
                    $isValid = $response->successful();
                    $message = $isValid ? "Connection successful!" : "Invalid Groq key.";
                    break;

                case 'elevenlabs':
                    $response = Http::timeout(3)->withHeaders(['xi-api-key' => $credential->value])->get('https://api.elevenlabs.io/v1/user');
                    $isValid = $response->successful();
                    $message = $isValid ? "Connection successful!" : "Invalid ElevenLabs key.";
                    break;

                case 'deepgram':
                    $response = Http::timeout(3)->withToken($credential->value, 'Token')->get('https://api.deepgram.com/v1/projects');
                    $isValid = $response->successful();
                    $message = $isValid ? "Connection successful!" : "Invalid Deepgram key.";
                    break;

                case 'openai':
                    $response = Http::timeout(3)->withToken($credential->value)->get('https://api.openai.com/v1/models');
                    $isValid = $response->successful();
                    $message = $isValid ? "Connection successful!" : "Invalid OpenAI key.";
                    break;

                case 'zadarma':
                    // Zadarma usually requires a more complex signature, 
                    // for now we'll just check if the secret/key are present.
                    $isValid = strlen($credential->value) > 10;
                    $message = $isValid ? "Key format valid (Partial check)" : "Invalid Zadarma credential.";
                    break;

                case 'twilio':
                    // We need SID and Token for a real test, but we'll check format for individual rows
                    if (str_starts_with($credential->name, 'twilio_sid')) {
                        $isValid = str_starts_with($credential->value, 'AC') && strlen($credential->value) === 34;
                        $message = $isValid ? "Valid Twilio SID format." : "Invalid SID (must start with AC and be 34 chars).";
                    } else {
                        $isValid = strlen($credential->value) > 20;
                        $message = $isValid ? "Token format valid." : "Token too short.";
                    }
                    break;

                case 'africas_talking':
                    $isValid = strlen($credential->value) > 2;
                    $message = $isValid ? "Credential format valid." : "Too short.";
                    break;

                case 'google_maps':
                    // Quick validation call to Google Places
                    $response = Http::timeout(3)->get("https://maps.googleapis.com/maps/api/place/details/json", [
                        'place_id' => 'ChIJN1t_tDeuEmsRUsoyG83frY4', // Google Sydney
                        'key' => $credential->value
                    ]);
                    // 403 or REQUEST_DENIED means key is bad
                    $isValid = $response->successful() && ($response->json()['status'] !== 'REQUEST_DENIED');
                    $message = $isValid ? "Valid Maps API Key!" : "Invalid Maps Key or API disabled.";
                    break;
            }
        } catch (\Exception $e) {
            $isValid = false;
            $message = "Connection failed: " . $e->getMessage();
        }

        $credential->update([
            'is_valid' => $isValid,
            'last_tested_at' => now(),
        ]);

        return back()->with($isValid ? 'success' : 'error', "Test for {$credential->name}: {$message}");
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
