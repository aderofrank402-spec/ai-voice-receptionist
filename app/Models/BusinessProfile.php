<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessProfile extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'hours_structured' => 'array',
        'contacts' => 'array',
        'services' => 'array',
        'needs_review' => 'boolean',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
