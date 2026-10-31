<x-app-layout x-data="{ 
    showInsights: false, 
    activeSummary: '',
    systemStatus: 'online',
    analyticDays: 7,
    analyticData: { labels: [], volume: [] },
    analyticLoading: false,
    openInsights(summary) {
        this.activeSummary = summary || 'Summary not available for this older interaction.';
        this.showInsights = true;
    },
    checkHealth() {
        fetch('/api/v1/system/health')
            .then(res => res.json())
            .then(data => {
                this.systemStatus = data.status;
            })
            .catch(() => { this.systemStatus = 'offline'; });
    },
    fetchAnalytics() {
        this.analyticLoading = true;
        fetch(`/api/analytics/volume?days=${this.analyticDays}`)
            .then(res => res.json())
            .then(data => {
                this.analyticData = data;
                this.analyticLoading = false;
            })
            .catch(() => { this.analyticLoading = false; });
    },
    get maxCallCount() {
        return Math.max(...this.analyticData.volume, 10);
    },
    init() {
        this.checkHealth();
        setInterval(() => this.checkHealth(), 30000);
        this.fetchAnalytics();
    }
}">
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900">Command Center</h1>
                <p class="text-slate-500 mt-1">Real-time supervision of your AI workforce.</p>
            </div>

            @if($agent)
                <div class="flex items-center gap-3">
                    <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs font-semibold uppercase tracking-wide transition-colors duration-500"
                        :class="systemStatus === 'online' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-red-50 text-red-700 border-red-100'">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full rounded-full opacity-75"
                                :class="systemStatus === 'online' ? 'animate-ping bg-emerald-400' : 'bg-red-400'"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2"
                                :class="systemStatus === 'online' ? 'bg-emerald-500' : 'bg-red-500'"></span>
                        </span>
                        <span x-text="systemStatus === 'online' ? 'System Operational' : 'Engine Offline'"></span>
                    </div>
                </div>
            @endif
        </div>
    </x-slot>

    <!-- Global Stats Row -->
    @if($agent)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Calls -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center gap-4 mb-3">
                    <div
                        class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-600 shadow-inner">
                        <i data-lucide="phone"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Total
                            Calls</div>
                        <div class="text-2xl font-bold text-slate-900 leading-none">{{ $stats['total_calls'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="text-xs text-emerald-600 font-medium flex items-center">
                    <i data-lucide="trending-up" class="w-3.5 h-3.5 mr-1.5"></i> Active Volume
                </div>
            </div>

            <!-- Minutes Used -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all hover:shadow-md">
                <div class="flex items-center gap-4 mb-3">
                    <div
                        class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-600 shadow-inner">
                        <i data-lucide="clock"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Total
                            Airtime</div>
                        <div class="text-2xl font-bold text-slate-900 leading-none">{{ $stats['minutes_used'] ?? 0 }}<span
                                class="text-sm font-medium text-slate-400 ml-0.5">m</span></div>
                    </div>
                </div>
                <div class="text-xs text-slate-400 font-medium">Monthly utilization</div>
            </div>

            <!-- Successful Outcomes -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all hover:shadow-md group">
                <div class="flex items-center gap-4 mb-3">
                    <div
                        class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 shadow-inner border border-emerald-100 group-hover:bg-emerald-100 transition-colors">
                        <i data-lucide="check-circle"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">
                            Successful Outcomes</div>
                        <div class="text-2xl font-bold text-slate-900 leading-none">{{ $stats['success_count'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="text-xs text-slate-500 font-medium flex items-center gap-1">
                    <span class="text-emerald-600 cursor-help"
                        title="Percentage of calls successfully handled">{{ $stats['success_rate'] }}% Success Rate</span>
                    <span class="text-slate-300">•</span>
                    <span>Leads & resolutions</span>
                </div>
            </div>

            <!-- After-Hours Support -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all hover:shadow-md group">
                <div class="flex items-center gap-4 mb-3">
                    <div
                        class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 shadow-inner border border-indigo-100 group-hover:bg-indigo-100 transition-colors">
                        <i data-lucide="moon"></i>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">
                            After-Hours Support</div>
                        <div class="text-2xl font-bold text-slate-900 leading-none">{{ $stats['after_hours'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="text-xs text-slate-500 font-medium">24/7 Coverage value</div>
            </div>
        </div>
    @endif

    @if($agent)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Left Column: Agent Control & Knowledge -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Agent Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative group">
                    <div
                        class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">
                    </div>

                    <div class="p-6">
                        <div class="flex items-start justify-between mb-6">
                            <div
                                class="h-14 w-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-inner border border-indigo-100">
                                <i data-lucide="bot" class="w-8 h-8"></i>
                            </div>
                            <span
                                class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 border border-emerald-200">
                                {{ ucfirst($agent->status) }}
                            </span>
                        </div>

                        <h2 class="text-xl font-bold text-slate-900 mb-1">{{ $agent->name }}</h2>
                        <p class="text-sm text-slate-500 mb-6">{{ $agent->phone_number ?? 'No Number Assigned' }}</p>

                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <a href="{{ route('agent.edit') }}"
                                class="flex items-center justify-center px-4 py-2.5 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all">
                                <i data-lucide="settings-2" class="w-4 h-4 mr-2"></i> Configure
                            </a>
                            <a href="{{ route('agent.test') }}"
                                class="flex items-center justify-center px-4 py-2.5 border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition-all">
                                <i data-lucide="message-square" class="w-4 h-4 mr-2 text-indigo-500"></i> Simulator
                            </a>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 border-t border-slate-100">
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Quick Config
                            Snapshot</div>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Business</span>
                                <span
                                    class="font-medium text-slate-900">{{ $agent->configuration['business_name'] ?? '---' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Hours</span>
                                <span class="font-medium text-slate-900">
                                    {{ is_array($agent->configuration['hours']) ? ($agent->configuration['hours']['schedule'] ?? 'Custom') : ($agent->configuration['hours'] ?? 'Unset') }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-200 text-center">
                            <a href="{{ route('onboarding.index') }}"
                                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium hover:underline">
                                Restart via Conversation
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System Health (Quantitative & Honest) -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 transition-all duration-300">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">System Health</h3>
                        <div class="flex items-center gap-1.5">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $readiness['score'] == 100 ? 'bg-emerald-400' : 'bg-amber-400' }} opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 {{ $readiness['score'] == 100 ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            </span>
                            <span class="text-[10px] font-black uppercase tracking-tighter {{ $readiness['score'] == 100 ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $readiness['score'] == 100 ? 'Optimized' : 'Setup Required' }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 rounded-xl border {{ $readiness['has_knowledge'] ? 'bg-emerald-50/30 border-emerald-100/50' : 'bg-slate-50 border-slate-100' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $readiness['has_knowledge'] ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-200 text-slate-400' }}">
                                    <i data-lucide="book-open" class="w-4 h-4"></i>
                                </div>
                                <span class="text-sm font-semibold {{ $readiness['has_knowledge'] ? 'text-slate-900' : 'text-slate-400' }}">Knowledge Base</span>
                            </div>
                            @if($readiness['has_knowledge'])
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500"></i>
                            @else
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Pending</span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-xl border {{ $readiness['has_voice'] ? 'bg-emerald-50/30 border-emerald-100/50' : 'bg-slate-50 border-slate-100' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $readiness['has_voice'] ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-200 text-slate-400' }}">
                                    <i data-lucide="mic-2" class="w-4 h-4"></i>
                                </div>
                                <span class="text-sm font-semibold {{ $readiness['has_voice'] ? 'text-slate-900' : 'text-slate-400' }}">Voice Config</span>
                            </div>
                            @if($readiness['has_voice'])
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500"></i>
                            @else
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Pending</span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-xl border {{ $readiness['has_calendar'] ? 'bg-emerald-50/30 border-emerald-100/50' : 'bg-slate-50 border-slate-100' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $readiness['has_calendar'] ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-200 text-slate-400' }}">
                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                </div>
                                <span class="text-sm font-semibold {{ $readiness['has_calendar'] ? 'text-slate-900' : 'text-slate-400' }}">Calendar Sync</span>
                            </div>
                            @if($readiness['has_calendar'])
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500"></i>
                            @else
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Pending</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Analytics & Feed -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Chart Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-slate-900 line-clamp-1">Call Volume (<span x-text="analyticDays"></span>
                            Days)</h3>
                        <select x-model="analyticDays" @change="fetchAnalytics()"
                            class="text-xs border-slate-200 rounded-lg text-slate-600 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50/50">
                            <option value="7">Last 7 Days</option>
                            <option value="30">Last 30 Days</option>
                        </select>
                    </div>

                    <!-- CSS Bar Chart (Dynamic) -->
                    <div class="h-48 flex items-end justify-between px-1 transition-all duration-300 transform" :class="[
                                analyticData.volume.length == 7 ? 'gap-3' : 'gap-1',
                                analyticLoading ? 'opacity-30 scale-[0.98] blur-sm px-4' : 'opacity-100 scale-100 blur-0 px-1'
                             ]">
                        <template x-for="(val, index) in analyticData.volume" :key="index">
                            <div
                                class="flex-1 bg-slate-50/50 rounded-t-lg relative group h-full flex flex-col justify-end transition-all duration-300">
                                <div class="bg-indigo-500 rounded-t-lg w-full transition-all duration-700 group-hover:bg-indigo-600 relative ease-out"
                                    :style="'height: ' + (val / maxCallCount * 100) + '%'">

                                    <!-- Tooltip -->
                                    <div
                                        class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-slate-900 text-white text-[10px] py-1.5 px-2.5 rounded-lg opacity-0 group-hover:opacity-100 transition-all pointer-events-none whitespace-nowrap z-10 shadow-xl border border-slate-800">
                                        <span x-text="val"></span> Calls
                                    </div>
                                </div>
                                <div class="text-[9px] font-bold text-slate-400 text-center mt-2 truncate w-full transition-opacity duration-300"
                                    x-show="analyticData.volume.length == 7 || index % 5 === 0"
                                    x-text="analyticData.labels[index]"></div>
                                <div class="h-4" x-show="analyticData.volume.length > 7 && index % 5 !== 0"></div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Activity Feed -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="font-bold text-slate-900">Recent Activity</h3>
                        <div class="flex items-center gap-4">
                            <a href="{{ route('calls.index') }}"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1.5 transition-colors">
                                <i data-lucide="archive" class="w-3.5 h-3.5"></i>
                                View All Archive
                            </a>
                            <span class="text-slate-300">|</span>
                            <a href="{{ route('export.calls') }}"
                                class="text-xs font-medium text-slate-500 hover:text-indigo-600 flex items-center gap-1.5 transition-colors">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                Export CSV
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead
                                class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold text-[11px] border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3">Outcome</th>
                                    <th class="px-6 py-3">Caller</th>
                                    <th class="px-6 py-3">Duration</th>
                                    <th class="px-6 py-3">Time</th>
                                    <th class="px-6 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($recentCalls as $call)
                                    <tr class="hover:bg-slate-50/80 transition-colors group">
                                        <td class="px-6 py-4">
                                            @if($call['status'] == 'success')
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                    <span class="font-medium text-slate-900">{{ $call['outcome'] }}</span>
                                                </div>
                                            @elseif($call['status'] == 'missed')
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                                    <span class="font-medium text-slate-900">{{ $call['outcome'] }}</span>
                                                </div>
                                            @else
                                                <div class="flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                    <span class="font-medium text-slate-900">{{ $call['outcome'] }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-slate-900">{{ $call['caller_name'] ?? 'Unknown' }}
                                            </div>
                                            <div class="text-slate-500 text-xs font-mono">{{ $call['caller'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500">{{ $call['duration'] }}</td>
                                        <td class="px-6 py-4 text-slate-500">{{ $call['time'] }}</td>
                                        <td class="px-6 py-4 text-right flex justify-end gap-2">
                                            @if($call['summary'] || $call['has_transcript'])
                                                <button @click='openInsights(@json($call["summary"]))'
                                                    class="text-slate-400 hover:text-indigo-600 transition-colors p-1 rounded-md hover:bg-indigo-50 border border-transparent hover:border-indigo-100"
                                                    title="View Call Insights">
                                                    <i data-lucide="message-square" class="w-5 h-5"></i>
                                                </button>
                                            @endif

                                            @if($call['recording_url'])
                                                <button
                                                    class="text-slate-400 hover:text-indigo-600 transition-colors p-1 rounded-md hover:bg-indigo-50 border border-transparent hover:border-indigo-100">
                                                    <i data-lucide="play-circle" class="w-5 h-5"></i>
                                                </button>
                                            @else
                                                <span class="text-slate-300 self-center">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                            No calls recorded yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($recentCalls->hasPages())
                        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                            {{ $recentCalls->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <!-- Empty State (No Agent) -->
        <div class="max-w-2xl mx-auto py-12">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden text-center p-12">
                <div
                    class="w-20 h-20 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto text-indigo-500 mb-6 shadow-sm">
                    <i data-lucide="bot" class="w-10 h-10"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Initialize Your AI Agent</h2>
                <p class="text-slate-500 mb-8 max-w-md mx-auto">You're one step away from automated professionalism.
                    Configure your agent's knowledge base to get started.</p>
                <a href="{{ route('onboarding.index') }}"
                    class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-200 transition-all transform active:scale-95">
                    Start Configuration <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                </a>
            </div>
        </div>
    @endif
    <!-- Call Insights Modal -->
    <div x-show="showInsights" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak
        @keydown.escape.window="showInsights = false">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showInsights" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity bg-slate-900/40" @click="showInsights = false">
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="showInsights" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block w-full max-w-xl overflow-hidden text-left align-bottom transition-all transform-gpu bg-white rounded-2xl shadow-2xl sm:my-8 sm:align-middle antialiased">

                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg">
                            <i data-lucide="sparkles" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 leading-tight">AI Call Insights</h3>
                            <p class="text-xs text-slate-500 font-medium">Auto-generated summary</p>
                        </div>
                    </div>
                    <button @click="showInsights = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors p-1">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-8 bg-white">
                    <div class="prose prose-sm prose-slate max-w-none">
                        <div class="p-5 bg-indigo-50/50 rounded-xl border border-indigo-100 text-slate-700 leading-relaxed text-[15px]"
                            x-text="activeSummary"></div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button @click="showInsights = false"
                        class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-all text-sm shadow-sm">
                        Close Insights
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>