@extends('admin.layout')

@section('title', 'Platform Intelligence')

@section('content')
    <!-- Dashboard Obsidian Header -->
    <div class="mb-10">
        <h1 class="text-3xl font-black text-white tracking-tight">Platform Intelligence</h1>
        <p class="text-slate-400 mt-1 font-medium">Real-time supervision of global infrastructure and node health.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Users Card -->
        <div class="bg-slate-900/40 backdrop-blur-md border border-white/5 p-6 rounded-3xl shadow-2xl hover:bg-slate-900/60 transition-all group">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center shadow-inner">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-none">Entity Registry</span>
                    <div class="text-2xl font-black text-white mt-1">{{ number_format($stats['total_users']) }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-1.5 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-400 text-[10px] font-bold border border-emerald-500/10">+{{ $stats['new_users_this_week'] }}</span>
                <span class="text-[10px] font-bold text-slate-500 uppercase">New this week</span>
            </div>
        </div>

        <!-- Agents Card -->
        <div class="bg-slate-900/40 backdrop-blur-md border border-white/5 p-6 rounded-3xl shadow-2xl hover:bg-slate-900/60 transition-all group">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 rounded-xl bg-violet-500/10 text-violet-400 flex items-center justify-center shadow-inner">
                    <i data-lucide="bot" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-none">Agent Fleet</span>
                    <div class="text-2xl font-black text-white mt-1">{{ number_format($stats['total_agents']) }}</div>
                </div>
            </div>
            <div class="text-[10px] font-bold text-slate-500 uppercase pt-1 inline-flex items-center gap-1.5">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_#10b981]"></div>
                Deployed cognitive units
            </div>
        </div>

        <!-- Calls Card -->
        <div class="bg-slate-900/40 backdrop-blur-md border border-white/5 p-6 rounded-3xl shadow-2xl hover:bg-slate-900/60 transition-all group">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center shadow-inner">
                    <i data-lucide="phone-forwarded" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-none">Total Throughput</span>
                    <div class="text-2xl font-black text-white mt-1">{{ number_format($stats['total_calls']) }}</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-1.5 py-0.5 rounded-lg bg-sky-500/10 text-sky-400 text-[10px] font-bold border border-sky-500/10">{{ $stats['calls_today'] }} Today</span>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">Active operational flow</span>
            </div>
        </div>

        <!-- Health Card -->
        <div class="bg-slate-900/40 backdrop-blur-md border border-white/5 p-6 rounded-3xl shadow-2xl hover:bg-slate-900/60 transition-all group">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center shadow-inner">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-none">System Integrity</span>
                    <div class="text-2xl font-black text-emerald-400 mt-1 uppercase italic tracking-tighter shadow-emerald-500/20">Nominal</div>
                </div>
            </div>
            <div class="text-[10px] font-bold text-slate-500 uppercase pt-1 inline-flex items-center gap-1.5">
                <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_#10b981]"></div>
                Cluster verification passed
            </div>
        </div>
    </div>

    <!-- Analytics Section (Obsidian Charts) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
        <div class="bg-slate-900/40 backdrop-blur-md border border-white/5 p-8 rounded-[3rem] shadow-2xl">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-white">Growth Velocity</h3>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mt-0.5 leading-none">Tenant Acquisition (30 Days)</p>
            </div>
            <div style="height: 300px;">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>

        <div class="bg-slate-900/40 backdrop-blur-md border border-white/5 p-8 rounded-[3rem] shadow-2xl">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-white">Node Load Density</h3>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mt-0.5 leading-none">Call Activity Volatility (30 Days)</p>
            </div>
            <div style="height: 300px;">
                <canvas id="callsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Event Feed (Obsidian List) -->
    <div class="bg-slate-900/40 backdrop-blur-md border border-white/5 rounded-[3rem] shadow-2xl overflow-hidden">
        <div class="px-8 py-6 border-b border-white/5 flex items-center justify-between bg-white/[0.02]">
            <div>
                <h3 class="font-bold text-white">Operational Event Stream</h3>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mt-0.5">Real-time system modifications</p>
            </div>
            <button class="px-5 py-2.5 border border-white/10 text-slate-400 hover:bg-white/5 text-[10px] font-black uppercase tracking-widest rounded-2xl transition-all">Export Protocol</button>
        </div>

        <div class="divide-y divide-white/5">
            @forelse($recentActivity as $activity)
                <div class="flex items-center justify-between px-8 py-5 hover:bg-white/5 transition-all group">
                    <div class="flex items-center gap-5">
                        <div class="w-10 h-10 rounded-xl bg-slate-800/50 border border-white/5 flex items-center justify-center text-slate-500 group-hover:text-indigo-400 transition-colors">
                            <i data-lucide="activity" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="text-sm font-bold text-slate-100 group-hover:text-white transition-colors">{{ $activity->description }}</span>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">System Trace</span>
                                <div class="w-1 h-1 rounded-full bg-slate-700"></div>
                                <span class="text-[10px] font-medium text-slate-500 font-mono italic">Ref: {{ substr($activity->id ?? 'LOG', 0, 8) }}</span>
                            </div>
                        </div>
                    </div>
                    <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">{{ $activity->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <div class="py-16 text-center">
                    <p class="text-slate-500 text-[10px] font-black uppercase tracking-[0.2em]">No recent operational events detected</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
        // Mission Control Chart Styling (Obsidian Tuning)
        Chart.defaults.color = 'rgba(255, 255, 255, 0.4)';
        Chart.defaults.font.family = 'Outfit';
        Chart.defaults.font.weight = '900';
        Chart.defaults.font.size = 9;

        // User Growth Chart Reconstruction
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        const userGradient = userGrowthCtx.createLinearGradient(0, 0, 0, 350);
        userGradient.addColorStop(0, 'rgba(99, 102, 241, 0.2)');
        userGradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: @json($userGrowth->pluck('date')),
                datasets: [{
                    label: 'Registrations',
                    data: @json($userGrowth->pluck('count')),
                    borderColor: '#6366f1',
                    borderWidth: 3,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#0c111d',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.45,
                    fill: true,
                    backgroundColor: userGradient
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0c111d',
                        titleFont: { size: 12, weight: 'black' },
                        bodyFont: { size: 12 },
                        padding: 15,
                        displayColors: false,
                        borderColor: 'rgba(255,255,255,0.05)',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255,255,255,0.03)', drawBorder: false },
                        border: { display: false },
                        ticks: { padding: 15 }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { padding: 15 }
                    }
                }
            }
        });

        // Calls Bar Chart Reconstruction
        const callsCtx = document.getElementById('callsChart').getContext('2d');
        const callsGradient = callsCtx.createLinearGradient(0, 0, 0, 350);
        callsGradient.addColorStop(0, '#6366f1');
        callsGradient.addColorStop(1, '#a855f7');

        new Chart(callsCtx, {
            type: 'bar',
            data: {
                labels: @json($callsPerDay->pluck('date')),
                datasets: [{
                    label: 'Call Density',
                    data: @json($callsPerDay->pluck('count')),
                    backgroundColor: callsGradient,
                    borderRadius: 12,
                    barPercentage: 0.5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#020617',
                        padding: 15,
                        displayColors: false,
                        titleFont: { size: 12, weight: '900' },
                        bodyFont: { size: 12 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255,255,255,0.03)', drawBorder: false },
                        border: { display: false },
                        ticks: { padding: 15 }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { padding: 15 }
                    }
                }
            }
        });
    </script>
@endpush