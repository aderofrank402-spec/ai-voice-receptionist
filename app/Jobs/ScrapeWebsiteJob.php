<?php

namespace App\Jobs;

use App\Events\CrawlerProgress;
use App\Models\OnboardingSession;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * Dispatched when the user submits a URL in onboarding. Broadcasts "analyzing",
 * then runs the crawler via Docker (Phase 2: job → Docker → crawler runs).
 */
class ScrapeWebsiteJob implements ShouldQueue
{
    use Queueable;

    public $url;
    public $sessionId;

    public $tries = 1;
    public $timeout = 300;       // 5 minute hard limit — prevents infinite hangs
    public $failOnTimeout = true; // Mark as failed so the queue moves on

    public function __construct(string $url, $sessionId)
    {
        $this->url = $url;
        $this->sessionId = $sessionId;
        $this->onConnection(config('queue.default') === 'sync' ? 'sync' : 'database');
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $correlationId = (string) Str::uuid();
        Log::info('ScrapeWebsiteJob: crawl requested', [
            'correlation_id' => $correlationId,
            'url' => $this->url,
            'session_id' => $this->sessionId,
        ]);

        $userId = OnboardingSession::find($this->sessionId)?->user_id;
        $userIdStr = $userId !== null ? (string) $userId : null;

        broadcast(new CrawlerProgress(
            sessionId: (string) $this->sessionId,
            userId: $userIdStr,
            phase: 'analyzing',
            userMessage: 'Preparing to scan your website...'
        ));

        $composeFile = config('crawler.docker_compose_file', 'docker-compose.crawler.yml');
        $cwd = base_path();

        // Safe for shell: compose path and URL escaped
        $composeArg = escapeshellarg($composeFile);
        $urlArg = escapeshellarg($this->url);
        $command = "docker compose -f {$composeArg} run --rm crawler npx tsx src/main.ts --url={$urlArg}";

        $process = Process::fromShellCommandline($command, $cwd, null, null, null);
        $process->run();

        $stdout = $process->getOutput();
        $stderr = $process->getErrorOutput();

        if ($process->isSuccessful()) {
            Log::info('ScrapeWebsiteJob: crawler finished', [
                'correlation_id' => $correlationId,
                'url' => $this->url,
                'output_length' => strlen($stdout),
            ]);
            if (strlen($stdout) > 0 && strlen($stdout) < 10000) {
                Log::debug('ScrapeWebsiteJob: crawler output', ['output' => $stdout]);
            } elseif (strlen($stdout) >= 10000) {
                Log::debug('ScrapeWebsiteJob: crawler output (truncated)', ['output' => substr($stdout, 0, 5000) . '...']);
            }
            broadcast(new CrawlerProgress(
                sessionId: (string) $this->sessionId,
                userId: $userIdStr,
                phase: 'completed',
                userMessage: 'Crawl finished (Phase 2). Results not yet sent to app.',
                done: true
            ));
        } else {
            $errorMsg = trim($stderr) !== '' ? trim($stderr) : $process->getExitCodeText();
            Log::warning('ScrapeWebsiteJob: crawler failed', [
                'correlation_id' => $correlationId,
                'url' => $this->url,
                'exit_code' => $process->getExitCode(),
                'stderr' => $stderr,
            ]);
            broadcast(new CrawlerProgress(
                sessionId: (string) $this->sessionId,
                userId: $userIdStr,
                error: 'Crawler failed: ' . $errorMsg,
                done: true
            ));
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ScrapeWebsiteJob failed', [
            'url' => $this->url,
            'session_id' => $this->sessionId,
            'error' => $exception->getMessage(),
        ]);
        $userId = OnboardingSession::find($this->sessionId)?->user_id;
        broadcast(new CrawlerProgress(
            sessionId: (string) $this->sessionId,
            userId: $userId !== null ? (string) $userId : null,
            error: 'Job failed: ' . $exception->getMessage(),
            done: true
        ));
    }
}
