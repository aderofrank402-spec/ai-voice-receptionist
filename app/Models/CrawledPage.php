<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrawledPage extends Model
{
    protected $fillable = [
        'crawl_run_id',
        'url',
        'title',
        'http_status',
        'content_hash',
        'raw_text',
        'raw_html',
        'page_type_guess',
        'confidence_score',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function crawlRun(): BelongsTo
    {
        return $this->belongsTo(CrawlRun::class);
    }
}
