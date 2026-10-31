<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiUsageLog extends Model
{
    protected $fillable = [
        'user_id',
        'call_id',
        'service',
        'endpoint',
        'tokens_used',
        'duration_seconds',
        'cost_usd',
        'response_time_ms',
        'status',
        'error_message',
    ];

    protected $casts = [
        'cost_usd' => 'decimal:4',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function call()
    {
        return $this->belongsTo(Call::class);
    }
}
