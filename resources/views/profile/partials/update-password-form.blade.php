@php /** @var \Illuminate\Support\ViewErrorBag $errors */ @endphp
<section>
    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <div class="space-y-6">
            <div class="max-w-md">
                <x-input-label for="update_password_current_password" :value="__('Current Password')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1" />
                <x-text-input id="update_password_current_password" name="current_password" type="password" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-slate-900" autocomplete="current-password" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <div>
                    <x-input-label for="update_password_password" :value="__('New Security Key')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1" />
                    <x-text-input id="update_password_password" name="password" type="password" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-slate-900" autocomplete="new-password" />
                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="update_password_password_confirmation" :value="__('Verify New Key')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1" />
                    <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-slate-900" autocomplete="new-password" />
                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4">
            <x-primary-button class="px-8 py-3 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-lg">
                {{ __('Update Security Credentials') }}
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-emerald-600 font-bold uppercase tracking-tight">{{ __('Credentials Updated.') }}</p>
            @endif
        </div>
    </form>
</section>