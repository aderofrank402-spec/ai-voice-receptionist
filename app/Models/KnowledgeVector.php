<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeVector extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
        'embedding' => 'array', // For JSON storage in SQLite, raw text in Postgres
    ];

    /**
     * Get the user that owns the vector.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
