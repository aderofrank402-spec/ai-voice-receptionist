<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CrawlerWebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Crawl service webhook: progress and final result (authenticated by X-Crawler-Secret)
Route::post('/crawler-progress', [CrawlerWebhookController::class, 'progress'])
    ->middleware('crawler.secret');

Route::prefix('v1')->group(function () {

    // ── PUBLIC: Health Check (no secrets exposed) ────────────────────────
    Route::get('/system/health', [AppointmentController::class, 'getSystemHealth'])
        ->middleware('throttle:30,1');

    // ── M-Pesa Callback: Server-to-server from Safaricom (no auth possible) ──
    Route::post('/payments/callback', [PaymentController::class, 'callback']);

    // ── INTERNAL SYSTEM ROUTES: Authenticated by shared secret ───────────
    // These are called by the Voice Engine and Telephony Server, not by browsers.
    Route::middleware(['crawler.secret', 'throttle:120,1'])->group(function () {
        Route::get('/agents/config', [AppointmentController::class, 'getConfig']);
        Route::get('/agents/by-number', [AppointmentController::class, 'getAgentByNumber']);
        Route::post('/calls/log', [AppointmentController::class, 'logCall']);
        Route::get('/system/credentials', [AppointmentController::class, 'getCredentials']);
        Route::post('/system/trace', [AppointmentController::class, 'saveTrace']);
    });

    // ── USER-FACING ROUTES: Authenticated by Sanctum token ───────────────
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::get('/appointments/availability', [AppointmentController::class, 'checkAvailability']);
        Route::post('/appointments/book', [AppointmentController::class, 'book']);
        Route::post('/payments/stk', [PaymentController::class, 'stkPush']);
    });

    // ── DEV-ONLY: Payment simulator (never available in production) ──────
    if (app()->environment('local', 'testing')) {
        Route::post('/payments/simulate', [PaymentController::class, 'simulateCallback']);
    }
});
