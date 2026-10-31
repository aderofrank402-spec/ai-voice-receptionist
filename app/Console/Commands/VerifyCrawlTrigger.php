<?php

namespace App\Console\Commands;

use App\Jobs\ScrapeWebsiteJob;
use App\Models\OnboardingSession;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Verifies that "paste URL in chat" triggers the backend: same URL detection and
 * dispatch as OnboardingController::chat(), then job runs (or is queued).
 *
 * Run: php artisan crawl:verify-triggerwe are not going to tackle
 */
class VerifyCrawlTrigger extends Command
{
    protected $signature = 'crawl:verify-trigger
                            {--url=https://example.com : URL to use in the test message}
                            {--no-sync : Do not force sync queue; use default driver}';

    protected $description = 'Verify that posting a URL to the onboarding chat triggers the crawl job (same path as frontend)';

    public function handle(): int
    {
        $testUrl = $this->option('url');
        $testMessage = $testUrl; // Same as user pasting the URL

        $this->line('Verify: "Paste URL in chat" → Laravel → dispatch ScrapeWebsiteJob');
        $this->line('Using message: ' . $testMessage);
        $this->line('');

        // --- 1. Same URL detection as OnboardingController::chat() ---
        if (!preg_match('/(https?:\/\/[^\s]+)|(www\.[^\s]+)/i', $testMessage, $matches)) {
            $this->error('Step 1 FAIL: URL regex did not match the test message.');
            return 1;
        }
        $url = $matches[0];
        if (!str_starts_with($url, 'http')) {
            $url = 'https://' . $url;
        }
        $this->info('Step 1 OK: URL detected (same regex as OnboardingController): ' . $url);

        // --- 2. Same session resolution as controller (first user + session) ---
        $user = User::first();
        if (!$user) {
            $this->error('Step 2 FAIL: No user in database. Create a user (register or seed).');
            return 1;
        }
        $session = OnboardingSession::firstOrCreate(
            ['user_id' => $user->id],
            ['status' => 'active', 'transcript' => []]
        );
        $this->info('Step 2 OK: User id=' . $user->id . ', OnboardingSession id=' . $session->id);

        // --- 3. Dispatch job (exactly as controller does) ---
        $useSync = !$this->option('no-sync');
        if ($useSync) {
            config(['queue.default' => 'sync']);
            $this->line('Step 3: Dispatching job with queue driver=sync (job runs in-process)...');
        } else {
            $this->line('Step 3: Dispatching job with queue driver=' . config('queue.default') . '...');
        }

        try {
            ScrapeWebsiteJob::dispatch($url, $session->id);
        } catch (\Throwable $e) {
            $this->error('Step 3 FAIL: Dispatch threw: ' . $e->getMessage());
            return 1;
        }

        $this->info('Step 3 OK: Job dispatched' . ($useSync ? ' and executed (sync).' : '.'));

        $this->line('');
        $this->info('Summary: Backend trigger path works (URL → dispatch → job run/queued).');
        if (!$useSync) {
            $this->line('Run: php artisan queue:work — to process the queued job.');
        }
        $this->line('');
        $this->comment('To verify via the real chat UI: log in → /onboarding → paste a URL and send. You should see the crawl/terminal view and no assistant reply (silent_start).');
        return 0;
    }
}
