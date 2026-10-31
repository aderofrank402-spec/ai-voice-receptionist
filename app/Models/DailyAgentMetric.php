<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyAgentMetric extends Model
{
    protected $fillable = [
        'agent_id',
        'date',
        'total_calls',
        'total_seconds',
        'success_count',
        'after_hours_count',
    ];

    /**
     * Increment a metric for a specific agent and date.
     * Uses atomic arithmetic to prevent race conditions during high volume.
     */
    public static function incrementMetric($agentId, $date, array $values)
    {
        $metric = self::firstOrCreate(
            ['agent_id' => $agentId, 'date' => $date],
            ['total_calls' => 0, 'total_seconds' => 0, 'success_count' => 0, 'after_hours_count' => 0]
        );

        $metric->increment('total_calls', $values['total_calls'] ?? 0);
        $metric->increment('total_seconds', $values['total_seconds'] ?? 0);
        $metric->increment('success_count', $values['success_count'] ?? 0);
        $metric->increment('after_hours_count', $values['after_hours_count'] ?? 0);
        
        return $metric;
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
