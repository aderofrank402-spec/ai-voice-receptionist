<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" class="text-zinc-700 font-medium" />
            <x-text-input id="name"
                class="block mt-1 w-full border-zinc-300 focus:border-zinc-500 focus:ring-zinc-500 rounded-lg"
                type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-600" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" class="text-zinc-700 font-medium" />
            <x-text-input id="email"
                class="block mt-1 w-full border-zinc-300 focus:border-zinc-500 focus:ring-zinc-500 rounded-lg"
                type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="text-zinc-700 font-medium" />

            <x-text-input id="password"
                class="block mt-1 w-full border-zinc-300 focus:border-zinc-500 focus:ring-zinc-500 rounded-lg"
                type="password" name="password" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')"
                class="text-zinc-700 font-medium" />

            <x-text-input id="password_confirmation"
                class="block mt-1 w-full border-zinc-300 focus:border-zinc-500 focus:ring-zinc-500 rounded-lg"
                type="password" name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-600" />
        </div>

        <div class="mt-6">
            <a href="{{ route('auth.google') }}"
                class="flex items-center justify-center gap-2 w-full px-4 py-2 border border-zinc-300 rounded-lg text-zinc-700 font-medium hover:bg-zinc-50 transition-colors">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path
                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                        fill="#4285F4" />
                    <path
                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                        fill="#34A853" />
                    <path
                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                        fill="#FBBC05" />
                    <path
                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                        fill="#EA4335" />
                </svg>
                Continue with Google
            </a>
        </div>

        <!-- Terms & Privacy Consent -->
        <div class="mt-6">
            <label for="terms" class="flex items-start gap-2 cursor-pointer">
                <input type="checkbox" id="terms" name="terms" required
                    class="mt-1 rounded border-zinc-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                <span class="text-sm text-zinc-600 leading-relaxed">
                    By registering, you agree to our
                    <a href="{{ route('terms') }}" target="_blank"
                        class="text-emerald-600 hover:text-emerald-500 font-medium underline">Terms of Service</a>
                    and
                    <a href="{{ route('privacy') }}" target="_blank"
                        class="text-emerald-600 hover:text-emerald-500 font-medium underline">Privacy Policy</a>.
                </span>
            </label>
            <x-input-error :messages="$errors->get('terms')" class="mt-2 text-red-600" />
        </div>

        <div class="flex items-center justify-between mt-8">
            <a class="text-sm text-zinc-500 hover:text-zinc-900 font-medium transition-colors"
                href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button
                class="ml-4 bg-zinc-900 hover:bg-zinc-800 active:bg-zinc-950 focus:ring-zinc-500 rounded-lg px-6 py-2.5">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>