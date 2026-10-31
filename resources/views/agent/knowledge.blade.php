<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 py-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-4xl font-[900] text-slate-900 tracking-tight leading-none">Intelligence Hub</h2>
                    <div class="px-2 py-1 bg-indigo-600 text-[10px] text-white font-black rounded-lg shadow-lg shadow-indigo-100 uppercase tracking-widest border border-indigo-500/20">
                        v2.4 Live
                    </div>
                </div>
                <p class="text-sm text-slate-500 font-medium tracking-tight">Systematic oversight of proprietary business intelligence and training registries.</p>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- Wisdom Pod -->
                <div class="flex items-center gap-5 px-6 py-4 bg-white/80 backdrop-blur-md border border-slate-200 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all hover:shadow-xl hover:shadow-slate-200/50 group">
                    <div class="w-10 h-10 rounded-2xl bg-slate-900 flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform">
                        <i data-lucide="brain-circuit" class="w-5 h-5 animate-pulse"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Total Wisdom</span>
                        <div class="flex items-center gap-2">
                            <span class="text-2xl font-[900] text-slate-900 leading-none">{{ $sources->count() }}</span>
                            <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></div>
                        </div>
                    </div>
                </div>

                <!-- Training Pod -->
                <div class="flex items-center gap-5 px-6 py-4 bg-white/80 backdrop-blur-md border border-slate-200 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all hover:shadow-xl hover:shadow-slate-200/50 group">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 border border-amber-100 shadow-sm group-hover:scale-110 transition-transform">
                        <i data-lucide="refresh-ccw" class="w-5 h-5 {{ $sources->where('status', 'indexing')->count() > 0 ? 'animate-spin' : '' }}"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">In Training</span>
                        <div class="flex items-center gap-2">
                            <span class="text-2xl font-[900] text-slate-900 leading-none">{{ $sources->where('status', 'indexing')->count() }}</span>
                            <div class="w-2 h-2 rounded-full bg-amber-400 {{ $sources->where('status', 'indexing')->count() > 0 ? 'animate-pulse' : '' }}"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4" x-data="{ tab: 'all' }">
        
        <!-- 1. UTILITY & NAVIGATION BAR -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 py-4 px-2">
            <div class="flex items-center gap-2 p-1.5 bg-slate-100/50 backdrop-blur-sm rounded-2xl border border-slate-200/50">
                <button @click="tab = 'all'" :class="tab === 'all' ? 'bg-slate-900 text-white shadow-xl' : 'text-slate-500 hover:bg-white'" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap">
                    All Wisdom
                </button>
                <div class="w-px h-4 bg-slate-200 mx-1"></div>
                <button @click="tab = 'core'" :class="tab === 'core' ? 'bg-slate-900 text-white shadow-xl' : 'text-slate-500 hover:bg-white'" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap">
                    Core Intelligence
                </button>
                <button @click="tab = 'file'" :class="tab === 'file' ? 'bg-slate-900 text-white shadow-xl' : 'text-slate-500 hover:bg-white'" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap">
                    Registry Files
                </button>
                <button @click="tab = 'link'" :class="tab === 'link' ? 'bg-slate-900 text-white shadow-xl' : 'text-slate-500 hover:bg-white'" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap">
                    Web Sync
                </button>
                <button @click="tab = 'snippet'" :class="tab === 'snippet' ? 'bg-slate-900 text-white shadow-xl' : 'text-slate-500 hover:bg-white'" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap">
                    Expert Facts
                </button>
            </div>

            <button @click="$dispatch('open-add-knowledge')" class="flex items-center gap-3 px-8 py-4 bg-slate-900 text-white rounded-[1.5rem] font-black text-xs uppercase tracking-widest shadow-2xl hover:bg-black hover:scale-[1.02] active:scale-95 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Expand Registry
            </button>
        </div>

        <!-- 2. INTELLIGENCE GRID -->
        <div class="relative min-h-[500px]">
            <!-- Precise Empty State -->
            <div x-show="tab !== 'all' && !document.querySelectorAll('[data-type=\'' + tab + '\']').length" 
                 x-cloak
                 class="absolute inset-0 flex flex-col items-center justify-center text-center p-12 bg-white/50 backdrop-blur-sm rounded-[3rem] border border-slate-200 transition-all">
                <div class="w-20 h-20 bg-white rounded-3xl shadow-xl flex items-center justify-center mb-6 border border-slate-100">
                    <i data-lucide="database" class="w-10 h-10 text-slate-300"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-900">Intelligence Stream Empty</h3>
                <p class="text-slate-500 mt-2 max-w-xs mx-auto text-sm font-medium leading-relaxed">No data has been synchronized for the <span x-text="tab" class="text-indigo-600 font-bold uppercase"></span> category yet.</p>
                <button @click="$dispatch('open-add-knowledge')" class="mt-8 px-10 py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl hover:bg-black transition-all">
                    Initialize Sync
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($sources as $source)
                    <div x-show="tab === 'all' || tab === '{{ $source->type }}'" 
                         data-type="{{ $source->type }}"
                         @click="$dispatch('view-knowledge', {{ json_encode($source) }})"
                         class="bg-gradient-to-b from-white to-slate-50/50 rounded-[2.5rem] border border-slate-200/60 p-8 hover:border-indigo-500/50 hover:shadow-[0_22px_70px_4px_rgba(0,0,0,0.06)] transition-all duration-500 group cursor-pointer flex flex-col h-full overflow-hidden relative">

                        <div class="flex items-center justify-between mb-8">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white group-hover:shadow-xl group-hover:shadow-indigo-100 transition-all duration-300">
                                @if($source->type === 'core') <i data-lucide="shield-check" class="w-6 h-6"></i>
                                @elseif($source->type === 'file') <i data-lucide="file-text" class="w-6 h-6"></i>
                                @elseif($source->type === 'link') <i data-lucide="globe" class="w-6 h-6"></i>
                                @else <i data-lucide="quote" class="w-6 h-6"></i>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-100 rounded-full shadow-sm">
                                @if($source->status === 'ready')
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Verified</span>
                                @else
                                    <div class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></div>
                                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Processing</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex-1">
                            <h3 class="text-xl font-black text-slate-900 tracking-tight leading-snug mb-3 group-hover:text-indigo-600 transition-colors truncate" title="{{ $source->title }}">
                                {{ $source->title }}
                            </h3>

                            <div class="text-slate-500 text-[12px] leading-relaxed font-medium">
                                @if($source->type === 'core')
                                    @php 
                                                                        $rawContent = $source->content;
                                        $core = is_array($rawContent) ? $rawContent : json_decode($rawContent, true); 
                                    @endphp
                                    @if($core)
                                        <div class="space-y-1.5 mt-2">
                                            <p class="flex items-center gap-2 text-slate-700 font-bold"><i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-300"></i> {{ $core['business_name'] ?? 'Not Set' }}</p>
                                            <p class="flex items-center gap-2"><i data-lucide="cog" class="w-3.5 h-3.5 text-slate-300"></i> {{ count($core['services'] ?? []) }} Operational Services</p>
                                        </div>
                                    @endif
                                @elseif($source->type === 'link')
                                    <p class="text-indigo-500 font-bold truncate italic flex items-center gap-2"><i data-lucide="link" class="w-3 h-3"></i> {{ $source->metadata['url'] ?? '' }}</p>
                                @else
                                    <p class="line-clamp-2 opacity-80">{{ Str::limit($source->content, 120) ?: 'Intelligence registry entry indexed.' }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-10 pt-6 border-t border-slate-100 flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Registry Code</span>
                                <span class="text-[10px] font-mono font-black text-slate-900 tracking-tight pb-1">SOURCEID-{{ substr($source->id, 0, 8) }}</span>
                            </div>

                            <div class="flex items-center gap-3">
                                @if($source->type !== 'core')
                                    <form action="{{ route('agent.knowledge.destroy', $source) }}" method="POST" onsubmit="return confirm('Erase from intelligence registry?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" @click.stop class="p-2 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                                <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-indigo-50 group-hover:text-indigo-500 transition-all">
                                    <i data-lucide="chevron-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Full Hub Empty State -->
                    <div x-show="tab === 'all'" class="col-span-full py-32 text-center bg-white/50 backdrop-blur-sm rounded-[4rem] border border-slate-200">
                        <div class="w-24 h-24 bg-white rounded-[2.5rem] shadow-2xl flex items-center justify-center mx-auto mb-8 border border-slate-100">
                            <i data-lucide="brain" class="w-12 h-12 text-slate-200"></i>
                        </div>
                        <h3 class="text-3xl font-[900] text-slate-900 tracking-tight">System Brain Uninitialized</h3>
                        <p class="text-slate-500 mt-3 max-w-sm mx-auto text-base font-medium leading-relaxed">No proprietary knowledge has been indexed. Please initiate the intelligence expander to teach your AI.</p>
                        <button @click="$dispatch('open-add-knowledge')" class="mt-10 px-12 py-5 bg-slate-900 text-white rounded-[2rem] font-black text-sm uppercase tracking-widest shadow-2xl hover:bg-black hover:scale-105 transition-all">
                            Initialize Knowledge Base
                        </button>
                    </div>
                @endforelse
            </div>
        </div>




    </div>
</x-app-layout>