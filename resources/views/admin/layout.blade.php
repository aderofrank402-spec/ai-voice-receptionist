<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Control') - Mission Control</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        
        /* Obsidian Core: High-Performance, High-Contrast Master UI */
        body {
            background-color: #020617; /* Slate 950 - Deep Space */
            color: #f1f5f9; /* Slate 100 - High Visibility */
            overflow: hidden;
            font-family: 'Outfit', 'Inter', system-ui, sans-serif;
            letter-spacing: -0.01em;
        }

        /* Glass Sidebar: Crystalline Depth */
        .glass-sidebar {
            background: #0f172a / 0.8; /* Slate 900 with transparency */
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Nav Link: Obsidian Command Style */
        .nav-link-active {
            background: rgba(99, 102, 241, 0.1) !important;
            border-left: 4px solid #6366f1; /* Indigo 500 */
            color: #ffffff !important;
            box-shadow: inset 10px 0 20px -10px rgba(99, 102, 241, 0.3);
        }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 20px; }
    </style>
</head>

<body class="antialiased text-slate-300 font-sans selection:bg-indigo-500/30 overflow-hidden" 
    x-data="{ 
        sidebarOpen: false,
        cmdPaletteOpen: false,
        searchQuery: '',
        searchResults: [],
        telemetry: { telephony: '...', ai: '...', database: '...', latency: '...' },
        budget: { breach: false, mtd: 0, limit: 100, forecast: 0 },
        updateHealth() {
            fetch('{{ route('admin.telemetry.health') }}')
                .then(res => res.json())
                .then(data => {
                    this.telemetry.telephony = data.nodes.telephony;
                    this.telemetry.ai = data.nodes.voice_engine;
                    this.telemetry.database = data.nodes.database;
                    this.telemetry.latency = data.telemetry.latency || '14ms';
                })
                .catch(() => {
                    this.telemetry.telephony = 'offline';
                    this.telemetry.ai = 'offline';
                    this.telemetry.database = 'offline';
                });
        },
        updateBudget() {
            fetch('{{ route('admin.telemetry.budget') }}')
                .then(res => res.json())
                .then(data => {
                    this.budget = data;
                });
        },
        search() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }
            fetch(`{{ route('admin.search.api') }}?q=${encodeURIComponent(this.searchQuery)}`)
                .then(res => res.json())
                .then(data => {
                    this.searchResults = data;
                });
        },
        init() {
            // High-Performance UX: Progressive enhancement of telemetry
            setTimeout(() => {
                this.updateHealth();
                this.updateBudget();
            }, 1500);
            
            // Background Ticker
            setInterval(() => {
                this.updateHealth();
                this.updateBudget();
            }, 60000); // Pulse every 60s
            
            this.$watch('searchQuery', () => {
                this.search();
            });
        }
    }" 
    @keydown.meta.k.window="cmdPaletteOpen = !cmdPaletteOpen"
    @keydown.ctrl.k.window="cmdPaletteOpen = !cmdPaletteOpen"
    @keydown.escape.window="cmdPaletteOpen = false"
