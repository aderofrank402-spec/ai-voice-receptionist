#!/usr/bin/env php
<?php
/**
 * Verification script for the new crawl stack (crawl service + Laravel webhook + job).
 * Run from project root: php scripts/verify_crawl_stack.php
 *
 * Prerequisites:
 * - .env has CRAWLER_SERVICE_URL and CRAWLER_SERVICE_SECRET set
 * - Crawl service running: cd crawler && npm run serve
 * - Laravel running: php artisan serve (or your APP_URL)
 * - Queue worker: php artisan queue:work (in another terminal)
 * - Reverb (if using WebSockets): php artisan reverb:start (in another terminal)
 */

$baseDir = dirname(__DIR__);
if (!is_file($baseDir . '/vendor/autoload.php')) {
    echo "Run from project root. composer install first.\n";
    exit(1);
}

require $baseDir . '/vendor/autoload.php';
$app = require_once $baseDir . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$errors = [];
$ok = [];

// 1. Config
$serviceUrl = rtrim(config('crawler.service_url', ''), '/');
$secret = config('crawler.service_secret', '');
$appUrl = rtrim(config('app.url', ''), '/');

if ($serviceUrl === '') {
    $errors[] = 'CRAWLER_SERVICE_URL is empty in .env';
} else {
    $ok[] = 'CRAWLER_SERVICE_URL is set: ' . $serviceUrl;
}

if ($secret === '') {
    $errors[] = 'CRAWLER_SERVICE_SECRET is empty in .env (crawl service callbacks will be rejected)';
} else {
    $ok[] = 'CRAWLER_SERVICE_SECRET is set';
}

// 2. Crawl service health
if ($serviceUrl !== '') {
    $healthUrl = $serviceUrl . '/health';
    $ch = curl_init($healthUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code === 200 && $body && str_contains($body, 'ok')) {
        $ok[] = 'Crawl service health OK (' . $healthUrl . ')';
    } else {
        $errors[] = 'Crawl service not reachable or not OK: ' . $healthUrl . ' (HTTP ' . $code . '). Start it with: cd crawler && npm run serve';
    }
}

// 3. & 4. Laravel webhook (only when secret is set)
$webhookUrl = $appUrl . '/api/crawler-progress';
if ($secret !== '') {
    $ch = curl_init($webhookUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['sessionId' => '1', 'userId' => '1']),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-Crawler-Secret: wrong-secret'],
        CURLOPT_TIMEOUT => 5,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code === 401) {
        $ok[] = 'Webhook rejects wrong secret (401)';
    } else {
        $errors[] = 'Webhook with wrong secret: expected 401, got ' . $code . (trim($body ?? '') ? ' ' . substr(trim($body), 0, 100) : '');
    }
}
if ($secret !== '') {
    $ch = curl_init($webhookUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'sessionId' => '1',
            'userId' => '1',
            'phase' => 'analyzing',
            'userMessage' => 'Test',
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-Crawler-Secret: ' . $secret],
        CURLOPT_TIMEOUT => 5,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code >= 200 && $code < 300) {
        $ok[] = 'Webhook accepts valid secret (200)';
    } else {
        $errors[] = 'Webhook with valid secret: expected 200, got ' . $code . (trim($body ?? '') ? ' ' . substr(trim($body), 0, 200) : '');
    }
}

// 5. Optional: trigger a real crawl (comment out if you don't want to hit a real URL)
$triggerCrawl = (isset($argv[1]) && $argv[1] === '--trigger-crawl');
if ($triggerCrawl && $serviceUrl !== '' && $secret !== '') {
    $ch = curl_init($serviceUrl . '/crawl');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'url' => 'https://example.com',
            'session_id' => '1',
            'user_id' => '1',
            'callback_base_url' => $appUrl,
            'auth_token' => $secret,
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 10,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code === 202) {
        $ok[] = 'Crawl trigger accepted (202). Check Laravel logs and Reverb for progress.';
    } else {
        $errors[] = 'Crawl trigger: expected 202, got ' . $code . ' ' . substr($body ?? '', 0, 300);
    }
}

// Output
foreach ($ok as $msg) {
    echo '[OK] ' . $msg . "\n";
}
foreach ($errors as $msg) {
    echo '[FAIL] ' . $msg . "\n";
}

if (!empty($errors)) {
    echo "\nFix the failures above. For full E2E:\n";
    echo "  1. In .env set CRAWLER_SERVICE_URL=http://127.0.0.1:3090 and CRAWLER_SERVICE_SECRET=<secret>\n";
    echo "  2. Start crawl service: cd crawler && npm run serve\n";
    echo "  3. Start Laravel: php artisan serve\n";
    echo "  4. Start queue worker: php artisan queue:work\n";
    echo "  5. (Optional) Start Reverb: php artisan reverb:start\n";
    echo "  6. Open /onboarding, paste a URL, press Enter. Results within 60s.\n";
    echo "  7. Run with --trigger-crawl to test trigger: php scripts/verify_crawl_stack.php --trigger-crawl\n";
    exit(1);
}

echo "\nAll checks passed.\n";
exit(0);
