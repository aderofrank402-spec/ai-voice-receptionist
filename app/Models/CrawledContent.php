<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrawledContent extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the crawled content.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
