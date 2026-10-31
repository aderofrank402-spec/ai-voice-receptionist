@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
    /** @var \App\Models\User $user */
@endphp
<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="name" :value="__('Full Name')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1" />
                <x-text-input id="name" name="name" type="text" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-slate-900" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email Address')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1" />
                <x-text-input id="email" name="email" type="email" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-slate-900 uppercase tracking-tight" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                    <div class="mt-4 p-4 bg-amber-50 rounded-2xl border border-amber-100">
                        <p class="text-[10px] font-bold text-amber-900 leading-relaxed uppercase tracking-tight">
                            Identity Verification Required
                        </p>
                        <button form="send-verification" class="text-[10px] font-black text-amber-600 underline uppercase tracking-widest hover:text-amber-800">
                            Resend Link
                        </button>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-black text-[9px] text-emerald-600 uppercase tracking-widest">
                                Link Delivered
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4">
            <x-primary-button class="px-8 py-3 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-lg">
                {{ __('Update Identity') }}
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-emerald-600 font-bold uppercase tracking-tight">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>