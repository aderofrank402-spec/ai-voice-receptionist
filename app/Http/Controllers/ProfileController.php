<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        
        // Fetch API Tokens
        $tokens = $user->tokens()->latest()->get();
        
        // Fetch or create Business Profile
        $businessProfile = $user->businessProfile ?? new \App\Models\BusinessProfile(['user_id' => $user->id]);
        
        // Calculate Usage Analytics
        $usageStats = [
            'total_minutes' => round($user->usageLogs()->sum('duration_seconds') / 60, 1),
            'total_cost' => $user->usageLogs()->sum('cost_usd'),
            'call_count' => $user->usageLogs()->count(),
        ];

        return view('profile.edit', [
            'user' => $user,
            'tokens' => $tokens,
            'businessProfile' => $businessProfile,
            'usageStats' => $usageStats,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the business profile information.
     */
    public function updateBusiness(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'hours_raw' => ['nullable', 'string', 'max:255'],
        ]);

        $profile = $request->user()->businessProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return Redirect::route('profile.edit', ['tab' => 'business'])->with('status', 'business-profile-updated');
    }

    /**
     * Create a new API Token.
     */
    public function storeToken(Request $request): RedirectResponse
    {
        $request->validate([
            'token_name' => ['required', 'string', 'max:255'],
        ]);

        $token = $request->user()->createToken($request->token_name);

        // We store the plain text token sessions to show it ONCE
        return Redirect::route('profile.edit', ['tab' => 'api'])
            ->with('status', 'token-created')
            ->with('plain_text_token', $token->plainTextToken);
    }

    /**
     * Revoke an API Token.
     */
    public function destroyToken(Request $request, $tokenId): RedirectResponse
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return Redirect::route('profile.edit', ['tab' => 'api'])->with('status', 'token-revoked');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
