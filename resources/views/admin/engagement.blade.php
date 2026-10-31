@extends('admin.layout')

@section('title', 'Engagement Metrics')

@section('content')
    <div class="mb-10">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Engagement Metrics</h1>
        <p class="text-slate-500 mt-1 font-medium italic">High-fidelity analysis of node retention and categorical distributions.</p>
    </div>

    <!-- Engagement Lite Pulse -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm hover:shadow-md transition-all">
            <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-2">Node Efficiency</h3>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-black text-slate-900 tracking-tighter">{{ $avgCallsPerUser }}</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Events/Node</span>
            </div>
            <p class="text-[9px] text-slate-400 mt-4 font-black uppercase tracking-widest italic">Global averaging across {{ $totalUsers }} entities.</p>
        </div>

        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm hover:shadow-md transition-all">
            <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-2">7-Day Retention</h3>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-black text-slate-900 tracking-tighter">{{ $retention7Day }}%</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Consistency</span>
            </div>
            <p class="text-[9px] text-slate-400 mt-4 font-black uppercase tracking-widest italic">{{ $activeUsers7Days }} entities active in phase.</p>
        </div>

        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm hover:shadow-md transition-all">
            <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-2">30-Day Retention</h3>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-black text-slate-900 tracking-tighter">{{ $retention30Day }}%</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Density</span>
            </div>
            <p class="text-[9px] text-slate-400 mt-4 font-black uppercase tracking-widest italic">{{ $activeUsers30Days }} entities active in cycle.</p>
        </div>

        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm hover:shadow-md transition-all">
            <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-2">Cluster Diversity</h3>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-black text-slate-900 tracking-tighter">{{ $agentsByType->count() }}</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Species</span>
            </div>
            <p class="text-[9px] text-slate-400 mt-4 font-black uppercase tracking-widest italic">Categorical cognitive unit types.</p>
        </div>
    </div>

    <!-- Distribution & Leaderboard -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- Leaderboard -->
        <div class="bg-white border border-slate-200 rounded-[2.5rem] shadow-sm overflow-hidden flex flex-col">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-base font-black text-slate-900">Highest Throughput Nodes</h3>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Elite Performers</span>
            </div>
            <div class="p-6 space-y-4">
                @forelse($leaderboard as $index => $user)
                    <div class="flex items-center justify-between p-5 bg-slate-50/50 border border-slate-100 rounded-2xl hover:border-indigo-100 transition-all shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 font-black text-sm">
                                @if($index === 0) 🏆 @else {{ $index + 1 }} @endif
                            </div>
                            <div>
                                <p class="text-slate-900 font-bold tracking-tight text-sm">{{ $user->name }}</p>
                                <p class="text-[9px] text-slate-400 font-black uppercase tracking-widest">{{ $user->agents_count }} Managed Units</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-base font-black text-slate-900 tracking-tighter">{{ number_format($user->total_calls ?? 0) }}</span>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest italic">Events</p>
                        </div>
                    </div>
                @empty
                    <div class="py-24 text-center">
                        <i data-lucide="ghost" class="w-12 h-12 text-slate-200 mx-auto mb-4"></i>
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">No Active entities recorded</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Categorical Distribution -->
        <div class="bg-white border border-slate-200 rounded-[2.5rem] shadow-sm p-8 flex flex-col">
            <h3 class="text-base font-black text-slate-900 tracking-tight mb-8 border-b border-slate-100 pb-6">Phylogenetic Categorization</h3>
            <div class="flex-1 w-full" style="min-height: 300px;">
                <canvas id="agentTypesChart"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const labelColor = '#94a3b8';
        const axisColor = '#e2e8f0';

        // Categorical Bar Chart (Lite)
        const agentTypesCtx = document.getElementById('agentTypesChart').getContext('2d');
        new Chart(agentTypesCtx, {
            type: 'bar',
            data: {
                labels: @json($agentsByType->keys()),
                datasets: [{
                    label: 'Deployment Count',
                    data: @json($agentsByType->values()),
                    backgroundColor: '#0f172a', // Slate 900
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: labelColor, font: { size: 9, weight: 'bold' } },
                        grid: { color: axisColor, drawBorder: false }
                    },
                    x: {
                        ticks: { color: labelColor, font: { size: 9, weight: 'bold' } },
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
@endpush
@endsection

@push('scripts')
    <script>
        // Agent Types Bar Chart
        const agentTypesCtx = document.getElementById('agentTypesChart').getContext('2d');
        new Chart(agentTypesCtx, {
            type: 'bar',
            data: {
                labels: @json($agentsByType->keys()),
                datasets: [{
                    label: 'Number of Agents',
                    data: @json($agentsByType->values()),
                    backgroundColor: '#06b6d4',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#9ca3af' },
                        grid: { color: '#374151' }
                    },
                    x: {
                        ticks: { color: '#9ca3af' },
                        grid: { display: false }
                    }
                }
            }
        });

        // Retention Comparison Chart
        const retentionCtx = document.getElementById('retentionChart').getContext('2d');
        new Chart(retentionCtx, {
            type: 'bar',
            data: {
                labels: ['7-Day Retention', '30-Day Retention'],
                datasets: [{
                    label: 'Retention Rate (%)',
                    data: [{{ $retention7Day }}, {{ $retention30Day }}],
                    backgroundColor: ['#10b981', '#3b82f6'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            color: '#9ca3af',
                            callback: function (value) {
                                return value + '%';
                            }
                        },
                        grid: { color: '#374151' }
                    },
                    x: {
                        ticks: { color: '#9ca3af' },
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
@endpush