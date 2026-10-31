<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnboardingSession extends Model
{
    protected $fillable = [
        'user_id',
        'transcript',
        'extracted_data',
        'checklist',
        'status',
    ];

    protected $casts = [
        'transcript' => 'array',
        'extracted_data' => 'array',
        'checklist' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
