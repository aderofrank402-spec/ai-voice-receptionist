<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Billing & Subscription</h1>
                <p class="text-slate-500 mt-1">Manage your plan and payment methods.</p>
            </div>
            <a href="{{ route('dashboard') }}"
                class="text-sm text-slate-500 hover:text-slate-900 font-medium flex items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto py-8">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Starter (Solo) - Active -->
            <div
                class="bg-white rounded-2xl shadow-sm border-2 border-indigo-500 p-6 relative overflow-hidden flex flex-col">
                <div
                    class="absolute top-0 right-0 bg-indigo-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-lg">
                    CURRENT PLAN</div>
                <h2 class="text-lg font-bold text-slate-900 mb-2">Starter (Solo)</h2>
                <div class="flex items-baseline gap-1 mb-6">
                    <span class="text-3xl font-extrabold text-slate-900">2,999</span>
                    <span class="text-lg font-bold text-slate-900">KES</span>
                    <span class="text-slate-500 text-sm">/mo</span>
                </div>

                <ul class="space-y-3 mb-8 flex-1">
                    <li class="flex items-start gap-3 text-sm text-slate-600">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0"></i> 60 AI Minutes /
                        month
                    </li>
                    <li class="flex items-start gap-3 text-sm text-slate-600">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0"></i> 15 KES / min
                        overage
                    </li>
                    <li class="flex items-start gap-3 text-sm text-slate-600">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0"></i> 1 Local Number
                        Included
                    </li>
                    <li class="flex items-start gap-3 text-sm text-slate-600">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0"></i> SMS Summaries
                    </li>
                </ul>

                <button disabled
                    class="w-full py-2.5 bg-slate-100 text-slate-500 font-semibold rounded-xl cursor-default text-sm">
                    Plan Active
                </button>
            </div>

            <!-- Growth (SME) -->
            <div
                class="bg-zinc-900 p-6 rounded-2xl border border-zinc-800 shadow-xl relative overflow-hidden flex flex-col transform md:-translate-y-2">
                <div
                    class="absolute top-0 right-0 w-[200px] h-[200px] bg-emerald-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2">
                </div>
                <div
                    class="absolute top-4 right-4 bg-emerald-500 text-white text-[10px] uppercase font-bold px-2 py-0.5 rounded-full shadow-lg shadow-emerald-500/20">
                    Most Popular
                </div>

                <h2 class="text-lg font-bold text-white mb-2 relative z-10">Growth (SME)</h2>
                <div class="flex items-baseline gap-1 mb-6 relative z-10">
                    <span class="text-3xl font-extrabold text-white">9,999</span>
                    <span class="text-lg font-bold text-white">KES</span>
                    <span class="text-zinc-400 text-sm">/mo</span>
                </div>

                <ul class="space-y-3 mb-8 flex-1 relative z-10">
                    <li class="flex items-start gap-3 text-sm text-zinc-300">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 mt-0.5 shrink-0"></i> 300 AI Minutes /
                        month
                    </li>
                    <li class="flex items-start gap-3 text-sm text-zinc-300">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 mt-0.5 shrink-0"></i> 10 KES / min
                        overage
                    </li>
                    <li class="flex items-start gap-3 text-sm text-zinc-300">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 mt-0.5 shrink-0"></i> Calendar Sync & CRM
                    </li>
                    <li class="flex items-start gap-3 text-sm text-zinc-300">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 mt-0.5 shrink-0"></i> Prioritized Support
                    </li>
                </ul>

                <button
                    class="w-full py-2.5 bg-emerald-500 text-white font-bold rounded-xl hover:bg-emerald-400 transition-all shadow-lg shadow-emerald-500/20 relative z-10 text-sm">
                    Upgrade to Growth
                </button>
            </div>

            <!-- Enterprise -->
            <div
                class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden flex flex-col group hover:border-slate-300 transition-colors">
                <h2 class="text-lg font-bold text-slate-900 mb-2">Enterprise</h2>
                <div class="flex items-baseline gap-1 mb-6">
                    <span class="text-3xl font-extrabold text-slate-900">Custom</span>
                </div>

                <ul class="space-y-3 mb-8 flex-1">
                    <li class="flex items-start gap-3 text-sm text-slate-600">
                        <i data-lucide="check" class="w-4 h-4 text-slate-900 mt-0.5 shrink-0"></i> Unlimited Agents &
                        Minutes
                    </li>
                    <li class="flex items-start gap-3 text-sm text-slate-600">
                        <i data-lucide="check" class="w-4 h-4 text-slate-900 mt-0.5 shrink-0"></i> Custom API
                        Integration
                    </li>
                    <li class="flex items-start gap-3 text-sm text-slate-600">
                        <i data-lucide="check" class="w-4 h-4 text-slate-900 mt-0.5 shrink-0"></i> Multiple Phone
                        Numbers
                    </li>
                    <li class="flex items-start gap-3 text-sm text-slate-600">
                        <i data-lucide="check" class="w-4 h-4 text-slate-900 mt-0.5 shrink-0"></i> Dedicated Account
                        Manager
                    </li>
                </ul>

                <button
                    class="w-full py-2.5 border border-slate-200 bg-white text-slate-900 font-bold rounded-xl hover:bg-slate-50 transition-all text-sm">
                    Contact Sales
                </button>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="mt-12">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Payment Methods</h3>
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-8 bg-slate-100 rounded border border-slate-200 flex items-center justify-center">
                        <span class="text-[10px] font-bold text-slate-400">VISA</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-900">•••• •••• •••• 4242</p>
                        <p class="text-xs text-slate-500">Expires 12/28</p>
                    </div>
                </div>
                <button
                    class="text-sm font-medium text-slate-600 hover:text-slate-900 border border-slate-200 px-3 py-1.5 rounded-lg hover:bg-slate-50 transition-colors">
                    Edit
                </button>
            </div>
        </div>

    </div>
</x-app-layout>