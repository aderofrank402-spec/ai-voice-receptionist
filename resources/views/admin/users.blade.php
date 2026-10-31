@extends('admin.layout')

@section('title', 'User Management')

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
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-white tracking-tight">User Management</h1>
            <p class="text-slate-400 mt-1 font-medium italic">Comprehensive registry of all active tenants and nodes.</p>
        </div>
        <div class="flex items-center gap-3">
            @can('view-economics')
            <a href="{{ route('admin.export.users') }}"
                class="px-4 py-2 bg-white/5 border border-white/10 text-slate-300 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-white/10 transition-all shadow-sm">
                <i data-lucide="download" class="w-4 h-4 mr-2 inline text-indigo-400"></i>
                Export
            </a>
            @endcan
            <form method="GET" class="flex items-center gap-2">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="text" name="search" placeholder="Filter entities..." value="{{ request('search') }}"
                        class="pl-10 pr-4 py-2 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all w-64 shadow-sm" />
                </div>
                <button type="submit" class="px-5 py-2 bg-slate-100 text-slate-900 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-white transition-colors shadow-sm">Search</button>
            </form>
        </div>
    </div>

    <div class="bg-slate-900/40 backdrop-blur-md border border-white/5 rounded-[3rem] shadow-2xl overflow-hidden mb-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-white/[0.02] text-slate-500 uppercase tracking-wider font-black text-[10px] border-b border-white/5">
                    <tr>
                        <th class="px-8 py-6">Entity Name</th>
                        <th class="px-8 py-6">Direct Access</th>
                        <th class="px-8 py-6">Node Count</th>
                        <th class="px-8 py-6">Throughput</th>
                        <th class="px-8 py-6">Registry Date</th>
                        <th class="px-8 py-6 text-right">Operational Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($users as $user)
                        <tr class="hover:bg-white/5 transition-colors group">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-slate-500 font-black text-xs">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-100 group-hover:text-white transition-colors">{{ $user->name }}</span>
                                        @if($user->role === 'super_admin')
                                            <span class="text-[9px] font-black text-indigo-400 uppercase tracking-tighter">Root Authority</span>
                                        @elseif($user->role === 'support_agent')
                                            <span class="text-[9px] font-black text-purple-400 uppercase tracking-tighter">Ops Support</span>
                                        @elseif($user->is_admin)
                                            <span class="text-[9px] font-black text-blue-400 uppercase tracking-tighter">Ops Admin</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-slate-400 text-xs font-medium">{{ $user->email }}</td>
                            <td class="px-8 py-5">
                                <span class="px-2 py-1 rounded-md bg-white/5 text-slate-300 text-[10px] font-black border border-white/5">{{ $user->agents_count }} Units</span>
                            </td>
                            <td class="px-8 py-5 text-slate-400 font-bold text-xs">{{ number_format($user->calls_count ?? 0) }} Calls</td>
                            <td class="px-8 py-5 text-slate-600 text-[10px] font-bold">{{ $user->created_at->format('M d, Y') }}</td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-4">
                                    @can('manage-users')
                                        @if($user->isSuspended())
                                            <button type="button" 
                                                @click="triggerModal('Reactivate Account', 'Are you sure you want to restore access for {{ $user->email }}?', '{{ route('admin.users.activate', $user) }}', 'Restore Access', 'bg-emerald-600')"
                                                class="text-emerald-600 hover:text-emerald-700 text-[10px] font-black uppercase tracking-widest">Activate</button>
                                        @elseif(!$user->is_admin && !$user->isSuperAdmin())
                                            <button type="button" 
                                                @click="triggerModal('Suspend Account', 'Are you sure you want to suspend {{ $user->email }}?', '{{ route('admin.users.suspend', $user) }}', 'Suspend Entity', 'bg-rose-600')"
                                                class="text-rose-500 hover:text-rose-600 text-[10px] font-black uppercase tracking-widest">Suspend</button>
                                        @endif
                                    @endcan
                                    
                                    <a href="{{ route('admin.users.details', $user) }}"
                                        class="px-3 py-1.5 bg-white/5 text-slate-400 hover:bg-white hover:text-slate-900 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all">Records</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-16 text-center text-slate-400 text-xs font-medium italic">Registry query returned zero entities.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-8 py-5 bg-slate-50 border-t border-slate-100">
            {{ $users->links() }}
        </div>
        @endif
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
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-6" :class="modalButtonColor === 'bg-rose-600' ? 'bg-rose-500/10 text-rose-500' : 'bg-emerald-500/10 text-emerald-500'">
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
            
            <div class="px-8 py-4 bg-black/20 border-t border-gray-800 flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-gray-600"></i>
                <span class="text-[10px] text-gray-600 font-bold uppercase tracking-widest">Authorized Action Required</span>
            </div>
        </div>
    </div>
</div>
@endsection