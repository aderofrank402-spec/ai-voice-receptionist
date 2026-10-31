<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->redirectUrl(Config('services.google.redirect_login', url('auth/google/callback')))
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $driver = Socialite::driver('google');
            $driver->setHttpClient(\App\Support\HttpClientFactory::make());
            $googleUser = $driver->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => bcrypt(Str::random(16)), // Random password for security
                ]);
            } else {
                // Update existing user with Google ID if not present
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar(),
                    ]);
                }
            }

            Auth::login($user);

            return redirect()->intended('dashboard');
        } catch (\Exception $e) {
            // Log the error for debugging if needed, but keep user experience clean
            // Log::error('Google Login Error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['email' => 'Unable to login with Google. Please try again.']);
        }
    }
}
