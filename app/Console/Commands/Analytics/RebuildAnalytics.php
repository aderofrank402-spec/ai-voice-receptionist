<?php

namespace App\Console\Commands\Analytics;

use App\Models\Agent;
use App\Models\Call;
use App\Models\DailyAgentMetric;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:rebuild {--agent= : Rebuild for a specific agent ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans historical call logs and rebuilds the daily_agent_metrics table for O(1) dashboard performance.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Analytics Rebuild...');
        
        // 1. Clear existing metrics to avoid double-counting if we are rebuilding from scratch
        if ($this->option('agent')) {
            DailyAgentMetric::where('agent_id', $this->option('agent'))->delete();
        } else {
            DB::table('daily_agent_metrics')->truncate();
        }

        // 2. Aggregate counts from the calls table grouped by Agent and Day
        // We calculate success_count and after_hours_count using SQL CASE for high speed
        $query = DB::table('calls')
            ->select(
                'agent_id',
                DB::raw('DATE(created_at) as call_date'),
                DB::raw('COUNT(*) as total_calls'),
                DB::raw('SUM(duration) as total_seconds'),
                DB::raw("COUNT(CASE WHEN outcome ILIKE '%Handled%' OR outcome ILIKE '%Booked%' OR outcome ILIKE '%Confirmed%' OR outcome ILIKE '%Resolved%' THEN 1 END) as success_count"),
                DB::raw("COUNT(CASE WHEN NOT (
                    EXTRACT(DOW FROM created_at AT TIME ZONE 'Africa/Nairobi') BETWEEN 1 AND 6 
                    AND EXTRACT(HOUR FROM created_at AT TIME ZONE 'Africa/Nairobi') BETWEEN 7 AND 19
                ) THEN 1 END) as after_hours_count")
            )
            ->whereNotNull('agent_id')
            ->groupBy('agent_id', 'call_date');

        if ($this->option('agent')) {
            $query->where('agent_id', $this->option('agent'));
        }

        $results = $query->get();

        $this->info("Found " . count($results) . " unique agent/day historical segments. Inserting...");

        foreach ($results as $row) {
            DailyAgentMetric::create([
                'agent_id' => $row->agent_id,
                'date' => $row->call_date,
                'total_calls' => $row->total_calls,
                'total_seconds' => $row->total_seconds ?? 0,
                'success_count' => $row->success_count,
                'after_hours_count' => $row->after_hours_count,
            ]);
        }

        $this->info('Analytics Rebuild Complete. Dashboard performance is now optimized.');
    }
}
