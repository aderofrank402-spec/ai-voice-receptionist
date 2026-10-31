<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div
                    class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                    <i data-lucide="cpu" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Identity Studio</h1>
                    <p
                        class="text-[10px] text-slate-500 mt-1 font-bold uppercase tracking-[0.15em] flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Neural Engine Active
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('agent.test') }}"
                    class="group px-6 py-3 bg-white border border-slate-200 text-slate-900 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                    <i data-lucide="play-circle"
                        class="w-4 h-4 text-indigo-600 group-hover:scale-110 transition-transform"></i>
                    Simulate Agent
                </a>
                <button type="submit" form="agent-config-form"
                    class="px-8 py-3 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-xl shadow-slate-200 flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Deploy Changes
                </button>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 min-w-0" x-data="{ 
        activeTab: 'persona',
        tone: '{{ $agent->configuration['persona']['tone'] ?? 'polite_admin' }}',
        agentName: '{{ $agent->configuration['persona']['agent_name'] ?? 'Sarah' }}',
        language: '{{ $agent->configuration['persona']['language'] ?? 'en' }}'
    }">
        <!-- IDENTITY OVERVIEW BANNER (Premium Glassmorphism) -->
        <div class="bg-slate-900 rounded-[3rem] p-10 mb-10 text-white shadow-2xl overflow-hidden relative group">
            <!-- Decorative Orbs -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/20 rounded-full blur-[100px] animate-pulse">
            </div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-emerald-500/10 rounded-full blur-[80px]"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-10">
                <div class="flex items-center gap-8">
                    <div class="relative">
                        <div
                            class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl flex items-center justify-center text-4xl shadow-2xl border border-white/20 rotate-3 group-hover:rotate-0 transition-transform duration-500">
                            🤖
                        </div>
                        <div
                            class="absolute -bottom-2 -right-2 w-8 h-8 bg-emerald-500 rounded-full border-4 border-slate-900 flex items-center justify-center">
                            <i data-lucide="zap" class="w-4 h-4 text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-4 mb-2">
                            <h2 class="text-3xl font-black uppercase tracking-tighter" x-text="agentName || 'Sarah'">
                            </h2>
                            <span
                                class="px-3 py-1 bg-white/10 text-indigo-300 text-[9px] font-black uppercase tracking-[0.2em] rounded-full border border-white/10 backdrop-blur-md">Core
                                Version 2.4</span>
                        </div>
                        <p
                            class="text-[11px] text-slate-400 font-medium uppercase tracking-[0.25em] flex items-center gap-2">
                            Status: <span class="text-emerald-400">Optimized for Conversion</span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <div
                        class="bg-white/5 backdrop-blur-xl rounded-[1.5rem] px-8 py-5 border border-white/10 hover:bg-white/10 transition-all group-hover:translate-y-[-2px]">
                        <label
                            class="block text-[8px] font-black text-slate-500 uppercase tracking-[0.3em] mb-2 font-mono">Linguistic
                            Model</label>
                        <p class="text-sm font-black flex items-center gap-2"
                            x-text="language === 'en' ? 'Natural Kenyan (EN)' : (language === 'sw' ? 'Sanifu Swahili' : 'Urban Nairobi Sheng')">
                        </p>
                    </div>
                    <div
                        class="bg-white/5 backdrop-blur-xl rounded-[1.5rem] px-8 py-5 border border-white/10 hover:bg-white/10 transition-all group-hover:translate-y-[-2px]">
                        <label
                            class="block text-[8px] font-black text-slate-500 uppercase tracking-[0.3em] mb-2 font-mono">Cognitive
                            Tone</label>
                        <p class="text-sm font-black uppercase tracking-tight flex items-center gap-2">
                            <i :data-lucide="tone === 'polite_admin' ? 'award' : 'zap'"
                                class="w-4 h-4 text-indigo-400"></i>
                            <span x-text="tone.replace('_', ' ')"></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN WORKSPACE -->
        <div class="w-full min-w-0">
            <!-- TAB NAVIGATION (The Neumorphic Bar) -->
            <div
                class="bg-slate-100/50 backdrop-blur-md border border-slate-200 rounded-[2rem] p-2 flex gap-2 shadow-inner mb-10 overflow-x-auto min-w-0">
                <button type="button" @click="activeTab = 'persona'"
                    :class="activeTab === 'persona' ? 'bg-white text-slate-900 shadow-xl shadow-slate-200 border-slate-200' : 'text-slate-500 hover:bg-white/50 border-transparent'"
                    class="flex-1 min-w-[120px] py-4 px-6 rounded-[1.5rem] border text-[11px] font-black uppercase tracking-[0.2em] transition-all duration-500 flex items-center justify-center gap-3">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    Persona
                </button>
                <button type="button" @click="activeTab = 'knowledge'"
                    :class="activeTab === 'knowledge' ? 'bg-white text-slate-900 shadow-xl shadow-slate-200 border-slate-200' : 'text-slate-500 hover:bg-white/50 border-transparent'"
                    class="flex-1 min-w-[120px] py-4 px-6 rounded-[1.5rem] border text-[11px] font-black uppercase tracking-[0.2em] transition-all duration-500 flex items-center justify-center gap-3">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                    Knowledge
                </button>
                <button type="button" @click="activeTab = 'intelligence'"
                    :class="activeTab === 'intelligence' ? 'bg-white text-slate-900 shadow-xl shadow-slate-200 border-slate-200' : 'text-slate-500 hover:bg-white/50 border-transparent'"
                    class="flex-1 min-w-[120px] py-4 px-6 rounded-[1.5rem] border text-[11px] font-black uppercase tracking-[0.2em] transition-all duration-500 flex items-center justify-center gap-3">
                    <i data-lucide="brain-cog" class="w-4 h-4"></i>
                    Intelligence
                </button>
            </div>

            <form action="{{ route('agent.update') }}" method="POST" id="agent-config-form" class="space-y-10 min-w-0">
                @csrf

                <!-- 1. PERSONA TAB -->
                <div x-show="activeTab === 'persona'" x-cloak x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-y-10"
                    x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8 min-w-0">
                    <div
                        class="bg-white rounded-[3rem] border border-slate-200 shadow-sm p-10 md:p-12 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-slate-50 rounded-full -mr-32 -mt-32"></div>

                        <div class="relative z-10">
                            <div class="mb-12">
                                <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Vocal Signature
                                </h3>
                                <p class="text-xs text-slate-500 mt-2 font-medium italic">Define how your agent greets
                                    and speaks to the world.</p>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                                <div>
                                    <label
                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 ml-1">Assigned
                                        Name</label>
                                    <input type="text" name="agent_name" x-model="agentName"
                                        class="w-full px-6 py-5 bg-slate-50 border-2 border-slate-100 rounded-2xl text-sm font-bold focus:ring-slate-900 focus:border-slate-900 transition-all"
                                        placeholder="e.g. Sarah, David">
                                </div>
                                <div>
                                    <label
                                        class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 ml-1">Linguistic
                                        Context</label>
                                    <div class="relative group">
                                        <select name="language" x-model="language"
                                            class="w-full px-6 py-5 bg-slate-50 border-2 border-slate-100 rounded-2xl text-sm font-bold focus:ring-slate-900 appearance-none cursor-pointer transition-all">
                                            <option value="en">English (Region: Kenya/East Africa)</option>
                                            <option value="sw">Kiswahili (Region: Tanzania/Kenya)</option>
                                            <option value="sheng">Modern Sheng (Vibe: Nairobi Urban)</option>
                                        </select>
                                        <div
                                            class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                            <i data-lucide="chevron-down" class="w-5 h-5"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-10 border-t border-slate-100">
                                <h4
                                    class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-8 ml-1 text-center">
                                    Emotional Intelligence Preset</h4>
                                <input type="hidden" name="tone" :value="tone">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <button type="button" @click="tone = 'polite_admin'"
                                        :class="tone === 'polite_admin' ? 'border-slate-900 bg-slate-900 text-white ring-8 ring-slate-100' : 'bg-slate-50 border-slate-100 text-slate-500 hover:border-slate-200'"
                                        class="flex flex-col p-10 rounded-[2.5rem] border-2 text-left transition-all duration-500 group relative overflow-hidden">
                                        <div class="w-14 h-14 rounded-2xl mb-6 flex items-center justify-center shadow-lg"
                                            :class="tone === 'polite_admin' ? 'bg-white/10 text-white' : 'bg-white text-slate-900'">
                                            <i data-lucide="heart" class="w-6 h-6"></i>
                                        </div>
                                        <h4 class="font-black text-sm uppercase tracking-widest mb-3">Polite &
                                            Structured</h4>
                                        <p class="text-[11px] font-medium leading-relaxed opacity-70 italic mb-4">
                                            "Greetings. I am Sarah, how may I assist you today?"</p>
                                        <div class="flex items-center gap-2 mt-auto">
                                            <span
                                                class="px-2 py-1 bg-white/10 rounded-md text-[8px] font-bold uppercase tracking-widest">Medical</span>
                                            <span
                                                class="px-2 py-1 bg-white/10 rounded-md text-[8px] font-bold uppercase tracking-widest">Legal</span>
                                        </div>
                                    </button>

                                    <button type="button" @click="tone = 'direct_expert'"
                                        :class="tone === 'direct_expert' ? 'border-slate-900 bg-slate-900 text-white ring-8 ring-slate-100' : 'bg-slate-50 border-slate-100 text-slate-500 hover:border-slate-200'"
                                        class="flex flex-col p-10 rounded-[2.5rem] border-2 text-left transition-all duration-500 group relative overflow-hidden">
                                        <div class="w-14 h-14 rounded-2xl mb-6 flex items-center justify-center shadow-lg"
                                            :class="tone === 'direct_expert' ? 'bg-white/10 text-white' : 'bg-white text-slate-900'">
                                            <i data-lucide="zap" class="w-6 h-6"></i>
                                        </div>
                                        <h4 class="font-black text-sm uppercase tracking-widest mb-3">Direct & Rapid
                                        </h4>
                                        <p class="text-[11px] font-medium leading-relaxed opacity-70 italic mb-4">"Hi
                                            there. I'm Sarah. Give me your location and time, I'll get you set."</p>
                                        <div class="flex items-center gap-2 mt-auto">
                                            <span
                                                class="px-2 py-1 bg-white/10 rounded-md text-[8px] font-bold uppercase tracking-widest">Retail</span>
                                            <span
                                                class="px-2 py-1 bg-white/10 rounded-md text-[8px] font-bold uppercase tracking-widest">Services</span>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. KNOWLEDGE TAB -->
                <div x-show="activeTab === 'knowledge'" x-cloak x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-y-10"
                    x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8 min-w-0">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="bg-white rounded-[3rem] border border-slate-200 shadow-sm p-10">
                            <div class="mb-8">
                                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Business Specs
                                </h3>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Core
                                    Organizational Identity</p>
                            </div>

                            <div class="space-y-8">
                                <div>
                                    <label
                                        class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Entity
                                        Name</label>
                                    <input type="text" name="business_name"
                                        value="{{ $agent->configuration['business_name'] ?? '' }}"
                                        class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-sm font-bold focus:ring-slate-900 transition-all">
                                </div>
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label
                                            class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Industry
                                            Type</label>
                                        <input type="text" name="business_type"
                                            value="{{ $agent->configuration['business_type'] ?? '' }}"
                                            class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-sm font-bold focus:ring-slate-900 transition-all">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Work
                                            Window</label>
                                        <input type="text" name="hours"
                                            value="{{ is_array($agent->configuration['hours']) ? ($agent->configuration['hours']['schedule'] ?? '') : ($agent->configuration['hours'] ?? '') }}"
                                            class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-sm font-bold focus:ring-slate-900 transition-all"
                                            placeholder="e.g. 24/7, Mon-Fri 8-5">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-indigo-600 rounded-[3rem] p-10 text-white shadow-2xl relative overflow-hidden flex flex-col group">
                            <div
                                class="absolute -top-12 -right-12 w-48 h-48 bg-white/10 rounded-full blur-2xl group-hover:scale-125 transition-transform duration-700">
                            </div>
                            <div class="relative z-10 flex-1 flex flex-col">
                                <div class="mb-8">
                                    <h3 class="text-xl font-black uppercase tracking-tight">Mental Summary</h3>
                                    <p class="text-[10px] text-indigo-300 font-bold uppercase tracking-widest mt-1">The
                                        Agent's Master Context</p>
                                </div>
                                <textarea name="summary" rows="8"
                                    class="flex-1 w-full bg-indigo-700/50 border-2 border-indigo-400/30 rounded-[2rem] text-white text-sm leading-relaxed p-6 focus:ring-0 focus:border-indigo-400 placeholder-indigo-300 block resize-none custom-scrollbar"
                                    placeholder="Briefly describe your business story, values, and mission...">{{ $agent->configuration['raw_summary'] ?? '' }}</textarea>
                                <div
                                    class="mt-6 flex items-center justify-between text-[9px] font-black uppercase tracking-widest text-indigo-300">
                                    <span>Sync: Ready</span>
                                    <span>Quality: High</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SERVICE INTELLIGENCE (Dynamic List) -->
                    <div class="bg-white rounded-[3rem] border border-slate-200 shadow-sm p-10 md:p-12"
                        x-data="servicesEditor({{ json_encode($agent->configuration['services'] ?? []) }})">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                            <div>
                                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Intelligence
                                    Nodes</h3>
                                <p class="text-xs text-slate-500 mt-1 font-medium italic">Specific services and products
                                    the agent should push.</p>
                            </div>
                            <button type="button" @click="addService()"
                                class="px-8 py-4 bg-indigo-600 text-white rounded-[1.5rem] text-[10px] font-black uppercase tracking-[0.2em] hover:bg-indigo-700 transition-all shadow-lg">+
                                Inject Node</button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <template x-for="(service, index) in services" :key="index">
                                <div
                                    class="p-8 bg-slate-50 rounded-[2rem] border border-slate-100 relative group transition-all hover:bg-white hover:ring-8 hover:ring-slate-50">
                                    <button type="button" @click="removeService(index)"
                                        class="absolute top-6 right-6 w-10 h-10 bg-white border border-slate-100 rounded-xl flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 shadow-sm transition-all z-10">
                                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                                    </button>

                                    <div class="space-y-6">
                                        <div class="grid grid-cols-3 gap-4">
                                            <div class="col-span-2">
                                                <label
                                                    class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Service
                                                    Key</label>
                                                <input type="text" x-model="service.name"
                                                    :name="'services['+index+'][name]'"
                                                    class="w-full bg-white border-slate-200 rounded-xl text-xs font-bold p-3">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Base
                                                    KES</label>
                                                <input type="number" x-model="service.price"
                                                    :name="'services['+index+'][price]'"
                                                    class="w-full bg-white border-slate-200 rounded-xl text-xs font-bold p-3 text-center">
                                            </div>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">AI
                                                Context / Pricing Rules</label>
                                            <textarea x-model="service.description"
                                                :name="'services['+index+'][description]'" rows="2"
                                                class="w-full bg-white border-slate-200 rounded-xl text-[10px] font-medium p-3 resize-none"
                                                placeholder="Explain value to Agent..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <template x-if="services.length === 0">
                            <div class="py-20 text-center border-2 border-dashed border-slate-100 rounded-[2.5rem]">
                                <i data-lucide="layers" class="w-12 h-12 text-slate-200 mx-auto mb-4"></i>
                                <p class="text-sm font-bold text-slate-400">Your agent's brain is empty.</p>
                                <button type="button" @click="addService()"
                                    class="mt-4 text-[10px] font-black text-indigo-600 uppercase tracking-widest underline decoration-2 underline-offset-4">Configure
                                    First Node</button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 3. INTELLIGENCE TAB -->
                <div x-show="activeTab === 'intelligence'" x-cloak x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-y-10"
                    x-transition:enter-end="opacity-100 translate-y-0" class="space-y-8 min-w-0">
                    <div class="bg-white rounded-[3rem] border border-slate-200 shadow-sm p-12 max-w-3xl mx-auto"
                        x-data="{ action: '{{ ($agent->configuration['missed_call_action']['type'] ?? '') == 'forward_call' ? 'forward' : 'voicemail' }}' }">
                        <div class="text-center mb-12">
                            <div
                                class="w-20 h-20 bg-emerald-500 rounded-[2rem] mx-auto flex items-center justify-center text-white mb-8 shadow-2xl scale-110">
                                <i data-lucide="phone-forwarded" class="w-10 h-10"></i>
                            </div>
                            <h3 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Fallback Logic</h3>
                            <p class="text-xs text-slate-500 mt-2 font-medium">What happens when the agent needs human
                                help?</p>
                        </div>

                        <div class="grid grid-cols-2 gap-6 mb-12">
                            <button type="button" @click="action = 'voicemail'"
                                :class="action === 'voicemail' ? 'border-slate-900 bg-slate-900 text-white translate-y-[-4px] shadow-xl' : 'border-slate-100 bg-slate-50 text-slate-500 hover:border-slate-300'"
                                class="p-10 rounded-[2.5rem] border-2 flex flex-col items-center gap-4 transition-all duration-300">
                                <input type="radio" name="missed_call_action" value="voicemail" x-model="action"
                                    class="hidden">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                                    :class="action === 'voicemail' ? 'bg-white/10' : 'bg-white shadow-sm'">
                                    <i data-lucide="mailbox" class="w-6 h-6"></i>
                                </div>
                                <span class="text-[11px] font-black uppercase tracking-[0.2em]">Record Message</span>
                            </button>

                            <button type="button" @click="action = 'forward'"
                                :class="action === 'forward' ? 'border-slate-900 bg-slate-900 text-white translate-y-[-4px] shadow-xl' : 'border-slate-100 bg-slate-50 text-slate-500 hover:border-slate-300'"
                                class="p-10 rounded-[2.5rem] border-2 flex flex-col items-center gap-4 transition-all duration-300">
                                <input type="radio" name="missed_call_action" value="forward" x-model="action"
                                    class="hidden">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                                    :class="action === 'forward' ? 'bg-white/10' : 'bg-white shadow-sm'">
                                    <i data-lucide="phone-outgoing" class="w-6 h-6"></i>
                                </div>
                                <span class="text-[11px] font-black uppercase tracking-[0.2em]">Live Handover</span>
                            </button>
                        </div>

                        <div x-show="action === 'forward'" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-95" class="space-y-6">
                            <div class="bg-indigo-50 border border-indigo-100 rounded-[2rem] p-8">
                                <label
                                    class="block text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-4 ml-1 text-center">Strategic
                                    Human Endpoint</label>
                                <div
                                    class="flex items-stretch shadow-xl rounded-2xl overflow-hidden border-2 border-indigo-200">
                                    <div
                                        class="flex items-center justify-center px-6 bg-white border-r border-indigo-200 text-indigo-400 font-black text-sm">
                                        +254
                                    </div>
                                    <input type="tel" name="forward_number"
                                        value="{{ $agent->configuration['missed_call_action']['forward_number'] ?? '' }}"
                                        class="flex-1 px-6 py-5 bg-white border-none focus:ring-0 font-mono text-base font-black text-slate-900"
                                        placeholder="712 345 678">
                                </div>
                                <p
                                    class="mt-4 text-[9px] text-indigo-400 font-bold uppercase tracking-widest text-center">
                                    Agent will use "Human Assist" protocol on trigger</p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- PRO TIP (Ultra Modern) -->
            <div
                class="mt-16 bg-gradient-to-r from-slate-900 to-indigo-950 rounded-[3rem] p-12 flex flex-col md:flex-row items-center gap-10 shadow-2xl relative overflow-hidden group border border-white/5">
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_50%_120%,rgba(99,102,241,0.15),transparent)]">
                </div>
                <div
                    class="w-24 h-24 bg-white/5 backdrop-blur-3xl rounded-[2rem] flex items-center justify-center text-white border border-white/10 flex-shrink-0 relative z-10">
                    <i data-lucide="lightbulb" class="w-10 h-10 text-indigo-400 animate-pulse"></i>
                </div>
                <div class="relative z-10 text-center md:text-left">
                    <h5 class="text-sm font-black text-white uppercase tracking-widest mb-3">Intelligence Tip: Semantic
                        Precision</h5>
                    <p
                        class="text-[11px] text-slate-400 leading-relaxed font-medium max-w-2xl uppercase tracking-wider">
                        Your agent isn't just a bot; it's a <span class="text-indigo-400 font-bold">Semantic
                            Processor</span>. Use the <span class="text-white font-bold">Intelligence Nodes</span> to
                        define rigid pricing tiers. This prevents the agent from being "negotiated down" by clever
                        callers.</p>
                </div>
                <div class="md:ml-auto relative z-10">
                    <button type="button"
                        class="px-8 py-4 bg-white/5 hover:bg-white/10 border border-white/10 rounded-2xl text-[9px] font-black text-white uppercase tracking-widest transition-all">Dismiss
                        Insight</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('servicesEditor', (initialServices) => ({
                    services: initialServices || [],
                    addService() {
                        this.services.push({ name: '', price: 0, description: '' });
                    },
                    removeService(index) {
                        this.services.splice(index, 1);
                    }
                }))
            })
            // Enhanced icon initialization
            document.addEventListener('DOMContentLoaded', () => {
                lucide.createIcons();
            });
            // Re-run after Alpine transitions if problematic
            document.addEventListener('alpine:initialized', () => {
                lucide.createIcons();
            });
        </script>
    @endpush
</x-app-layout>