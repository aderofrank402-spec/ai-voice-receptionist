@extends('admin.layout')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Performance Pulse</h1>
    <p class="text-slate-500 mt-1 font-medium">Real-time latency and reliability tracking for AI infrastructure nodes.</p>
</div>

<!-- Provider Pulse Cards (Lite) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    @php
        $providers = [
            'groq' => ['name' => 'Groq (LLM)', 'icon' => '⚡', 'threshold' => 800],
            'deepgram' => ['name' => 'Deepgram (STT)', 'icon' => '👂', 'threshold' => 300],
            'elevenlabs' => ['name' => 'ElevenLabs (TTS)', 'icon' => '🗣️', 'threshold' => 1000],
        ];
    @endphp

    @foreach($providers as $slug => $info)
        @php
            $metric = $providerMetrics[$slug] ?? null;
            $latency = $metric ? round($metric->avg_latency) : 0;
            $status = 'Offline';
            $statusColor = 'slate';
            
            if ($metric && $metric->total_requests > 0) {
                if ($latency < $info['threshold']) {
                    $status = 'Optimal';
                    $statusColor = 'emerald';
                } elseif ($latency < $info['threshold'] * 2) {
                    $status = 'Degraded';
                    $statusColor = 'amber';
                } else {
                    $status = 'Critical';
                    $statusColor = 'rose';
                }
            }
        @endphp
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-{{ $statusColor }}-50 text-{{ $statusColor }}-600 flex items-center justify-center text-lg border border-{{ $statusColor }}-100">
                        {{ $info['icon'] }}
                    </div>
                    <div>
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $info['name'] }}</h3>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-{{ $statusColor }}-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-{{ $statusColor }}-500"></span>
                            </span>
                            <span class="text-[10px] font-black text-{{ $statusColor }}-600 uppercase tracking-tighter">{{ $status }}</span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-black text-slate-900 tracking-tighter">{{ $latency }}<span class="text-xs text-slate-400 font-medium ml-0.5 italic">ms</span></div>
                    <div class="text-[8px] text-slate-400 uppercase font-black tracking-widest">Latency</div>
                </div>
            </div>
            
            <div class="space-y-3 mt-6">
                <div class="flex justify-between text-[10px] font-bold uppercase tracking-tight">
                    <span class="text-slate-400">Success Rate</span>
                    <span class="text-{{ ($metric->success_rate ?? 0) > 95 ? 'emerald' : 'amber' }}-600">{{ $metric->success_rate ?? 0 }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-{{ ($metric->success_rate ?? 0) > 95 ? 'emerald' : 'amber' }}-500 h-1.5 rounded-full" style="width: {{ $metric->success_rate ?? 0 }}%"></div>
                </div>
                <div class="flex justify-between text-[9px] font-bold text-slate-300 uppercase tracking-wider">
                    <span>{{ $metric->total_requests ?? 0 }} total requests</span>
                    <span>24h window</span>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Latency Timeline -->
    <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-sm">
        <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
            <i data-lucide="activity" class="w-5 h-5 text-indigo-500"></i>
            Latency Timeline (24h)
        </h3>
        <div class="h-[300px]">
            <canvas id="latencyChart"></canvas>
        </div>
    </div>

    <!-- Call Success Distribution -->
    <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-sm">
        <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500"></i>
            Node Integrity (30d)
        </h3>
        <div class="h-[300px]">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h3 class="text-lg font-bold text-slate-900">Platform Exhaustion Monitor</h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Global connection heuristics</p>
        </div>
        <span class="px-3 py-1 rounded-full bg-slate-50 border border-slate-200 text-slate-500 text-[10px] font-black uppercase">Real-time Node</span>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 shadow-inner">
            <div class="text-[10px] text-slate-400 uppercase font-bold mb-1 tracking-widest">Total Calls</div>
            <div class="text-2xl font-black text-slate-900 tracking-tighter">{{ number_format($totalCalls) }}</div>
        </div>
        <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 shadow-inner">
            <div class="text-[10px] text-slate-400 uppercase font-bold mb-1 tracking-widest">Successful</div>
            <div class="text-2xl font-black text-emerald-600 tracking-tighter">{{ number_format($successfulCalls) }}</div>
        </div>
        <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 shadow-inner">
            <div class="text-[10px] text-slate-400 uppercase font-bold mb-1 tracking-widest">Success Rate</div>
            <div class="text-2xl font-black text-indigo-600 tracking-tighter">{{ $successRate }}%</div>
        </div>
        <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 shadow-inner">
            <div class="text-[10px] text-slate-400 uppercase font-bold mb-1 tracking-widest">Error Rate</div>
            <div class="text-2xl font-black text-rose-500 tracking-tighter">{{ $errorRate }}%</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Latency Timeline Chart
    new Chart(document.getElementById('latencyChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($latencyOverTime->pluck('hour')) !!},
            datasets: [{
                label: 'Avg Latency (ms)',
                data: {!! json_encode($latencyOverTime->pluck('avg_latency')) !!},
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6, 182, 212, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: '#374151' }, ticks: { color: '#9ca3af' } },
                x: { grid: { display: false }, ticks: { color: '#9ca3af', maxRotation: 0 } }
            }
        }
    });

    // Success vs Failed Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($callsByStatus->pluck('date')) !!},
            datasets: [
                {
                    label: 'Successful',
                    data: {!! json_encode($callsByStatus->pluck('successful')) !!},
                    backgroundColor: '#10b981',
                    borderRadius: 4
                },
                {
                    label: 'Failed',
                    data: {!! json_encode($callsByStatus->pluck('failed')) !!},
                    backgroundColor: '#ef4444',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { stacked: true, grid: { color: '#374151' }, ticks: { color: '#9ca3af' } },
                x: { stacked: true, grid: { display: false }, ticks: { color: '#9ca3af' } }
            }
        }
    });
});
</script>
@endpush
@endsection