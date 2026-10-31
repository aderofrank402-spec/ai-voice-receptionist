<div class="space-y-8">
    <!-- TOP METRICS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="p-8 bg-slate-900 rounded-[2rem] text-white shadow-xl relative overflow-hidden group">
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-indigo-500/20 rounded-full blur-3xl group-hover:bg-indigo-500/30 transition-colors"></div>
            <div class="relative z-10">
                <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">Total Processing Time</p>
                <h4 class="text-3xl font-black tracking-tight">{{ number_format($usageStats['total_minutes'], 1) }} <span class="text-xs uppercase text-slate-500">Mins</span></h4>
            </div>
        </div>

        <div class="p-8 bg-white border border-slate-200 rounded-[2rem] shadow-sm flex flex-col justify-center">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Investment</p>
            <h4 class="text-2xl font-black text-slate-900 tracking-tight">${{ number_format($usageStats['total_cost'], 2) }} <span class="text-[10px] uppercase text-slate-400 font-bold">USD</span></h4>
        </div>

        <div class="p-8 bg-white border border-slate-200 rounded-[2rem] shadow-sm flex flex-col justify-center">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Conversation Count</p>
            <h4 class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($usageStats['call_count']) }} <span class="text-[10px] uppercase text-slate-400 font-bold">Calls</span></h4>
        </div>
    </div>

    <!-- USAGE LIMITS BAR -->
    <div class="bg-slate-50 border border-slate-100 rounded-[2.5rem] p-8 md:p-10">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h4 class="font-black text-slate-900 uppercase tracking-tight">Monthly Subscription Cap</h4>
                <p class="text-[10px] text-slate-500 font-medium italic">Based on your current Startup Plan (1,000 mins/mo)</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-black text-slate-900">{{ round(($usageStats['total_minutes'] / 1000) * 100, 1) }}%</span>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Utilized</p>
            </div>
        </div>

        <div class="h-4 bg-slate-200 rounded-full overflow-hidden shadow-inner">
            <div class="h-full bg-slate-900 rounded-full transition-all duration-1000" style="width: {{ min(($usageStats['total_minutes'] / 1000) * 100, 100) }}%"></div>
        </div>

        <div class="flex justify-between mt-4">
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">0 Mins</span>
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">1,000 Mins Cap</span>
        </div>
    </div>

    <!-- PRO TIP -->
    <div class="flex items-start gap-4 p-6 bg-indigo-50 border border-indigo-100 rounded-3xl">
        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100 flex-shrink-0">
            <i data-lucide="zap" class="w-5 h-5"></i>
        </div>
        <div>
            <h5 class="text-[10px] font-black text-indigo-900 uppercase tracking-widest mb-1">Optimization Insight</h5>
            <p class="text-[10px] text-slate-600 leading-relaxed font-medium">Your average cost per call is <span class="text-indigo-600 font-bold">${{ $usageStats['call_count'] > 0 ? number_format($usageStats['total_cost'] / $usageStats['call_count'], 3) : '0.00' }}</span>. Consider lowering LLM temperature to reduce token latency and costs in your Agent settings.</p>
        </div>
    </div>
</div>
