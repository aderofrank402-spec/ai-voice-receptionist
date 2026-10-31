<?php

namespace App\Listeners;

use App\Events\CrawledContentCreated;
use App\Jobs\ProcessCrawlJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class StartRefinery implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CrawledContentCreated $event): void
    {
        Log::info("Refinery: Triggered for Crawl Run ID: {$event->crawlRun->id} ({$event->crawlRun->pages_crawled} pages)");

        // Dispatch the heavy lifting to the queue (redis:default)
        ProcessCrawlJob::dispatch($event->crawlRun);
    }
}
