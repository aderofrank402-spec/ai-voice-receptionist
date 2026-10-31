<section class="space-y-6">
    <!-- NEW TOKEN GENERATION -->
    <div class="p-6 bg-slate-50 border border-slate-100 rounded-3xl" x-data="{ generating: false }">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest">Generate New Access Token</h4>
            <button @click="generating = !generating" class="text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-800 transition-colors">
                <span x-show="!generating">+ Create New</span>
                <span x-show="generating">Cancel</span>
            </button>
        </div>

        <div x-show="generating" x-transition class="mt-4 pt-4 border-t border-slate-200">
            <form method="post" action="{{ route('profile.tokens.store') }}" class="flex flex-col md:flex-row gap-3">
                @csrf
                <div class="flex-1">
                    <x-text-input name="token_name" type="text" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-xs font-bold" placeholder="e.g. My Website CRM, Production Bot" required />
                </div>
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-md">
                    Generate Secret Key
                </button>
            </form>
            <p class="mt-3 text-[9px] text-slate-400 italic">API keys allow other software to securely connect to your Voice Agents.</p>
        </div>
    </div>

    <!-- SECRET KEY REVEAL (SHOW ONCE) -->
    @if (session('plain_text_token'))
        <div class="p-6 bg-indigo-900 rounded-3xl text-white shadow-xl animate-pulse">
            <div class="flex items-center gap-3 mb-4">
                <i data-lucide="shield-check" class="w-5 h-5 text-indigo-400"></i>
                <h4 class="text-xs font-black uppercase tracking-widest">Token Generated Successfully</h4>
            </div>
            <p class="text-[10px] text-indigo-300 mb-4 leading-relaxed font-medium">Please copy this secret key now. For your security, <span class="text-white font-bold underline">it will never be shown again.</span></p>
            
            <div class="flex items-stretch bg-white/10 border border-white/20 rounded-xl overflow-hidden" x-data="{ copied: false, token: '{{ session('plain_text_token') }}' }">
                <input type="text" readonly :value="token" class="flex-1 bg-transparent border-none text-xs font-mono font-bold px-4 py-3 focus:ring-0 text-indigo-100">
                <button @click="navigator.clipboard.writeText(token); copied = true; setTimeout(() => copied = false, 2000)" 
                    class="px-5 bg-white text-indigo-900 flex items-center justify-center transition-all hover:bg-slate-100 border-l border-white/20">
                    <span x-show="!copied" class="text-[10px] font-black uppercase tracking-widest">Copy</span>
                    <span x-show="copied" class="text-[10px] font-black uppercase tracking-widest flex items-center gap-1">
                        <i data-lucide="check" class="w-3 h-3"></i> Copied
                    </span>
                </button>
            </div>
        </div>
    @endif

    <!-- ACTIVE TOKENS LIST -->
    <div class="space-y-4">
        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Active Developer Tokens</h4>
        
        @forelse($tokens as $token)
            <div class="flex items-center justify-between p-5 bg-white border border-slate-100 rounded-2xl transition-all hover:shadow-sm hover:border-slate-200">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 border border-slate-100">
                        <i data-lucide="key" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-900 uppercase tracking-tight">{{ $token->name }}</p>
                        <p class="text-[9px] text-slate-400 font-medium">Last active: {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never used' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <span class="px-2 py-0.5 bg-slate-50 border border-slate-100 text-[8px] font-black text-slate-400 uppercase rounded-full">
                        Created {{ $token->created_at->format('M j, Y') }}
                    </span>
                    <form method="post" action="{{ route('profile.tokens.destroy', $token->id) }}" onsubmit="return confirm('Revoking this token will disconnect any apps using it. Proceed?')">
                        @csrf
                        @method('delete')
                        <button type="submit" class="text-slate-300 hover:text-red-500 transition-colors p-2">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center py-12 border-2 border-dashed border-slate-100 rounded-3xl">
                <i data-lucide="code-xml" class="w-8 h-8 text-slate-200 mx-auto mb-3"></i>
                <p class="text-xs font-bold text-slate-400">No active API keys found.</p>
                <p class="text-[9px] text-slate-300 mt-1">Generate a key to begin automating your agents.</p>
            </div>
        @endforelse
    </div>
</section>
