<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 leading-tight">
                    Call Archive
                </h2>
                <p class="text-sm text-slate-500 mt-1">Audit interaction history and track AI performance with precision.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('export.calls') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50 transition-all shadow-sm">
                    <i data-lucide="download" class="w-4 h-4 mr-2 text-slate-400"></i>
                    Export History
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ 
        showInsights: false, 
        activeCall: null,
        openInsights(call) {
            this.activeCall = call;
            this.showInsights = true;
        },
        getSentimentColor(sentiment) {
            const colors = {
                'Satisfied': 'text-emerald-600 bg-emerald-50 border-emerald-100',
                'Frustrated': 'text-red-600 bg-red-50 border-red-100',
                'Neutral': 'text-slate-600 bg-slate-50 border-slate-100',
                'Inquisitive': 'text-indigo-600 bg-indigo-50 border-indigo-100'
            };
            return colors[sentiment] || 'text-slate-600 bg-slate-50 border-slate-100';
        }
    }">
        
        <!-- Archive Velocity Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Today's Volume -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-indigo-500/30 transition-all" style="min-height: 130px;">
                <!-- ROOT FIX: Absolute inline style for watermark -->
                <div style="position: absolute; right: -10px; bottom: -10px; opacity: 0.05; pointer-events: none; z-index: 0;">
                    <i data-lucide="phone-incoming" style="width: 80px; height: 80px; color: #000;"></i>
                </div>
                <div style="position: relative; z-index: 10;">
                    <h4 class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Calls Today</h4>
                    <div class="text-3xl font-black text-slate-900">{{ $stats['volume_today'] }}</div>
                    <div class="mt-2 text-[10px] text-emerald-600 font-bold uppercase tracking-tighter">Real-time Activity</div>
                </div>
            </div>

            <!-- 2. Total Time Saved -->
            <div class="bg-indigo-600 p-6 rounded-3xl shadow-xl shadow-indigo-100 relative overflow-hidden group" style="min-height: 130px;">
                <!-- ROOT FIX: Absolute inline style for watermark -->
                <div style="position: absolute; right: -10px; bottom: -10px; opacity: 0.1; pointer-events: none; z-index: 0;">
                    <i data-lucide="clock" style="width: 80px; height: 80px; color: #fff;"></i>
                </div>
                <div style="position: relative; z-index: 10;">
                    <h4 class="text-[10px] font-bold uppercase tracking-widest text-indigo-200 mb-2">Total Time Saved</h4>
                    <div class="text-3xl font-black text-white">{{ $stats['total_time_saved'] }}</div>
                    <div class="mt-2 text-[10px] text-indigo-100 font-medium uppercase tracking-tighter">Human labor replaced</div>
                </div>
            </div>

            <!-- 3. Avg Quality -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-indigo-500/30 transition-all" style="min-height: 130px;">
                <div style="position: absolute; right: -10px; bottom: -10px; opacity: 0.03; pointer-events: none; z-index: 0;">
                    <i data-lucide="trending-up" style="width: 80px; height: 80px; color: #000;"></i>
                </div>
                <div style="position: relative; z-index: 10;">
                    <h4 class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Avg Lead Quality</h4>
                    <div class="flex items-center gap-3">
                        <div class="text-3xl font-black text-slate-900">{{ $stats['avg_lead_score'] }}</div>
                        <div class="flex items-center text-amber-400">
                            <i data-lucide="star" class="w-5 h-5 fill-amber-400"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-[10px] text-slate-400 font-medium uppercase tracking-tighter">Out of 5.0 Rating</div>
                </div>
            </div>

            <!-- 4. Success Rate -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-indigo-500/30 transition-all" style="min-height: 130px;">
                <div style="position: absolute; right: -10px; bottom: -10px; opacity: 0.03; pointer-events: none; z-index: 0;">
                    <i data-lucide="check-circle" style="width: 80px; height: 80px; color: #000;"></i>
                </div>
                <div style="position: relative; z-index: 10;">
                    <h4 class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">Completion Rate</h4>
                    <div class="text-3xl font-black text-slate-900">{{ $stats['success_rate'] }}</div>
                    <div class="mt-2 text-[10px] text-indigo-500 font-bold uppercase tracking-tighter">Goal Achievement</div>
                </div>
            </div>
        </div>

        <!-- Search & Filter Bar -->
        <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-200">
            <form action="{{ route('calls.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search by caller phone number..." 
                           class="w-full block pl-11 pr-4 py-3.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
                </div>
                
                <div class="w-full md:w-48">
                    <select name="outcome" 
                            class="block w-full px-4 py-3.5 bg-slate-50/50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                        <option value="">All Outcomes</option>
                        <option value="Handled" {{ request('outcome') == 'Handled' ? 'selected' : '' }}>Handled</option>
                        <option value="Booked" {{ request('outcome') == 'Booked' ? 'selected' : '' }}>Booked</option>
                        <option value="Confirmed" {{ request('outcome') == 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="Failed" {{ request('outcome') == 'Failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-8 py-3.5 bg-indigo-600 text-white text-sm font-bold rounded-2xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2">
                        Apply Filters
                    </button>
                    @if(request()->anyFilled(['search', 'outcome']))
                        <a href="{{ route('calls.index') }}" class="px-4 py-3.5 bg-slate-100 text-slate-600 text-sm font-bold rounded-2xl hover:bg-slate-200 transition-all text-center flex items-center justify-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Call Table -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold text-[10px] border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-5">Result & Intelligence</th>
                            <th class="px-6 py-5">Caller Identity</th>
                            <th class="px-6 py-5 text-center">Score</th>
                            <th class="px-6 py-5 text-center">Duration</th>
                            <th class="px-6 py-5">Timestamp</th>
                            <th class="px-6 py-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($calls as $call)
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-5">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full" :class="'{{ $call['status'] }}' === 'success' ? 'bg-emerald-500' : ('{{ $call['status'] }}' === 'missed' ? 'bg-red-500' : 'bg-amber-500')"></div>
                                            <span class="font-bold text-slate-900">{{ $call['outcome'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span :class="getSentimentColor('{{ $call['sentiment'] }}')" class="px-2 py-0.5 rounded-full text-[9px] font-bold border uppercase tracking-wider">
                                                {{ $call['sentiment'] }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200 uppercase tracking-wider">
                                                {{ $call['intent'] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors shadow-sm">
                                            <i data-lucide="user" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-sm">{{ $call['caller_name'] ?? 'Inbound Caller' }}</div>
                                            <div class="text-slate-500 text-xs font-mono mt-0.5">{{ $call['caller'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex justify-center flex-col items-center gap-1">
                                        <div class="flex gap-0.5">
                                            @for($i=1; $i<=5; $i++)
                                                <i data-lucide="star" class="w-3 h-3 {{ $i <= ($call['lead_score'] ?? 5) ? 'text-amber-400 fill-amber-400' : 'text-slate-200' }}"></i>
                                            @endfor
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Quality</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="font-bold text-slate-700">{{ $call['duration'] }}</div>
                                    <div class="text-[9px] font-bold text-indigo-500 uppercase tracking-tighter">ROI: +{{ $call['time_saved'] }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-slate-900 font-bold text-xs">{{ explode(' ', $call['date'])[0] }} {{ explode(' ', $call['date'])[1] }}</div>
                                    <div class="text-slate-400 text-[10px] mt-0.5 tracking-wider uppercase font-medium">{{ explode(' ', $call['date'])[2] ?? '' }}</div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <button @click='openInsights(@json($call))' 
                                            class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all shadow-md shadow-indigo-100 active:scale-95">
                                        <i data-lucide="layout-panel-left" class="w-3.5 h-3.5"></i>
                                        Review
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-24 text-center">
                                    <div class="flex flex-col items-center max-w-sm mx-auto text-slate-400">
                                        <i data-lucide="search-x" class="w-12 h-12 mb-4"></i>
                                        <h3 class="text-slate-900 font-bold text-lg">No calls found</h3>
                                        <p class="mt-2 text-sm">Adjust your filters or wait for new interactions.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- ROOT FIX: Restored Pagination -->
            @if($calls->hasPages())
                <div class="px-6 py-6 bg-slate-50/50 border-t border-slate-100">
                    {{ $calls->links() }}
                </div>
            @endif
        </div>
        
        <!-- MODERNIZED CALL REVIEW SIDE PANEL -->
        <div x-show="showInsights" 
             x-init="$watch('showInsights', value => { if(value) { setTimeout(() => lucide.createIcons(), 50); } })"
             class="fixed inset-0 z-50 overflow-hidden" 
             x-cloak>
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" 
                 x-show="showInsights"
                 x-transition.opacity
                 @click="showInsights = false"></div>

            <div class="fixed inset-y-0 right-0 max-w-2xl w-full flex">
                <div class="relative w-screen max-w-2xl"
                     x-show="showInsights"
                     x-transition:enter="transform transition ease-in-out duration-500"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-500"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full">
                    
                    <div class="h-full flex flex-col bg-slate-50 shadow-2xl relative">
                        <!-- Panel Header -->
                        <div class="bg-white px-8 py-8 border-b border-slate-200 sticky top-0 z-20">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-5">
                                    <div class="w-14 h-14 bg-indigo-600 text-white rounded-2xl shadow-xl shadow-indigo-100 flex items-center justify-center">
                                        <i data-lucide="bot-message-square" class="w-7 h-7"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-black text-slate-900 tracking-tight" x-text="activeCall?.caller_name || 'Inbound Call'"></h3>
                                        <div class="flex items-center gap-2 mt-1.5">
                                            <span :class="getSentimentColor(activeCall?.sentiment)" class="px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider" x-text="activeCall?.sentiment"></span>
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span class="text-xs text-slate-500 font-bold font-mono" x-text="activeCall?.caller"></span>
                                        </div>
                                    </div>
                                </div>
                                <button @click="showInsights = false" class="p-3 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-2xl transition-all">
                                    <i data-lucide="x" class="w-6 h-6"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Panel Body -->
                        <div class="flex-1 overflow-y-auto p-8 space-y-8 min-h-0">
                            
                            <!-- 1. AUDIO PLAYER SECTION -->
                            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm overflow-hidden relative group">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                                        <i data-lucide="mic-2" class="w-3 h-3"></i>
                                        Call Recording
                                    </h4>
                                    <span class="text-[10px] font-bold text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded-lg border border-indigo-100" x-text="activeCall?.duration + ' TOTAL'"></span>
                                </div>
                                
                                <template x-if="activeCall?.recording_url">
                                    <audio controls class="w-full h-10 rounded-lg bg-slate-50">
                                        <source :src="activeCall.recording_url" type="audio/mpeg">
                                    </audio>
                                </template>
                            </div>

                            <!-- 2. SaaS VALUE & ROI CARD -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-indigo-600 p-6 rounded-3xl shadow-xl shadow-indigo-100 relative overflow-hidden group">
                                    <!-- ROOT FIX: Absolute inline style for watermark -->
                                    <div style="position: absolute; right: -15px; bottom: -15px; width: 100px; height: 100px; opacity: 0.05; pointer-events: none; z-index: 0;">
                                        <i data-lucide="clock" style="width: 100%; height: 100%; color: white;"></i>
                                    </div>
                                    <div style="position: relative; z-index: 10;">
                                        <h4 class="text-[10px] font-bold uppercase tracking-widest text-indigo-200 mb-1">Time Saved</h4>
                                        <div class="text-3xl font-black text-white" x-text="activeCall?.time_saved"></div>
                                        <div class="mt-2 text-[10px] text-indigo-100 font-medium uppercase mt-2">Human interaction replaced</div>
                                    </div>
                                </div>
                                
                                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden">
                                    <h4 class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Lead Quality</h4>
                                    <div class="flex items-center gap-1">
                                        <template x-for="i in 5">
                                            <i data-lucide="star" class="w-5 h-5" :class="i <= (activeCall?.lead_score || 5) ? 'text-amber-400 fill-amber-400' : 'text-slate-100'"></i>
                                        </template>
                                    </div>
                                    <div class="mt-2 text-[10px] font-bold text-slate-500 uppercase" x-text="(activeCall?.lead_score >= 4 ? 'High Priority' : 'Needs Review')"></div>
                                </div>
                            </div>

                            <!-- 3. TRANSCRIPT VIEWER -->
                            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm flex flex-col h-[400px]">
                                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Interaction Transcript</h4>
                                    <div class="flex items-center gap-2 text-[10px] text-emerald-500 font-bold uppercase bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-100">
                                        <i data-lucide="shield-check" class="w-3 h-3"></i>
                                        PII Redacted
                                    </div>
                                </div>
                                <div class="flex-1 overflow-y-auto p-6 space-y-6">
                                    <template x-if="activeCall?.transcript && activeCall.transcript.length > 0">
                                        <div class="space-y-6">
                                            <template x-for="msg in activeCall.transcript">
                                                <div class="flex flex-col" 
                                                     x-show="msg.text || (msg.content && msg.content.trim() !== '')"
                                                     :class="msg.role === 'assistant' ? 'items-start' : 'items-end'">
                                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 px-1" x-text="msg.role"></span>
                                                    <div class="max-w-[85%] p-4 rounded-2xl text-sm leading-relaxed" 
                                                         :class="msg.role === 'assistant' 
                                                            ? 'bg-slate-50 text-slate-700 border border-slate-100 rounded-bl-none' 
                                                            : 'bg-indigo-600 text-white rounded-br-none shadow-sm'">
                                                        <span x-text="msg.text || msg.content"></span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- 4. EXECUTIVE SUMMARY -->
                            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm relative" style="padding: 50px 32px 32px 32px; min-height: 140px;">
                                <div style="position: absolute; top: -14px; left: 32px; background: white; padding: 6px 16px; border-radius: 99px; border: 1px solid #e2e8f0; font-size: 10px; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; box-shadow: 0 1px 2px rgba(0,0,0,0.05); z-index: 20;">
                                    Pro Summary
                                </div>
                                <p class="text-slate-800 text-lg leading-relaxed font-bold italic" x-text="'“' + (activeCall?.summary_text || 'Select a call to view summary') + '”'"></p>
                            </div>

                            <!-- 5. ACTION ITEMS -->
                            <div class="bg-slate-900 p-8 rounded-3xl shadow-2xl">
                                <div class="flex items-center gap-2 mb-8">
                                    <div class="p-1.5 bg-indigo-500/20 text-indigo-300 rounded-lg">
                                        <i data-lucide="zap" class="w-4 h-4 text-indigo-400"></i>
                                    </div>
                                    <h4 class="text-xs font-bold uppercase tracking-widest text-indigo-300">Next Recommended Steps</h4>
                                </div>

                                <div class="space-y-4">
                                    <template x-if="activeCall?.action_items && activeCall.action_items.length > 0">
                                        <template x-for="item in activeCall.action_items">
                                            <div class="flex items-start gap-4 p-5 bg-white/5 rounded-2xl border border-white/5">
                                                <i data-lucide="check" class="w-3 h-3 text-indigo-400 mt-1 shrink-0"></i>
                                                <span class="text-slate-200 text-sm font-medium leading-snug" x-text="item"></span>
                                            </div>
                                        </template>
                                    </template>
                                </div>
                            </div>

                            <!-- 6. SUPER ADMIN AUDIT -->
                            @if($isAdmin)
                            <div class="bg-red-50/50 p-8 rounded-3xl border border-red-100 relative overflow-hidden">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-red-500 mb-6 underline decoration-red-200 decoration-4 underline-offset-4">Super Admin: Financial Audit</h4>
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total USD</div>
                                        <div class="text-xl font-black text-slate-900" x-text="'$' + parseFloat(activeCall?.costs?.total_usd || 0).toFixed(4)"></div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total KES</div>
                                        <div class="text-xl font-black text-slate-900" x-text="'KES ' + parseFloat(activeCall?.costs?.total_kes || 0).toFixed(2)"></div>
                                    </div>
                                </div>
                                <div class="mt-8 pt-6 border-t border-red-100 grid grid-cols-4 gap-2">
                                    <div class="flex flex-col"><span class="text-[8px] font-bold text-red-400 uppercase">STT</span><span class="text-xs font-bold text-slate-600" x-text="'$' + parseFloat(activeCall?.costs?.stt || 0).toFixed(4)"></span></div>
                                    <div class="flex flex-col"><span class="text-[8px] font-bold text-red-400 uppercase">TTS</span><span class="text-xs font-bold text-slate-600" x-text="'$' + parseFloat(activeCall?.costs?.tts || 0).toFixed(4)"></span></div>
                                    <div class="flex flex-col"><span class="text-[8px] font-bold text-red-400 uppercase">LLM</span><span class="text-xs font-bold text-slate-600" x-text="'$' + parseFloat(activeCall?.costs?.llm || 0).toFixed(4)"></span></div>
                                    <div class="flex flex-col"><span class="text-[8px] font-bold text-red-400 uppercase">TEL</span><span class="text-xs font-bold text-slate-600" x-text="'$' + parseFloat(activeCall?.costs?.telephony || 0).toFixed(4)"></span></div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Panel Footer -->
                        <div class="bg-white px-8 py-6 border-t border-slate-200 flex items-center justify-between">
                             <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                                <i data-lucide="lock" class="w-3 h-3 inline mr-1"></i>Secure Backend
                             </div>
                             <a :href="'{{ route('export.calls') }}?id=' + activeCall?.id" class="text-indigo-600 text-xs font-black uppercase tracking-widest">
                                Download Audit
                             </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>