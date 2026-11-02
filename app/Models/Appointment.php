<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'agent_id',
        'status',
        'scheduled_at',
        'duration_minutes',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];
}
