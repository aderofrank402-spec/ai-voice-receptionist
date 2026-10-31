@extends('admin.layout')

@section('title', 'System Health')

@section('content')
    <div class="mb-10">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">System Integrity</h1>
        <p class="text-slate-500 mt-1 font-medium italic">Real-time health telemetry across global compute nodes and bridges.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-10">
        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm hover:shadow-md transition-all group">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Core Persistence (Postgres)</h3>
            <div class="flex items-center gap-3">
                @if($health['database']['status'] === 'healthy')
                    <div class="w-3 h-3 bg-emerald-500 rounded-full shadow-[0_0_8px_rgba(16,185,129,0.4)] animate-pulse"></div>
                    <span class="text-emerald-600 font-black text-lg tracking-tight uppercase italic">Operational</span>
                @else
                    <div class="w-3 h-3 bg-rose-500 rounded-full shadow-[0_0_8px_rgba(244,63,94,0.4)]"></div>
                    <span class="text-rose-600 font-black text-lg tracking-tight uppercase italic">Critical</span>
                @endif
            </div>
            <p class="text-[11px] text-slate-400 mt-3 font-medium">{{ $health['database']['message'] }}</p>
        </div>

        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm hover:shadow-md transition-all group">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Node Storage Density</h3>
            <div class="flex items-center gap-3">
                <div class="flex-1 h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-200/50">
                    <div class="h-full bg-slate-900 transition-all duration-1000" style="width: {{ $health['disk_space']['percentage'] }}%"></div>
                </div>
                <span class="text-slate-900 font-black text-lg tracking-tighter">{{ $health['disk_space']['percentage'] }}%</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-3 font-medium">{{ $health['disk_space']['used'] }} utilized of {{ $health['disk_space']['total'] }} availability.</p>
        </div>

        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm hover:shadow-md transition-all group">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Queue Throughput (Redis)</h3>
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 bg-emerald-500 rounded-full shadow-[0_0_8px_rgba(16,185,129,0.4)]"></div>
                <span class="text-emerald-600 font-black text-lg tracking-tight uppercase italic">Nominal</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-3 font-medium">{{ $health['queue_status']['pending_jobs'] }} tasks currently in global processing queue.</p>
        </div>

        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm hover:shadow-md transition-all group">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Voice Engine (Real-time Hub)</h3>
            <div class="flex items-center gap-3">
                @if($health['voice_engine']['status'] === 'healthy')
                    <div class="w-3 h-3 bg-emerald-500 rounded-full shadow-[0_0_8px_rgba(16,185,129,0.4)]"></div>
                    <span class="text-emerald-600 font-black text-lg tracking-tight uppercase italic">Synched</span>
                @else
                    <div class="w-3 h-3 bg-rose-500 rounded-full shadow-[0_0_8px_rgba(244,63,94,0.4)]"></div>
                    <span class="text-rose-600 font-black text-lg tracking-tight uppercase italic">Desynched</span>
                @endif
            </div>
            <p class="text-[11px] text-slate-400 mt-3 font-medium">Port 3000 connectivity check: {{ $health['voice_engine']['message'] ?? 'Verified' }}.</p>
        </div>

        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm hover:shadow-md transition-all group">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Telephony Gateway (Bridge)</h3>
            <div class="flex items-center gap-3">
                @if($health['telephony']['status'] === 'healthy')
                    <div class="w-3 h-3 bg-emerald-500 rounded-full shadow-[0_0_8px_rgba(16,185,129,0.4)]"></div>
                    <span class="text-emerald-600 font-black text-lg tracking-tight uppercase italic">Secured</span>
                @else
                    <div class="w-3 h-3 bg-rose-500 rounded-full shadow-[0_0_8px_rgba(244,63,94,0.4)]"></div>
                    <span class="text-rose-600 font-black text-lg tracking-tight uppercase italic">Blocked</span>
                @endif
            </div>
            <p class="text-[11px] text-slate-400 mt-3 font-medium">Port 8081 operational response: {{ $health['telephony']['message'] ?? 'Active' }}.</p>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden mb-10">
        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-900">Protocol Exception Log</h3>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Recent Node Errors</span>
        </div>
        <div class="p-8">
            @if(count($recentErrors) > 0)
                <div class="space-y-4">
                    @foreach($recentErrors as $error)
                        <div class="p-5 bg-rose-50 border border-rose-100 rounded-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <p class="text-rose-600 font-bold text-sm tracking-tight">{{ $error->message }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-black text-rose-400 uppercase tracking-widest">Stack Breach</span>
                                    <div class="w-1 h-1 rounded-full bg-rose-200"></div>
                                    <span class="text-[10px] text-rose-400 font-medium italic">{{ $error->created_at }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-16 text-center">
                    <div class="w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 mx-auto mb-4 border border-emerald-100">
                        <i data-lucide="shield-check" class="w-8 h-8"></i>
                    </div>
                    <p class="text-slate-400 text-xs font-black uppercase tracking-widest italic">System logs verified and clear.</p>
                </div>
            @endif
        </div>
    </div>
@endsection