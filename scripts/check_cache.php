<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;

// We need to find the session ID. The test script used "TEST_SESSION_" + timestamp.
// Since we don't know the exact timestamp, let's just dump the logs or guess.
// actually, let's just trigger a new job and wait 30s and dump it.

// Use the hardcoded session ID from the previous test run
$sessionId = "TEST_SESSION_1738612537";

echo "Checking Cache for Session: $sessionId\n";

$status = Cache::get("onboarding_status_{$sessionId}");
echo "Status: " . ($status ?? 'NULL') . "\n";

$data = Cache::get("onboarding_data_{$sessionId}");
echo "Data Found:\n";
print_r($data);

