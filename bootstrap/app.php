<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__.'/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // CSRF Exemptions: Only M-Pesa callback (server-to-server from Safaricom)
        $middleware->validateCsrfTokens(except: [
            'api/v1/payments/callback',
        ]);
        
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'crawler.secret' => \App\Http\Middleware\ValidateCrawlerSecret::class,
            'not_suspended' => \App\Http\Middleware\EnsureUserNotSuspended::class,
        ]);

        // Security Headers: Applied to all web requests
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('app:prune-api-logs')->daily();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
