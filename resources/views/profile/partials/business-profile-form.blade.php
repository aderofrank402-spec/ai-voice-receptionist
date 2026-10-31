@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
    /** @var \App\Models\BusinessProfile $businessProfile */
@endphp

<section>
    <form method="post" action="{{ route('profile.business.update') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="business_name" :value="__('Company Name')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1" />
                <x-text-input id="business_name" name="business_name" type="text" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-slate-900" :value="old('business_name', $businessProfile->business_name)" required />
                <x-input-error class="mt-2" :messages="$errors->get('business_name')" />
            </div>

            <div>
                <x-input-label for="business_type" :value="__('Industry / Category')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1" />
                <x-text-input id="business_type" name="business_type" type="text" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-slate-900" :value="old('business_type', $businessProfile->business_type)" placeholder="e.g. Legal, Retail, Healthcare" />
                <x-input-error class="mt-2" :messages="$errors->get('business_type')" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="location" :value="__('Physical HQ / Service Area')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1" />
                <x-text-input id="location" name="location" type="text" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-slate-900" :value="old('location', $businessProfile->location)" placeholder="e.g. Nairobi, Kenya" />
                <x-input-error class="mt-2" :messages="$errors->get('location')" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="hours_raw" :value="__('Default Operating Hours')" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1" />
                <x-text-input id="hours_raw" name="hours_raw" type="text" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-slate-900" :value="old('hours_raw', $businessProfile->hours_raw)" placeholder="e.g. Mon-Fri 8am to 5pm" />
                <p class="mt-2 text-[10px] text-slate-400 font-medium italic">This ensures your agent correctly handles appointment scheduling requests.</p>
                <x-input-error class="mt-2" :messages="$errors->get('hours_raw')" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4">
            <x-primary-button class="px-8 py-3 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all">
                {{ __('Sync Business Identity') }}
            </x-primary-button>

            @if (session('status') === 'business-profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-emerald-600 font-bold"
                >{{ __('Identity Synced.') }}</p>
            @endif
        </div>
    </form>
</section>
