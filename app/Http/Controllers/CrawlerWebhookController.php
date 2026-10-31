<?php

namespace App\Http\Controllers;

use App\Events\CrawledContentCreated;
use App\Events\CrawlerProgress;
use App\Models\CrawlRun;
use App\Models\CrawledPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Receives progress and final result from the crawl service.
 * Broadcasts to the frontend and persists final data (with idempotency by correlation_id).
 */
class CrawlerWebhookController extends Controller
{
    private const IDEMPOTENCY_TTL_SECONDS = 86400; // 24h

    /**
     * Crawl service POST: sessionId, userId, correlation_id?, phase?, userMessage?, pageDiscovered?, finalJson?, error?, done?, status?, startUrl?
     */
    public function progress(Request $request): JsonResponse
    {
        $correlationId = $request->input('correlation_id');
        $sessionId = $request->input('sessionId');
        $userId = $request->input('userId');
        $phase = $request->input('phase');
        $userMessage = $request->input('userMessage');
        $pageDiscovered = $request->input('pageDiscovered');
        $finalJson = $request->input('finalJson');
        $error = $request->input('error');
        $done = (bool) $request->input('done', false);
        $status = $request->input('status');

        Log::info('CrawlerWebhook: progress', [
            'correlation_id' => $correlationId,
            'session_id' => $sessionId,
            'phase' => $phase,
            'done' => $done,
        ]);

        if (empty($sessionId) || empty($userId)) {
            return response()->json(['error' => 'sessionId and userId required'], 400);
        }

        broadcast(new CrawlerProgress(
            sessionId: (string) $sessionId,
            userId: (string) $userId,
            phase: $phase ? (string) $phase : null,
            userMessage: $userMessage ? (string) $userMessage : null,
            pageDiscovered: is_array($pageDiscovered) ? $pageDiscovered : null,
            finalJson: is_array($finalJson) ? $finalJson : null,
            error: $error ? (string) $error : null,
            done: $done,
            status: $status ? (string) $status : null
        ));

        if ($done && is_array($finalJson) && ! empty($finalJson)) {
            $this->persistOnce($correlationId, $request->input('startUrl'), (int) $userId, $finalJson);
        }

        return response()->json(['ok' => true]);
    }

    private function persistOnce(?string $correlationId, ?string $startUrl, int $userId, array $pages): void
    {
        if (empty($startUrl)) {
            return;
        }

        $idempotencyKey = $correlationId !== null && $correlationId !== ''
            ? 'crawl_persisted_' . $correlationId
            : 'crawl_persisted_' . $userId . '_' . $startUrl . '_' . md5(json_encode($pages));

        if (Cache::has($idempotencyKey)) {
            Log::info('CrawlerWebhook: skip duplicate persist (idempotent)', ['idempotency_key' => $idempotencyKey]);
            return;
        }

        Cache::put($idempotencyKey, true, self::IDEMPOTENCY_TTL_SECONDS);

        $this->persistCrawlRun($startUrl, $userId, $pages);
    }

    private function persistCrawlRun(?string $startUrl, int $userId, array $pages): void
    {
        if (empty($startUrl)) {
            return;
        }

        try {
            $crawlVersion = (string) Str::uuid();
            $crawlRun = CrawlRun::create([
                'user_id' => $userId,
                'crawl_version' => $crawlVersion,
                'start_url' => $startUrl,
                'status' => 'running',
                'pages_found' => count($pages),
                'pages_crawled' => 0,
                'started_at' => now(),
            ]);

            $successfulPages = 0;
            foreach ($pages as $page) {
                try {
                    CrawledPage::create([
                        'crawl_run_id' => $crawlRun->id,
                        'url' => $page['url'] ?? '',
                        'title' => $page['title'] ?? null,
                        'http_status' => 200,
                        'content_hash' => md5($page['text'] ?? ''),
                        'raw_text' => $page['text'] ?? null,
                        'raw_html' => null,
                        'page_type_guess' => 'unknown',
                        'confidence_score' => 0.5,
                        'metadata' => ['source' => 'velocity_crawler'],
                    ]);
                    $successfulPages++;
                } catch (\Exception $e) {
                    Log::error('Failed to persist page: ' . $e->getMessage());
                }
            }

            $crawlRun->update([
                'status' => 'completed',
                'pages_crawled' => $successfulPages,
                'finished_at' => now(),
            ]);

            CrawledContentCreated::dispatch($crawlRun);
        } catch (\Exception $e) {
            Log::error('CrawlerWebhook persistCrawlRun: ' . $e->getMessage());
        }
    }
}