>

    <div class="flex h-screen relative bg-[#020617]">
        <!-- Sidebar (Desktop) -->
        <aside class="hidden lg:flex flex-col w-72 glass-sidebar z-30 border-r border-white/5">
            <!-- Branding -->
            <div class="h-20 flex items-center px-8 border-b border-white/5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                        <i data-lucide="shield-half" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h2 class="text-white font-black tracking-tight text-lg leading-tight">CONTROL</h2>
                        <span class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em]">Super Admin Hub</span>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-8 space-y-1 overflow-y-auto">
                <p class="px-4 text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4">Operations</p>
                
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-4 py-2.5 text-xs font-bold rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="layout-grid" class="w-4 h-4 mr-4 transition-colors group-hover:text-indigo-400"></i>
                    Dashboard
                </a>

                <a href="{{ route('admin.users') }}" 
                   class="flex items-center px-4 py-2.5 text-xs font-bold rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.users*') ? 'nav-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="users" class="w-4 h-4 mr-4 transition-colors group-hover:text-indigo-400"></i>
                    User Registry
                </a>

                <a href="{{ route('admin.agents') }}" 
                   class="flex items-center px-4 py-2.5 text-xs font-bold rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.agents') ? 'nav-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="bot" class="w-4 h-4 mr-4 transition-colors group-hover:text-indigo-400"></i>
                    Agent Fleet
                </a>

                <a href="{{ route('admin.calls') }}" 
                   class="flex items-center px-4 py-2.5 text-xs font-bold rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.calls') ? 'nav-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="phone-call" class="w-4 h-4 mr-4 transition-colors group-hover:text-indigo-400"></i>
                    Call Archive
                </a>

                <p class="px-4 text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4 mt-8">Infrastructure</p>

                <a href="{{ route('admin.performance') }}" 
                   class="flex items-center px-4 py-2.5 text-xs font-bold rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.performance') ? 'nav-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="activity" class="w-4 h-4 mr-4 transition-colors group-hover:text-indigo-400"></i>
                    Performance
                </a>

                @can('view-economics')
                <a href="{{ route('admin.api.usage') }}" 
                   class="flex items-center px-4 py-2.5 text-xs font-bold rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.api.usage') ? 'nav-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 mr-4 transition-colors group-hover:text-indigo-400"></i>
                    API Economics
                </a>
                @endcan

                @can('view-system-logs')
                <a href="{{ route('admin.system') }}" 
                   class="flex items-center px-4 py-2.5 text-xs font-bold rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.system') ? 'nav-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="shield-check" class="w-4 h-4 mr-4 transition-colors group-hover:text-indigo-400"></i>
                    System Health
                </a>
                @endcan

                <p class="px-4 text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4 mt-8">Vault</p>
                
                @can('view-economics')
                <a href="{{ route('admin.credentials') }}" class="flex items-center px-4 py-2.5 text-xs font-bold rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.credentials') ? 'nav-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="key" class="w-4 h-4 mr-4 transition-colors group-hover:text-amber-400"></i>
                    Credentials
                </a>
                <a href="{{ route('admin.audits') }}" class="flex items-center px-4 py-2.5 text-xs font-bold rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.audits') ? 'nav-link-active' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="history" class="w-4 h-4 mr-4 transition-colors group-hover:text-indigo-400"></i>
                    Audit Trails
                </a>
                @endcan
            </nav>

            <!-- Deployment Ticker -->
            <div class="p-6 border-t border-white/5">
                <div class="px-4 py-3 bg-white/5 rounded-2xl border border-white/5 backdrop-blur-md">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[9px] font-black uppercase text-slate-500 tracking-widest">Platform Build</span>
                        <span class="text-[9px] font-black text-emerald-500">v4.2.0-STABLE</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-[10px] font-bold text-white">Live Monitoring Active</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
            <!-- Global Intelligence Header -->
            <header class="h-20 glass-sidebar flex items-center justify-between px-8 z-20 shrink-0">
                <div class="flex items-center gap-8">
                    <!-- Search Trigger -->
                    <button @click="cmdPaletteOpen = true; $nextTick(() => $refs.searchInput.focus())" class="flex items-center gap-3 px-5 py-2.5 bg-white/5 border border-white/5 rounded-2xl text-slate-500 hover:text-slate-300 transition-all group">
                        <i data-lucide="search" class="w-4 h-4 group-hover:text-indigo-400 transition-colors"></i>
                        <span class="text-xs font-bold">Search Platform...</span>
                        <span class="px-2 py-0.5 rounded-lg bg-white/10 text-[9px] font-black tracking-tighter">CMD+K</span>
                    </button>

                    <!-- Telemetry Tickers -->
                    <div class="hidden lg:flex items-center gap-8 border-l border-white/5 pl-8">
                        <div class="flex flex-col">
                            <span class="text-[8px] font-black text-slate-500 uppercase tracking-[0.2em]">Voice Engine</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                <div class="w-1.5 h-1.5 rounded-full" :class="telemetry.telephony === 'online' ? 'bg-emerald-500 shadow-[0_0_10px_#10b981]' : 'bg-amber-500 shadow-[0_0_10px_#f59e0b]'"></div>
                                <span class="text-[10px] font-black text-slate-100 uppercase tracking-tighter" x-text="telemetry.telephony"></span>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[8px] font-black text-slate-500 uppercase tracking-[0.2em]">AI Backbone</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                <div class="w-1.5 h-1.5 rounded-full" :class="telemetry.ai === 'online' ? 'bg-emerald-500 shadow-[0_0_10px_#10b981]' : 'bg-amber-500 shadow-[0_0_10px_#f59e0b]'"></div>
                                <span class="text-[10px] font-black text-slate-100 uppercase tracking-tighter" x-text="telemetry.ai"></span>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[8px] font-black text-slate-500 uppercase tracking-[0.2em]">Response</span>
                            <span class="text-[11px] font-black text-indigo-400 mt-0.5 tracking-tighter" x-text="telemetry.latency"></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-white/5 border border-white/10 hover:bg-white/10 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl transition-all flex items-center gap-3">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        Exit to Site
                    </a>
                </div>
            </header>

            <!-- Scrollable Content Workspace -->
            <div class="flex-1 overflow-y-auto p-8 custom-scrollbar relative">
                <div class="max-w-[1600px] mx-auto">
                    <!-- Fiscal Alert Breach (Async Reactive) -->
                    <div x-show="budget.breach" x-cloak
                         class="bg-rose-500/10 border border-rose-500/20 backdrop-blur-md px-8 py-6 rounded-[2.5rem] mb-10 flex items-center justify-between shadow-2xl shadow-rose-900/10">
                        <div class="flex items-center gap-8">
                            <div class="w-16 h-16 rounded-[1.5rem] bg-rose-500/20 flex items-center justify-center text-rose-500 shadow-inner">
                                <i data-lucide="shield-alert" class="w-8 h-8"></i>
                            </div>
                            <div>
                                <h4 class="text-slate-900 font-black text-xl tracking-tight">Critical Fiscal Drift Detected</h4>
                                <p class="text-slate-500 text-xs font-medium">Month-end projection ($<span x-text="budget.forecast"></span>) violates platform safety policies of $<span x-text="budget.limit"></span>.</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.api.usage') }}" class="px-8 py-4 bg-rose-500 hover:bg-rose-400 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl transition-all shadow-lg shadow-rose-500/30">Harden Limits</a>
                    </div>

                    @if(session('success'))
                        <div class="bg-emerald-500/10 border border-emerald-500/20 backdrop-blur-md text-emerald-400 px-6 py-4 rounded-2xl mb-8 flex items-center gap-3 shadow-xl">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                            <span class="text-sm font-bold tracking-tight">{{ session('success') }}</span>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <!-- COMMAND PALETTE (Teleport System) -->
    <template x-teleport="body">
        <div x-show="cmdPaletteOpen" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             class="fixed inset-0 z-[1000] p-4 pt-[15vh] sm:p-6 sm:pt-[20vh] bg-slate-950/80 backdrop-blur-xl" x-cloak>
            
            <div @click.away="cmdPaletteOpen = false" 
                 class="mx-auto max-w-2xl transform overflow-hidden rounded-[3rem] bg-[#0c111d] border border-white/10 shadow-[0_0_100px_rgba(0,0,0,0.5)] transition-all">
                
                <div class="relative border-b border-white/5">
                    <i data-lucide="search" class="pointer-events-none absolute left-10 top-10 h-6 w-6 text-slate-500"></i>
                    <input type="text" 
                           x-ref="searchInput"
                           x-model="searchQuery"
                           class="h-28 w-full border-0 bg-transparent pl-24 pr-10 text-white placeholder-slate-600 focus:ring-0 text-2xl font-black" placeholder="Command Intelligence..." autofocus>
                </div>

                <!-- Live Results -->
                <div class="px-10 py-8 min-h-[300px] overflow-y-auto max-h-[60vh] custom-scrollbar">
                    <template x-if="searchResults.length > 0">
                        <div class="space-y-2">
                            <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4 px-2">Intelligence Results</h3>
                            <template x-for="result in searchResults" :key="result.url">
                                <a :href="result.url" class="flex items-center justify-between p-5 rounded-[1.5rem] hover:bg-white/5 transition-all group">
                                    <div class="flex items-center gap-6">
                                        <div class="w-12 h-12 rounded-[1.2rem] bg-indigo-500/10 flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform">
                                            <i :data-lucide="result.icon" class="w-6 h-6"></i>
                                        </div>
                                        <div class="text-left">
                                            <p class="text-base font-black text-white" x-text="result.title"></p>
                                            <p class="text-xs text-slate-600" x-text="result.subtitle"></p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-black text-slate-700 uppercase group-hover:text-indigo-400 font-mono tracking-tighter" x-text="'GO:' + result.type.toUpperCase()"></span>
                                </a>
                            </template>
                        </div>
                    </template>

                    <template x-if="searchResults.length === 0 && searchQuery.length > 1">
                        <div class="py-20 text-center">
                            <i data-lucide="ghost" class="w-12 h-12 text-slate-700 mx-auto mb-4"></i>
                            <p class="text-sm font-bold text-slate-500 uppercase tracking-widest leading-loose">No matches found in any registry</p>
                        </div>
                    </template>

                    <template x-if="searchQuery.length < 2">
                        <div class="space-y-4">
                            <h3 class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4 px-2 text-center">Protocol Reference</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-6 rounded-3xl bg-white/[0.02] border border-white/5 text-center">
                                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">Users</p>
                                    <p class="text-xs text-slate-500">Search by name or email</p>
                                </div>
                                <div class="p-6 rounded-3xl bg-white/[0.02] border border-white/5 text-center">
                                    <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-2">Calls</p>
                                    <p class="text-xs text-slate-500">Search by full Call ID</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div class="bg-white/[0.02] px-10 py-6 border-t border-white/5 flex items-center justify-between">
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest">Esc to Close</p>
                    <div class="flex items-center gap-6">
                        <span class="text-[9px] font-black text-slate-700 uppercase tracking-widest">Control Kernel Alpha</span>
                    </div>
                </div>
            </div>
        </div>
    </template>

    @stack('scripts')
</body>

</html>