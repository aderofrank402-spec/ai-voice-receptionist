@extends('admin.layout')

@section('title', 'User Details')

@section('content')
<div x-data="{ 
    showModal: false, 
    modalTitle: '', 
    modalMessage: '', 
    modalAction: '', 
    modalButtonText: '', 
    modalButtonColor: '',
    triggerModal(title, message, action, buttonText, color) {
        this.modalTitle = title;
        this.modalMessage = message;
        this.modalAction = action;
        this.modalButtonText = buttonText;
        this.modalButtonColor = color;
        this.showModal = true;
    }
}">
    <div class="mb-10">
        <a href="{{ route('admin.users') }}" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-indigo-600 flex items-center gap-2 transition-colors group">
            <i data-lucide="arrow-left" class="w-3 h-3 group-hover:-translate-x-1 transition-transform"></i>
            Registry Return
        </a>
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mt-8">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-slate-900 flex items-center justify-center text-white font-black text-2xl shadow-xl shadow-slate-200">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-black text-slate-900 tracking-tight">{{ $user->name }}</h1>
                        @if($user->isSuspended())
                            <span class="px-2 py-0.5 bg-rose-50 text-rose-600 text-[9px] font-black uppercase tracking-widest rounded-md border border-rose-100">Suspended Node</span>
                        @else
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-widest rounded-md border border-emerald-100">Active Node</span>
                        @endif
                    </div>
                    <p class="text-slate-500 font-medium italic text-sm mt-0.5">{{ $user->email }}</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                @can('manage-users')
                @if($user->isSuspended())
                    <button type="button" 
                        @click="triggerModal('Reactivate Account', 'Are you sure you want to restore access for {{ $user->email }}?', '{{ route('admin.users.activate', $user) }}', 'Restore Access', 'bg-emerald-600')"
                        class="px-6 py-2.5 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-sm hover:bg-slate-800">
                        Activate Entity
                    </button>
                @elseif(!$user->is_admin)
                    <button type="button" 
                        @click="triggerModal('Suspend Account', 'Are you sure you want to suspend {{ $user->email }}?', '{{ route('admin.users.suspend', $user) }}', 'Suspend Entity', 'bg-rose-600')"
                        class="px-6 py-2.5 bg-rose-50 border border-rose-100 text-rose-600 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all hover:bg-rose-100 shadow-sm">
                        Suspend Entity
                    </button>
                @endif
                @endcan
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm hover:shadow-md transition-shadow">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Fleet Scale</h3>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-black text-slate-900 tracking-tighter">{{ number_format($user->agents->count()) }}</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Active Units</span>
            </div>
        </div>
        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm hover:shadow-md transition-shadow">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Operational Depth</h3>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-black text-slate-900 tracking-tighter">{{ number_format($recentCalls->count()) }}</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter italic">Total Interactions</span>
            </div>
        </div>
        <div class="bg-white border border-slate-200 p-8 rounded-3xl shadow-sm hover:shadow-md transition-shadow">
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Node Residency</h3>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-black text-slate-900 tracking-tighter italic">{{ $user->created_at->format('M Y') }}</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Deployment Date</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- Managed Units -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-3">
                    <i data-lucide="bot" class="w-5 h-5 text-indigo-500"></i>
                    Cognitive Unit Deployment
                </h3>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Local Registry</span>
            </div>
            <div class="p-4 space-y-4">
                @forelse($user->agents as $agent)
                    <div class="p-6 bg-slate-50/50 border border-slate-100 rounded-2xl hover:border-indigo-100 transition-all flex justify-between items-center group shadow-sm hover:shadow-indigo-50">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 group-hover:text-indigo-600 transition-colors">
                                <i data-lucide="cpu" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-slate-900 font-bold text-sm tracking-tight">{{ $agent->name }}</h4>
                                <p class="text-[9px] text-slate-400 uppercase font-black tracking-widest">{{ $agent->configuration['business_type'] ?? 'General Purpose' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-black text-slate-900 tracking-tighter">{{ number_format($agent->calls->count()) }}</span>
                            <div class="text-[9px] text-slate-400 uppercase font-bold tracking-tighter">Throughput</div>
                        </div>
                    </div>
                @empty
                    <div class="py-24 text-center">
                        <i data-lucide="ghost" class="w-12 h-12 text-slate-200 mx-auto mb-4"></i>
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">No cognitive units registered</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Call Telemetry -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden flex flex-col">
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-3">
                    <i data-lucide="activity" class="w-5 h-5 text-rose-500"></i>
                    Interaction Telemetry
                </h3>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Real-time Stream</span>
            </div>
            <div class="p-4 space-y-4">
                @forelse($recentCalls as $call)
                    <div class="p-6 bg-slate-50/50 border border-slate-100 rounded-2xl flex justify-between items-center shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-400">
                                <i data-lucide="phone-call" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-900 font-mono text-xs font-bold">{{ substr($call->call_id, 0, 10) }}...</span>
                                    <span class="px-1.5 py-0.5 rounded-md text-[8px] font-black uppercase tracking-tighter {{ str_contains(strtolower($call->status), 'success') ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $call->status }}
                                    </span>
                                </div>
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ $call->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-base font-black text-slate-900 tracking-tighter">{{ $call->duration }}<span class="text-[10px] text-slate-400 font-medium ml-0.5">s</span></span>
                            <div class="text-[9px] text-slate-400 uppercase font-bold tracking-tighter italic">Vocal Depth</div>
                        </div>
                    </div>
                @empty
                    <div class="py-24 text-center">
                        <i data-lucide="zap-off" class="w-12 h-12 text-slate-200 mx-auto mb-4"></i>
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">No Interaction telemetry recorded</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Premium Action Modal -->
    <div x-show="showModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        
        <div @click.away="showModal = false" 
             class="bg-gray-900 border border-gray-800 rounded-3xl w-full max-w-md overflow-hidden shadow-2xl shadow-black/50"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100">
            
            <div class="p-8">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-6" :class="modalButtonColor === 'bg-rose-600' ? 'bg-rose-500/10 text-rose-500' : 'bg-emerald-500/10 text-emerald-400'">
                    <i :data-lucide="modalButtonColor === 'bg-rose-600' ? 'shield-off' : 'shield-check'" class="w-8 h-8"></i>
                </div>
                
                <h3 class="text-2xl font-bold text-white mb-2" x-text="modalTitle"></h3>
                <p class="text-gray-400 leading-relaxed" x-text="modalMessage"></p>
                
                <div class="mt-8 flex gap-3">
                    <button @click="showModal = false" 
                            class="flex-1 px-6 py-3 bg-gray-800 hover:bg-gray-700 text-white font-bold rounded-xl transition-all active:scale-95">
                        Cancel
                    </button>
                    
                    <form :action="modalAction" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" 
                                :class="modalButtonColor"
                                class="w-full px-6 py-3 text-white font-bold rounded-xl transition-all hover:brightness-110 active:scale-95 shadow-lg"
                                x-text="modalButtonText">
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection