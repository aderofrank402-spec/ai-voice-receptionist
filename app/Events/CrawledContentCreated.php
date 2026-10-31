<?php

namespace App\Events;

use App\Models\CrawlRun;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CrawledContentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $crawlRun;

    /**
     * Create a new event instance.
     */
    public function __construct(CrawlRun $crawlRun)
    {
        $this->crawlRun = $crawlRun;
    }
}
