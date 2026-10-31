@extends('admin.layout')

@section('title', 'Call Logs')

@section('content')
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">System Throughput</h1>
            <p class="text-slate-500 mt-1 font-medium italic">Comprehensive audit stream of all platform-wide interactions.</p>
        </div>
        <a href="{{ route('admin.export.calls') }}"
            class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-slate-800 transition-all shadow-sm">
            <i data-lucide="download" class="w-4 h-4 mr-2 inline"></i>
            Export Logs
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden mb-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-extrabold text-[10px] border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-5">Node Identity</th>
                        <th class="px-8 py-5">Agent Unit</th>
                        <th class="px-8 py-5">Tenant</th>
                        <th class="px-8 py-5">Duration</th>
                        <th class="px-8 py-5">Outcome</th>
                        <th class="px-8 py-5">Timestamp</th>
                        <th class="px-8 py-5 text-right">Audit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($calls as $call)
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-8 py-5">
                                <span class="font-mono text-[11px] font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded border border-slate-100">{{ substr($call->call_id, 0, 12) }}...</span>
                            </td>
                            <td class="px-8 py-5 text-slate-900 font-bold text-xs">{{ $call->agent->name ?? 'N/A' }}</td>
                            <td class="px-8 py-5 text-slate-500 font-medium text-xs">{{ $call->agent->user->name ?? 'N/A' }}</td>
                            <td class="px-8 py-5 text-slate-500 font-bold text-xs">{{ $call->duration }}s</td>
                            <td class="px-8 py-5">
                                <span class="px-2 py-1 rounded-md text-[9px] font-black uppercase tracking-tighter {{ str_contains(strtolower($call->outcome ?? ''), 'success') ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-slate-50 text-slate-500 border border-slate-100' }}">
                                    {{ $call->outcome ?? 'Processing' }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-slate-400 text-[10px] font-bold">{{ $call->created_at->format('M d, H:i') }}</td>
                            <td class="px-8 py-5 text-right">
                                @php
                                    $hasTranscript = !empty($call->transcript);
                                    $summary = $call->metadata['summary'] ?? null;
                                @endphp
                                @if($hasTranscript || $summary)
                                    <button @click="activeTranscript = {{ json_encode($call->transcript ?? []) }}; activeSummary = {{ json_encode($call->metadata['summary'] ?? null) }}; showTranscript = true" 
                                            class="p-2 bg-slate-50 text-slate-400 hover:text-indigo-600 hover:bg-slate-100 rounded-lg border border-slate-100 transition-all"
                                            title="View Transcript">
                                        <i data-lucide="message-square" class="w-4 h-4"></i>
                                    </button>
                                @else
                                    <span class="text-slate-200">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-8 py-16 text-center text-slate-400 text-xs font-medium italic">No calls detected in global registry.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($calls->hasPages())
        <div class="px-8 py-5 bg-slate-50 border-t border-slate-100">
            {{ $calls->links() }}
        </div>
        @endif
    </div>

    <!-- Transcript Modal (Lite Redesign) -->
    <div x-show="showTranscript" 
         class="fixed inset-0 z-[60] overflow-y-auto" 
         x-cloak
         @keydown.escape.window="showTranscript = false">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showTranscript" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm" 
                 @click="showTranscript = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="showTranscript" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block w-full max-w-xl overflow-hidden text-left align-bottom transition-all transform bg-white rounded-3xl shadow-2xl sm:my-8 sm:align-middle border border-slate-200">
                
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl border border-indigo-100">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Conversation Audit</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Enterprise verification stream</p>
                        </div>
                    </div>
                    <button @click="showTranscript = false" class="text-slate-400 hover:text-slate-900 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-8 max-h-[60vh] overflow-y-auto space-y-8 custom-scrollbar">
                    <!-- AI Insight Section -->
                    <template x-if="activeSummary">
                        <div class="mb-4">
                            <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-600 mb-3 ml-1">AI Node Synthesis</h4>
                            <div class="p-5 bg-indigo-50/30 border border-indigo-100 rounded-2xl text-slate-700 text-sm leading-relaxed font-medium italic" 
                                 x-text="activeSummary"></div>
                        </div>
                    </template>

                    <div class="space-y-6">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-3 ml-1">Event Logs</h4>
                        <template x-for="(msg, index) in activeTranscript" :key="index">
                        <div :class="msg.role === 'user' ? 'flex flex-col items-start' : 'flex flex-col items-end'">
                            <div :class="msg.role === 'user' ? 'bg-slate-50 border border-slate-100 text-slate-700' : 'bg-slate-900 text-white shadow-lg shadow-slate-200'"
                                 class="max-w-[90%] px-5 py-3 rounded-2xl text-sm leading-relaxed font-medium">
                                <p x-text="msg.content"></p>
                            </div>
                            <span class="text-[9px] uppercase font-black tracking-widest text-slate-400 mt-2 px-1" 
                                  x-text="msg.role === 'user' ? 'Direct Input' : 'AI Protocol'"></span>
                        </div>
                    </template>
                </div>

                <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button @click="showTranscript = false" 
                            class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-100 transition-all text-xs uppercase tracking-widest shadow-sm">
                        Close Audit
                    </button>
                    <button class="px-5 py-2.5 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition-all text-xs uppercase tracking-widest shadow-sm">
                        Confirm Logs
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection