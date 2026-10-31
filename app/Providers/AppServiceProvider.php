<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Broadcast;
use App\Events\CrawledContentCreated;
use App\Listeners\StartRefinery;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Opcodes\LogViewer\Facades\LogViewer;
use App\Models\User;
use App\Logs\VoiceEngineLog;
use App\Logs\TelephonyLog;
use App\Logs\AuditLog as LogViewerAudit;
use App\Support\Audit;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Analytics Snapshot: Monitor all calls for real-time high-performance aggregation
        \App\Models\Call::observe(\App\Observers\CallObserver::class);


        // Log Auditing: Login
        Event::listen(Login::class, function ($event) {
            Audit::log('user_login', $event->user);
        });

        // Log Auditing: Logout
        Event::listen(Logout::class, function ($event) {
            Audit::log('user_logout', $event->user);
        });

        // Register broadcast auth route so Echo can subscribe to private channels (e.g. crawler.{id})
        Broadcast::routes(['middleware' => ['web', 'auth']]);

        // Enterprise Pipeline: Trigger Refinery when raw content is ingested
        Event::listen(CrawledContentCreated::class, StartRefinery::class);

        // Super Admin: Log Auditing Authorization
        LogViewer::auth(function ($request) {
            $user = $request->user();
            return $user && ($user->isSuperAdmin() || $user->is_admin === true);
        });

        // Register Voice Platform Log Types
        LogViewer::extend('voice-engine', VoiceEngineLog::class);
        LogViewer::extend('telephony', TelephonyLog::class);
        LogViewer::extend('audit', LogViewerAudit::class);

        // Core Authorization Gates
        Gate::define('access-admin', function (User $user) {
            return $user->hasAdminAccess();
        });

        Gate::define('view-economics', function (User $user) {
            return $user->isSuperAdmin() || $user->is_admin === true;
        });

        Gate::define('view-system-logs', function (User $user) {
            return $user->isSuperAdmin() || $user->is_admin === true;
        });

        Gate::define('manage-users', function (User $user) {
            return $user->isSuperAdmin() || $user->is_admin === true;
        });

        Gate::define('viewLogViewer', function (User $user) {
            $is_super = $user->isSuperAdmin() || $user->is_admin === true;
            if (!$is_super) {
                Log::warning("Unauthorized Log Viewer access attempt by: {$user->email}");
            }
            return $is_super;
        });
    }
}
