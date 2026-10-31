<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'access_token',
        'refresh_token',
        'status',
        'settings',
        'last_synced_at'
    ];

    protected $casts = [
        'settings' => 'encrypted:array',
        'last_synced_at' => 'datetime',
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
    ];

    /**
     * Get the user that owns the integration.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
