<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Call extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'call_id',
        'caller_number',
        'status',
        'duration',
        'cost',
        'stt_cost_usd',
        'llm_cost_usd',
        'tts_cost_usd',
        'telephony_cost_usd',
        'total_cost_kes',
        'tokens_used',
        'characters_used',
        'transcript',
        'recording_url',
        'lead_score',
        'primary_intent',
        'outcome',
        'metadata',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'transcript' => 'array',
        'cost'       => 'decimal:6',
        'stt_cost_usd' => 'decimal:6',
        'llm_cost_usd' => 'decimal:6',
        'tts_cost_usd' => 'decimal:6',
        'telephony_cost_usd' => 'decimal:6',
        'total_cost_kes' => 'decimal:2',
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo(\App\Models\Agent::class);
    }
}
