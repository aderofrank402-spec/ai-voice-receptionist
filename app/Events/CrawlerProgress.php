<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast immediately (ShouldBroadcastNow) so the UI receives events during the crawl.
 * If we used ShouldBroadcast, events would be queued and the same worker running
 * ScrapeWebsiteJob would not process them until the 5-min job finishes → no updates for 5 min.
 */
class CrawlerProgress implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param string $sessionId OnboardingSession id (for payload/logic)
     * @param string|null $userId User id for the private channel (frontend subscribes to crawler.{userId})
     * @param string|null $log Raw log line (legacy; prefer phase + userMessage for UI)
     * @param array|null $pageDiscovered {url, title} when a page is found
     * @param array|null $finalJson Parsed crawl result (when done)
     * @param string|null $error Error message if failed
     * @param bool $done Whether the crawl is finished
     * @param string|null $status e.g. 'COMPLETED'
     * @param string|null $phase One of: analyzing, crawling, extracting, completed
     * @param string|null $userMessage Short user-friendly message for the current step
     * @param array|null $logs Batch of raw crawler output lines (for live log in UI)
     */
    public function __construct(
        public string $sessionId,
        public ?string $userId = null,
        public ?string $log = null,
        public ?array $pageDiscovered = null,
        public ?array $finalJson = null,
        public ?string $error = null,
        public bool $done = false,
        public ?string $status = null,
        public ?string $phase = null,
        public ?string $userMessage = null,
        public ?array $logs = null
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     * Uses user id so the frontend (Echo.private('crawler.' + Auth::id())) receives events.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn(): PrivateChannel
    {
        $channelKey = $this->userId !== null && $this->userId !== '' ? $this->userId : $this->sessionId;
        return new PrivateChannel("crawler.{$channelKey}");
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'progress';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'log' => $this->log,
            'logs' => $this->logs,
            'pageDiscovered' => $this->pageDiscovered,
            'finalJson' => $this->finalJson,
            'error' => $this->error,
            'done' => $this->done,
            'status' => $this->status,
            'phase' => $this->phase,
            'userMessage' => $this->userMessage,
            'timestamp' => now()->toISOString(),
        ];
    }
}
