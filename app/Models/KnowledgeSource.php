<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KnowledgeSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'agent_id',
        'type',
        'title',
        'content',
        'file_path',
        'status',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the source.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the agent that uses this source.
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
