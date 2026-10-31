@extends('admin.layout')

@section('title', 'Agents Gallery')

@section('content')
    <div class="mb-10">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Agent Fleet</h1>
        <p class="text-slate-500 mt-1 font-medium italic">Global registry of all active cognitive units and system prompts.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($agents as $agent)
            <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-sm hover:shadow-md transition-all group flex flex-col h-full">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 group-hover:text-indigo-600 transition-colors">
                        <i data-lucide="bot" class="w-6 h-6"></i>
                    </div>
                    @if($agent->status === 'active')
                        <span class="px-2.5 py-1 rounded-full text-[9px] font-black bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-widest italic">Operational</span>
                    @endif
                </div>

                <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ $agent->name }}</h3>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Assigned to: <span class="text-slate-600 italic">{{ $agent->user->name ?? 'Unknown Node' }}</span></p>
                
                <div class="mt-6 flex-1">
                    <div class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Core Directive</div>
                    <div class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-5 rounded-2xl border border-slate-100 font-medium italic relative overflow-hidden max-h-48 overflow-y-auto custom-scrollbar">
                        {{ \Illuminate\Support\Str::limit($agent->system_prompt, 400) }}
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-between pt-6 border-t border-slate-50">
                    <div class="flex flex-col">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Throughput</span>
                        <span class="text-sm font-black text-slate-900 tracking-tighter">{{ number_format($agent->calls_count) }} <span class="text-[10px] text-slate-400 font-medium italic">Calls</span></span>
                    </div>
                    <span class="text-[10px] font-bold text-slate-300 uppercase italic">{{ $agent->created_at->diffForHumans() }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full py-32 text-center bg-white border border-slate-200 border-dashed rounded-[3rem]">
                <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 mx-auto mb-4">
                    <i data-lucide="ghost" class="w-8 h-8"></i>
                </div>
                <p class="text-slate-400 text-xs font-black uppercase tracking-[0.2em]">Agent registry empty</p>
            </div>
        @endforelse
    </div>

    <div class="mt-10">
        {{ $agents->links() }}
    </div>
@endsection