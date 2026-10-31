@extends('admin.layout')

@section('title', 'Economics & API Usage')

@section('content')
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Platform Economics</h1>
            <p class="text-slate-500 mt-1 font-medium italic">High-fidelity tracking of infrastructure density and compute overhead.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 bg-slate-100 text-slate-500 text-[9px] font-black rounded-md border border-slate-200 tracking-widest uppercase">Node Telemetry Active</span>
        </div>
    </div>

    <!-- Economics Lite Pulse -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
        <!-- Budget Pulse -->
        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm hover:shadow-md transition-all relative overflow-hidden group">
            <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-4">Cycle Budget Pulse</h3>
            <div class="flex items-baseline gap-2 mb-3">
                <span class="text-3xl font-black text-slate-900 tracking-tighter">${{ number_format($mtdSpend, 2) }}</span>
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">/ ${{ number_format($budgetLimit, 0) }}</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mb-2 overflow-hidden border border-slate-200/50">
                <div class="bg-slate-900 h-1.5 rounded-full transition-all duration-1000" style="width: {{ $budgetUsedPercentage }}%"></div>
            </div>
            <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mt-2">{{ $budgetUsedPercentage }}% Consumption Ratio</p>
        </div>

        <!-- Predictive Forecast -->
        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm hover:shadow-md transition-all">
            <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-4">Predictive Liquidity</h3>
            <div class="flex items-baseline gap-2 mb-2">
                <span class="text-3xl font-black text-slate-900 tracking-tighter">${{ number_format($eomForecast, 2) }}</span>
                @if($forecastBreach)
                    <span class="text-[8px] bg-rose-50 text-rose-600 px-1.5 py-0.5 rounded font-black border border-rose-100 lowercase">alert</span>
                @else
                    <span class="text-[8px] bg-emerald-50 text-emerald-600 px-1.5 py-0.5 rounded font-black border border-emerald-100 lowercase">stable</span>
                @endif
            </div>
            <p class="text-[10px] font-black uppercase tracking-widest mt-3 {{ $forecastBreach ? 'text-rose-600' : 'text-emerald-600' }}">
                {{ $forecastBreach ? '▲ Breach Boundary detected' : '▼ Policy Conformance' }}
            </p>
        </div>

        <!-- Margin Audit -->
        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm hover:shadow-md transition-all">
            <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-4">Gross Yield Density</h3>
            <div class="flex items-baseline gap-2 mb-1">
                <p class="text-3xl font-black {{ $estimatedMargin >= 0 ? 'text-slate-900' : 'text-rose-600' }} tracking-tighter">
                    ${{ number_format($estimatedMargin, 2) }}
                </p>
            </div>
            <p class="text-[10px] text-slate-400 mt-2 font-black uppercase tracking-widest italic">Efficiency: {{ $estimatedRevenue > 0 ? number_format(($estimatedMargin / $estimatedRevenue) * 100, 1) : 0 }}% Ratio</p>
        </div>

        <!-- Volume -->
        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm hover:shadow-md transition-all">
            <h3 class="text-slate-400 text-[10px] font-black uppercase tracking-widest mb-4">Traffic Velocity</h3>
            <p class="text-3xl font-black text-slate-900 tracking-tighter">{{ number_format($totalCalls) }}<span class="text-[10px] text-slate-400 ml-1 font-bold uppercase tracking-widest">Calls</span></p>
            <p class="text-[10px] text-slate-400 mt-3 font-black uppercase tracking-widest italic">Global Node Throughput</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-3xl p-8 shadow-sm">
            <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-8 border-b border-slate-100 pb-6">
                <h3 class="text-lg font-black text-slate-900 tracking-tight">Temporal Usage Heuristics</h3>
                <div class="flex flex-wrap gap-4">
                    <span class="flex items-center text-[10px] font-black uppercase tracking-widest text-slate-400"><span class="w-2.5 h-2.5 rounded-sm bg-slate-900 mr-2"></span> LLM</span>
                    <span class="flex items-center text-[10px] font-black uppercase tracking-widest text-slate-400"><span class="w-2.5 h-2.5 rounded-sm bg-slate-300 mr-2"></span> STT</span>
                    <span class="flex items-center text-[10px] font-black uppercase tracking-widest text-slate-400"><span class="w-2.5 h-2.5 rounded-sm bg-slate-100 mr-2"></span> TTS</span>
                </div>
            </div>
            <div style="height: 350px;">
                <canvas id="dailyCostsChart"></canvas>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-sm flex flex-col">
            <h3 class="text-lg font-black text-slate-900 tracking-tight mb-8 border-b border-slate-100 pb-6">Spectral Distribution</h3>
            <div class="flex-1 w-full relative mb-8" style="min-height: 250px;">
                <canvas id="costDistributionChart"></canvas>
            </div>
            <div class="space-y-4">
                <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest">
                    <span class="text-slate-400">Telephony Hub</span>
                    <span class="text-slate-900 font-mono tracking-tighter">${{ number_format($componentCosts['phone'], 4) }}</span>
                </div>
                <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest">
                    <span class="text-slate-400">Cognitive Hub</span>
                    <span class="text-slate-900 font-mono tracking-tighter">${{ number_format($componentCosts['llm'], 4) }}</span>
                </div>
                <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-widest border-t border-slate-50 pt-4 mt-2">
                    <span class="text-slate-500">Cumulative Overhead</span>
                    <span class="text-slate-900 text-lg tracking-tighter">${{ number_format($totalUsd, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Governance & Consumers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-base font-black text-slate-900">Entity Consumption</h3>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">High-Volume Nodes</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[9px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-100">
                        <tr>
                            <th class="px-8 py-4">Node Profile</th>
                            <th class="px-8 py-4">Throughput</th>
                            <th class="px-8 py-4 text-right">Burn Rate (USD)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($costPerUser as $user)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-5">
                                <span class="font-black text-slate-900 tracking-tight">{{ $user->name }}</span>
                            </td>
                            <td class="px-8 py-5 text-slate-400 font-bold text-xs">{{ $user->calls_count }} events</td>
                            <td class="px-8 py-5 text-right font-black text-slate-900 tracking-tighter">${{ number_format($user->total_cost_usd, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl p-10 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-xl font-black text-slate-900 tracking-tight mb-2">Economic Governance</h3>
                <p class="text-xs text-slate-400 mb-8 font-medium italic">Execute global policy adjustments for infrastructure fiscal limits.</p>
                
                <form action="{{ route('admin.economics.update') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Monthly Operational Ceiling (USD)</label>
                        <div class="relative group">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 font-black group-focus-within:text-slate-900 transition-colors">$</span>
                            <input 
                                type="number" 
                                name="platform_budget_usd" 
                                value="{{ $budgetLimit }}" 
                                step="0.01" 
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-10 pr-6 py-4 text-slate-900 font-black tracking-tight focus:bg-white focus:border-slate-900 transition-all outline-none shadow-inner"
                            >
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full py-4 bg-slate-900 text-white text-xs font-black uppercase tracking-[0.2em] rounded-2xl transition-all hover:bg-slate-800 shadow-xl shadow-slate-200 active:scale-95 mt-4">
                        Re-Execute Policy
                    </button>
                </form>
            </div>

            <div class="mt-10 p-6 bg-slate-50 border border-slate-100 rounded-2xl">
                <div class="flex gap-4">
                    <div class="mt-1 text-slate-400"><i data-lucide="info" class="w-4 h-4"></i></div>
                    <p class="text-[10px] text-slate-400 leading-relaxed font-bold italic tracking-tight">
                        Governance modifications are permanently logged set against critical node identifiers. Forecast recalculation triggered upon commit.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const labelColor = '#94a3b8';
        const axisColor = '#e2e8f0';

        // Daily Costs Chart (Lite - High Contrast)
        const dailyCtx = document.getElementById('dailyCostsChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'bar',
            data: {
                labels: @json($dailyCosts->pluck('date')),
                datasets: [
                    {
                        label: 'LLM',
                        data: @json($dailyCosts->pluck('llm')),
                        backgroundColor: '#0f172a', // Slate 900
                        borderRadius: 6,
                    },
                    {
                        label: 'STT',
                        data: @json($dailyCosts->pluck('stt')),
                        backgroundColor: '#cbd5e1', // Slate 300
                        borderRadius: 6,
                    },
                    {
                        label: 'TTS',
                        data: @json($dailyCosts->pluck('tts')),
                        backgroundColor: '#f1f5f9', // Slate 100
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { 
                        stacked: true, 
                        grid: { display: false }, 
                        ticks: { color: labelColor, font: { size: 9, weight: 'bold' } } 
                    },
                    y: { 
                        stacked: true, 
                        grid: { color: axisColor, drawBorder: false }, 
                        ticks: { color: labelColor, font: { size: 9, weight: 'bold' }, callback: v => '$' + v } 
                    }
                }
            }
        });

        // Distribution Doughnut (Lite)
        const distCtx = document.getElementById('costDistributionChart').getContext('2d');
        new Chart(distCtx, {
            type: 'doughnut',
            data: {
                labels: ['STT', 'LLM', 'TTS', 'Phone'],
                datasets: [{
                    data: [
                        {{ $componentCosts['stt'] }},
                        {{ $componentCosts['llm'] }},
                        {{ $componentCosts['tts'] }},
                        {{ $componentCosts['phone'] }}
                    ],
                    backgroundColor: ['#cbd5e1', '#0f172a', '#f1f5f9', '#94a3b8'],
                    borderWidth: 8,
                    borderColor: '#ffffff',
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '82%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endpush
push