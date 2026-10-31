@extends('admin.layout')

@section('title', 'Security Ledger')

@section('content')
<!-- Ledger Lite Header -->
<div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Security Ledger</h1>
        <p class="text-slate-500 mt-1 font-medium italic">Immutable registry of administrative state modifications and node events.</p>
    </div>
    
    <div class="flex items-center gap-4">
        <button class="px-5 py-2.5 bg-slate-100 border border-slate-200 text-slate-700 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-slate-200 transition-all flex items-center gap-3">
            <i data-lucide="download" class="w-4 h-4"></i>
            Export Archive
        </button>
    </div>
</div>

<!-- Ledger Grid (Lite) -->
<div class="bg-white border border-slate-200 rounded-[2rem] shadow-sm overflow-hidden mb-10">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-extrabold text-[10px] border-b border-slate-100">
                <tr>
                    <th class="px-8 py-5">Node Timestamp</th>
                    <th class="px-8 py-5">Operator Entity</th>
                    <th class="px-8 py-5">Operation</th>
                    <th class="px-8 py-5">Target Record</th>
                    <th class="px-8 py-5">Integrity Mesh</th>
                    <th class="px-8 py-5 text-right">Inspect</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($audits as $audit)
                <tr class="hover:bg-slate-50/50 transition-all group">
                    <td class="px-8 py-5 whitespace-nowrap">
                        <div class="text-slate-900 font-bold tracking-tight">{{ $audit->created_at->format('M d, H:i:s') }}</div>
                        <div class="text-[9px] text-slate-400 font-black uppercase tracking-tighter mt-0.5">{{ $audit->created_at->diffForHumans() }}</div>
                    </td>
                    <td class="px-8 py-5 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 font-black text-xs">
                                {{ strtoupper(substr($audit->user->name ?? 'S', 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-slate-900 font-bold tracking-tight">{{ $audit->user->name ?? 'System Node' }}</div>
                                <div class="text-[10px] text-slate-400 font-medium italic">{{ $audit->user->email ?? 'automated@kernel' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-5 whitespace-nowrap">
                        @php
                            $badgeStyle = match($audit->action) {
                                'user_suspended' => 'bg-rose-50 text-rose-600 border-rose-100',
                                'user_activated' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                'credential_updated' => 'bg-amber-50 text-amber-600 border-amber-100',
                                'agent_modified' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                                'user_login' => 'bg-slate-50 text-slate-600 border-slate-100',
                                default => 'bg-slate-50 text-slate-400 border-slate-100'
                            };
                            $actionLabel = ucwords(str_replace('_', ' ', $audit->action));
                        @endphp
                        <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $badgeStyle }}">
                            {{ $actionLabel }}
                        </span>
                    </td>
                    <td class="px-8 py-5 whitespace-nowrap">
                        @if($audit->target_type)
                            <div class="flex items-center gap-2">
                                <span class="px-1.5 py-0.5 rounded-md bg-slate-50 text-slate-400 text-[8px] font-black border border-slate-100 lowercase">{{ class_basename($audit->target_type) }}</span>
                                <span class="text-slate-900 font-mono text-[11px] font-bold">#{{ $audit->target_id }}</span>
                            </div>
                        @else
                            <div class="text-[10px] text-slate-300 font-black italic tracking-widest">GLOBAL_MESH</div>
                        @endif
                    </td>
                    <td class="px-8 py-5">
                        <div class="text-[10px] text-indigo-600 font-mono font-black mb-0.5 tracking-tight">{{ $audit->ip_address }}</div>
                        <div class="text-[9px] text-slate-400 truncate max-w-[150px] font-medium" title="{{ $audit->user_agent }}">
                            {{ $audit->user_agent }}
                        </div>
                    </td>
                    <td class="px-8 py-5 text-right">
                        <button onclick="toggleAudit('{{ $audit->id }}')" class="w-9 h-9 rounded-lg border border-slate-100 bg-slate-50 text-slate-400 hover:text-indigo-600 hover:bg-white hover:border-indigo-100 transition-all flex items-center justify-center">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </td>
                </tr>

                <!-- Lite Detailed Inspector -->
                <tr id="audit-{{ $audit->id }}" class="hidden bg-slate-50/30">
                    <td colspan="6" class="px-12 py-8">
                        <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-inner relative overflow-hidden">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                <i data-lucide="binary" class="w-4 h-4 text-indigo-500"></i>
                                Protocol State Delta
                            </h4>
                            
                            <div class="bg-slate-900 rounded-xl p-6 overflow-hidden">
                                <pre class="text-[11px] text-emerald-400 font-mono overflow-x-auto leading-relaxed">{{ json_encode($audit->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </div>

                            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
                                <div class="flex flex-col">
                                    <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest">Registry Hash</span>
                                    <span class="text-[9px] font-mono text-slate-400 italic">{{ hash('sha1', $audit->id . $audit->created_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-8 py-32 text-center text-slate-400">
                        <i data-lucide="shield-off" class="w-12 h-12 mx-auto mb-4 opacity-10"></i>
                        <p class="text-xs font-black uppercase tracking-widest">Registry sanitized (Zero Events)</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($audits->hasPages())
    <div class="px-8 py-5 bg-slate-50 border-t border-slate-100">
        {{ $audits->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
    function toggleAudit(id) {
        const mesh = document.getElementById(`audit-${id}`);
        mesh.classList.toggle('hidden');
    }
</script>
@endpush

@endsection
