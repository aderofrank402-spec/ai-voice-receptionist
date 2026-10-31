<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

// Google OAuth Routes
Route::get('auth/google', [App\Http\Controllers\Auth\SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [App\Http\Controllers\Auth\SocialAuthController::class, 'handleGoogleCallback']);

Route::get('/how-it-works', function () {
    return view('how-it-works');
})->name('how-it-works');

Route::get('/pricing', function () {
    return view('pricing');
})->name('pricing');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/terms', function () {
    return view('legal.terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('legal.privacy');
})->name('privacy');

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'not_suspended'])
    ->name('dashboard');

Route::get('/calls', [App\Http\Controllers\DashboardController::class, 'calls'])
    ->middleware(['auth', 'verified', 'not_suspended'])
    ->name('calls.index');

Route::middleware(['auth', 'not_suspended'])->group(function () {
    Route::get('/onboarding', [App\Http\Controllers\OnboardingController::class, 'index'])->name('onboarding.index');
    Route::get('/onboarding/status', [App\Http\Controllers\OnboardingController::class, 'status'])->name('onboarding.status');
    Route::post('/onboarding/chat', [App\Http\Controllers\OnboardingController::class, 'chat'])->name('onboarding.chat');
    Route::get('/onboarding/form', [App\Http\Controllers\OnboardingController::class, 'form'])->name('onboarding.form');
    Route::post('/onboarding/form', [App\Http\Controllers\OnboardingController::class, 'submitForm'])->name('onboarding.submit');
    Route::get('/onboarding/review', [App\Http\Controllers\OnboardingController::class, 'review'])->name('onboarding.review');
    Route::post('/onboarding/launch', [App\Http\Controllers\OnboardingController::class, 'launch'])->name('onboarding.launch');
    Route::post('/onboarding/reset', [App\Http\Controllers\OnboardingController::class, 'reset'])->name('onboarding.reset');
    // Agent Management
    Route::get('/agents/edit', [App\Http\Controllers\AgentController::class, 'edit'])->name('agent.edit');
    Route::post('/agents/update', [App\Http\Controllers\AgentController::class, 'update'])->name('agent.update');
    Route::get('/agents/test', [App\Http\Controllers\AgentController::class, 'test'])->name('agent.test');
    Route::get('/agents/test', [App\Http\Controllers\AgentController::class, 'test'])->name('agent.test');
    Route::post('/agents/test/chat', [App\Http\Controllers\AgentController::class, 'chat'])->name('agent.test.chat');

    // Integrations
    Route::get('/integrations', [App\Http\Controllers\IntegrationsController::class, 'index'])->name('integrations.index');
    Route::match(['post', 'patch'], '/integrations', [App\Http\Controllers\IntegrationsController::class, 'update'])->name('integrations.update');
    Route::get('/integrations/connect/{provider}', [App\Http\Controllers\IntegrationsController::class, 'connect'])->name('integrations.connect');
    Route::get('/integrations/connect/{provider}/callback', [App\Http\Controllers\IntegrationsController::class, 'callback'])->name('integrations.callback');
    Route::delete('/integrations/{id}', [App\Http\Controllers\IntegrationsController::class, 'disconnect'])->name('integrations.disconnect');

    Route::get('/billing', [App\Http\Controllers\BillingController::class, 'index'])->name('billing.index');

    // Knowledge Base Management
    Route::get('/knowledge-base', [App\Http\Controllers\KnowledgeBaseController::class, 'index'])->name('agent.knowledge');
    Route::post('/knowledge-base', [App\Http\Controllers\KnowledgeBaseController::class, 'store'])->name('agent.knowledge.store');
    Route::delete('/knowledge-base/{source}', [App\Http\Controllers\KnowledgeBaseController::class, 'destroy'])->name('agent.knowledge.destroy');




    // Analytics
    Route::get('/api/analytics/volume', [App\Http\Controllers\DashboardController::class, 'getVolumeData'])->name('api.analytics.volume');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/business', [ProfileController::class, 'updateBusiness'])->name('profile.business.update');
    Route::post('/profile/api-tokens', [ProfileController::class, 'storeToken'])->name('profile.tokens.store');
    Route::delete('/profile/api-tokens/{id}', [ProfileController::class, 'destroyToken'])->name('profile.tokens.destroy');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Export
    Route::get('/export-calls', [App\Http\Controllers\DashboardController::class, 'exportCalls'])->name('export.calls');
});

// TEST ROUTE (Temporary) - Removed

// Super Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('dashboard');
    Route::get('/users', [App\Http\Controllers\AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}', [App\Http\Controllers\AdminController::class, 'userDetails'])->name('users.details');
    Route::post('/users/{user}/suspend', [App\Http\Controllers\AdminController::class, 'suspendUser'])->name('users.suspend');
    Route::post('/users/{user}/activate', [App\Http\Controllers\AdminController::class, 'activateUser'])->name('users.activate');
    Route::get('/agents', [App\Http\Controllers\AdminController::class, 'agents'])->name('agents');
    Route::get('/calls', [App\Http\Controllers\AdminController::class, 'calls'])->name('calls');
    Route::get('/engagement', [App\Http\Controllers\AdminController::class, 'engagement'])->name('engagement');
    Route::get('/performance', [App\Http\Controllers\AdminController::class, 'performance'])->name('performance');
    Route::get('/api-usage', [App\Http\Controllers\AdminController::class, 'apiUsage'])->name('api.usage');
    Route::post('/economics/update', [App\Http\Controllers\AdminController::class, 'updateEconomicsSettings'])->name('economics.update');
    Route::get('/system', [App\Http\Controllers\AdminController::class, 'system'])->name('system');
    Route::get('/credentials', [App\Http\Controllers\AdminController::class, 'credentials'])->name('credentials');
    Route::post('/credentials/update', [App\Http\Controllers\AdminController::class, 'updateCredential'])->name('credentials.update');
    Route::post('/credentials/test', [App\Http\Controllers\AdminController::class, 'testCredential'])->name('credentials.test');

    // Audit Trails
    Route::get('/audits', [App\Http\Controllers\AdminController::class, 'audits'])->name('audits');

    // Export routes
    Route::get('/export/users', [App\Http\Controllers\AdminController::class, 'exportUsers'])->name('export.users');
    Route::get('/export/calls', [App\Http\Controllers\AdminController::class, 'exportCalls'])->name('export.calls');

    // Platform Intelligence
    Route::get('/telemetry/health', [App\Http\Controllers\Admin\AdminTelemetryController::class, 'health'])->name('telemetry.health');
    Route::get('/telemetry/budget', [App\Http\Controllers\Admin\AdminTelemetryController::class, 'budget'])->name('telemetry.budget');
    Route::get('/search', [App\Http\Controllers\Admin\AdminSearchController::class, 'search'])->name('search.api');
});

require __DIR__ . '/auth.php';
