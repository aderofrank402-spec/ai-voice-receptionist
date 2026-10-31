@extends('admin.layout')

@section('title', 'Credentials Vault')

@section('content')
<div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Credentials Vault</h1>
        <p class="text-slate-500 mt-1 font-medium italic">Secure enclave for administrative API keys and provider secrets.</p>
    </div>
    <div class="flex items-center gap-3">
        <div class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black rounded-md border border-emerald-100 tracking-widest uppercase flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)]"></span>
            AES-256 Encrypted
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-10">
    @foreach($credentials as $provider => $providerKeys)
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm hover:shadow-md transition-all flex flex-col group">
        <div class="p-8 flex-1">
            <div class="flex items-center justify-between mb-8 pb-6 border-b border-slate-50">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center text-white shadow-lg shadow-slate-200 group-hover:scale-110 transition-transform">
                        @if($provider === 'openai')
                            <i data-lucide="bot" class="w-6 h-6"></i>
                        @elseif($provider === 'groq')
                            <i data-lucide="zap" class="w-6 h-6 text-amber-300"></i>
                        @elseif($provider === 'elevenlabs')
                            <i data-lucide="mic" class="w-6 h-6"></i>
                        @elseif($provider === 'deepgram')
                            <i data-lucide="headphones" class="w-6 h-6"></i>
                        @else
                            <i data-lucide="key" class="w-6 h-6"></i>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight uppercase">{{ $provider }}</h3>
                        <div class="flex items-center gap-2 mt-0.5">
                            @php
                                $allValid = $providerKeys->every('is_valid');
                                $anyTested = $providerKeys->some('last_tested_at');
                            @endphp
                            @if(!$anyTested)
                                <span class="text-[9px] font-black text-amber-500 uppercase tracking-widest italic">Untested Domain</span>
                            @elseif($allValid)
                                <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest italic">Active Integration</span>
                            @else
                                <span class="text-[9px] font-black text-rose-600 uppercase tracking-widest italic">Node Failure</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                @foreach($providerKeys as $key)
                <div class="bg-slate-50/50 rounded-2xl p-5 border border-slate-100 shadow-sm">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ str_replace('_', ' ', str_replace($provider . '_', '', $key->name)) }}</span>
                        @if($key->last_tested_at)
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full {{ $key->is_valid ? 'bg-emerald-500 shadow-[0_0_5px_rgba(16,185,129,0.3)]' : 'bg-rose-500 shadow-[0_0_5px_rgba(244,63,94,0.3)]' }}"></span>
                                <span class="text-[8px] font-black uppercase tracking-tight {{ $key->is_valid ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $key->is_valid ? 'Verified' : 'Failed' }}
                                </span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex items-center bg-white rounded-xl px-4 py-3 border border-slate-200 mb-4 group/key shadow-inner transition-all hover:border-slate-300">
                        <span class="text-slate-400 font-mono text-xs flex-1 truncate font-bold tracking-widest opacity-40">
                            @if($key->value === 'placeholder')
                                <span class="text-amber-500/50 lowercase italic">Not Configured</span>
                            @else
                                ••••••••••••••••••••••••
                            @endif
                        </span>
                        <button onclick="openModal('{{ $key->id }}', '{{ $key->name }}')" class="ml-2 text-slate-400 hover:text-indigo-600 transition-colors">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form action="{{ route('admin.credentials.test') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $key->id }}">
                        <button type="submit" class="w-full py-2.5 bg-white border border-slate-200 text-slate-600 hover:text-slate-900 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-sm flex items-center justify-center gap-2 hover:bg-slate-50">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> 
                            Verify Protocol
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Lite Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[60] hidden flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 w-full max-w-lg rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="p-10 border-b border-slate-100 bg-slate-50/50 flex flex-col gap-2">
            <h3 class="text-2xl font-black text-slate-900 tracking-tight" id="modalTitle">Update Secret</h3>
            <p class="text-xs text-slate-500 font-medium italic" id="modalSubtitle">Administrative key rotation and node configuration.</p>
        </div>
        <form action="{{ route('admin.credentials.update') }}" method="POST">
            @csrf
            <input type="hidden" name="id" id="modalId">
            <div class="p-10">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-1">Key Value (Encrypted Transmission)</label>
                <textarea name="value" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-6 py-4 text-slate-900 font-mono text-xs focus:bg-white focus:border-slate-900 transition-all outline-none shadow-inner" placeholder="Paste credential payload..."></textarea>
                
                <div class="mt-8 p-6 bg-slate-50 border border-slate-100 rounded-2xl flex gap-4">
                    <div class="text-slate-400 mt-0.5"><i data-lucide="shield-alert" class="w-5 h-5"></i></div>
                    <p class="text-[10px] text-slate-400 leading-relaxed font-bold italic tracking-tight">
                        Updating this key will affect all active integrated units using this provider. The value will be secured using AES-256 before disk persistence.
                    </p>
                </div>
            </div>
            <div class="px-10 py-8 bg-slate-50 border-t border-slate-100 flex justify-end gap-4">
                <button type="button" onclick="closeModal()" class="px-6 py-3 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-900 transition-all">Cancel</button>
                <button type="submit" class="px-8 py-3 bg-slate-900 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-xl shadow-slate-200 transition-all hover:bg-slate-800">Commit Encrypted</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openModal(id, name) {
        document.getElementById('modalId').value = id;
        document.getElementById('modalTitle').innerText = 'Update ' + name.replace(/_/g, ' ').toUpperCase();
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    // Close on escape
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });
</script>
@endpush
@endsection
