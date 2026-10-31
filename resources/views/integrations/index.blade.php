<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900 uppercase">Integration Hub</h1>
                <p class="text-slate-500 mt-1">Global infrastructure connectivity and service registry.</p>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="px-4 py-2 bg-white border border-slate-200 rounded-xl shadow-sm flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-widest">Global Sync: Active</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8">
        <!-- Top Metrics Row (Diagnostic Restoration) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <i data-lucide="zap" class="w-5 h-5"></i>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">API Latency</label>
                    <span class="text-lg font-black text-slate-900">{{ $diagnostics['latency'] }}ms</span>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Uptime Registry</label>
                    <span class="text-lg font-black text-slate-900">{{ $diagnostics['uptime'] }}%</span>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Success Rate</label>
                    <span class="text-lg font-black text-slate-900">99.8%</span>
                </div>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white">
                    <i data-lucide="webhook" class="w-5 h-5"></i>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Hooks</label>
                    <span class="text-lg font-black text-slate-900">{{ $diagnostics['hooks_count'] }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Integration Grid (70%) -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Category: Infrastructure -->
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Infrastructure Layer</span>
                        <div class="flex-1 h-px bg-slate-200"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Zadarma Integration Card -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden group hover:border-indigo-500 transition-all duration-300">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="w-12 h-12 rounded-xl bg-slate-900 flex items-center justify-center text-white shadow-lg">
                                        <i data-lucide="phone"></i>
                                    </div>
                                    @if(isset($integrations['zadarma']))
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700 border border-emerald-200">Linked</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-500 border border-slate-200">Pending</span>
                                    @endif
                                </div>
                                <h3 class="text-lg font-bold text-slate-900">Virtual Number</h3>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Enterprise telephony backbone for automated voice interactions.</p>
                                
                                @if(isset($integrations['zadarma']))
                                    <div class="mt-6 p-3 bg-slate-50 rounded-xl border border-slate-100">
                                        <div class="flex justify-between items-center text-[10px]">
                                            <span class="font-bold text-slate-400 uppercase tracking-tight">Active Number</span>
                                            <span class="font-mono font-bold text-slate-900">{{ $integrations['zadarma']->settings['phone_number'] ?? 'Assigned after interview' }}</span>
                                        </div>
                                        <div class="mt-2 flex justify-between items-center text-[10px]">
                                            <span class="font-bold text-slate-400 uppercase tracking-tight">SIP Status</span>
                                            <span class="text-emerald-600 font-bold uppercase">Healthy</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-6">
                                        <div class="flex items-start gap-2 p-3 bg-amber-50 rounded-xl border border-amber-100 text-amber-800 text-[10px] font-medium leading-relaxed">
                                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                                            Awaiting number issuance. Please complete the Onboarding Interview to finalize provisioning.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- WhatsApp Card -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:border-emerald-500 transition-all duration-300">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-inner">
                                        <i data-lucide="message-square"></i>
                                    </div>
                                    @if(isset($integrations['whatsapp']))
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700 border border-emerald-200">Connected</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-50 text-slate-400 border border-slate-200">Ready</span>
                                    @endif
                                </div>
                                <h3 class="text-lg font-bold text-slate-900">WhatsApp Business</h3>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Automated booking confirmations and rich media notifications.</p>
                                
                                <button onclick="window.dispatchEvent(new CustomEvent('whatsapp-toggle'))" class="w-full mt-6 py-2 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-black transition-all">Configure</button>
                            </div>
                        </div>

                        <!-- Webhook Card -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:border-indigo-500 transition-all duration-300">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 shadow-inner">
                                        <i data-lucide="webhook"></i>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700 border border-emerald-200">Active</span>
                                </div>
                                <h3 class="text-lg font-bold text-slate-900">Event Registry</h3>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Broadcast call outcomes and transcripts to your external metadata sink.</p>
                                
                                <form action="{{ route('integrations.update') }}" method="POST" class="mt-6">
                                    @csrf
                                    <input type="url" name="webhook_url" 
                                        value="{{ $agent->configuration['integrations']['webhook_url'] ?? '' }}"
                                        class="w-full text-xs font-mono bg-slate-50 border-slate-200 rounded-lg focus:ring-slate-900 focus:border-slate-900" 
                                        placeholder="https://api.domain.com/hook" required>
                                    <button type="submit" class="w-full mt-2 py-2 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-black transition-all">Update Hook</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category: Productivity -->
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Productivity Sync</span>
                        <div class="flex-1 h-px bg-slate-200"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Google Calendar -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:border-indigo-500 transition-all duration-300">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-inner">
                                        <i data-lucide="calendar"></i>
                                    </div>
                                    @if(isset($integrations['google_calendar']))
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700 border border-emerald-200">Authorized</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-50 text-slate-400 border border-slate-200">Disconnected</span>
                                    @endif
                                </div>
                                <h3 class="text-lg font-bold text-slate-900">Google Calendar</h3>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Check availability and book meetings on your primary calendar.</p>
                                
                                <div class="mt-6">
                                    @if(isset($integrations['google_calendar']))
                                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 mb-2">
                                            <div class="flex justify-between items-center text-[10px]">
                                                <span class="font-bold text-slate-400 uppercase tracking-tight">Sync Target</span>
                                                <span class="font-bold text-slate-900">{{ $integrations['google_calendar']->settings['calendar_name'] ?? 'Primary' }}</span>
                                            </div>
                                        </div>
                                        <form action="{{ route('integrations.disconnect', $integrations['google_calendar']->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-full py-2 border border-red-200 text-red-600 text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-red-50 transition-all">Revoke Access</button>
                                        </form>
                                    @else
                                        <a href="{{ route('integrations.connect', 'google_calendar') }}" class="w-full inline-flex justify-center items-center py-3 bg-white border border-slate-200 text-slate-700 text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-slate-50 transition-all">
                                            <i data-lucide="shield-check" class="w-3.5 h-3.5 mr-2"></i> Authorize Google
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- HubSpot CRM Card -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:border-orange-500 transition-all duration-300">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 shadow-inner">
                                        <i data-lucide="users"></i>
                                    </div>
                                    @if(isset($integrations['hubspot']))
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700 border border-emerald-200">Connected</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-50 text-slate-400 border border-slate-200">Ready</span>
                                    @endif
                                </div>
                                <h3 class="text-lg font-bold text-slate-900">HubSpot CRM</h3>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Maintain client memory and automated call outcome logging.</p>
                                
                                <button onclick="window.dispatchEvent(new CustomEvent('hubspot-toggle'))" class="w-full mt-6 py-2 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-black transition-all">Connect CRM</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category: Finance -->
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Finance & Payments</span>
                        <div class="flex-1 h-px bg-slate-200"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- M-Pesa Integration -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:border-emerald-500 transition-all duration-300">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-inner">
                                        <i data-lucide="wallet"></i>
                                    </div>
                                    @if(isset($integrations['mpesa']))
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700 border border-emerald-200">Linked</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-50 text-slate-400 border border-slate-200">Ready</span>
                                    @endif
                                </div>
                                <h3 class="text-lg font-bold text-slate-900">M-Pesa Express</h3>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Automate payment collection via STK Push. Hybrid mode supported.</p>
                                
                                <div class="mt-6 flex flex-col gap-2">
                                    <button onclick="toggleMpesaConfig()" class="w-full py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-black transition-all">
                                        Configure
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Google Maps Card -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:border-red-500 transition-all duration-300">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center text-red-600 shadow-inner">
                                        <i data-lucide="map-pin"></i>
                                    </div>
                                    @if(isset($integrations['google_maps']))
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700 border border-emerald-200">Linked</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-50 text-slate-400 border border-slate-200">Ready</span>
                                    @endif
                                </div>
                                <h3 class="text-lg font-bold text-slate-900">Navigation & Geo</h3>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Provide accurate location pins to your customers after booking.</p>
                                
                                <button onclick="window.dispatchEvent(new CustomEvent('maps-toggle'))" class="w-full mt-6 py-2 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-black transition-all">Configure Maps</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar Monitoring (30%) (Backup diagnostics, user prefers top cards) -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Health Board -->
                <div class="bg-white rounded-2xl border border-slate-900 shadow-2xl p-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5">
                        <i data-lucide="activity" class="w-16 h-16 text-slate-900"></i>
                    </div>
                    
                    <h3 class="text-slate-900 font-black uppercase tracking-widest text-xs mb-6">Real-time Telemetry</h3>
                    
                    <div class="space-y-6">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">System Latency</span>
                                <span class="text-xs font-bold text-indigo-600">{{ $diagnostics['latency'] }}ms</span>
                            </div>
                            <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: 15%"></div>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-slate-100">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Active Agents</span>
                                <span class="text-xl font-black text-slate-900">1</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- WhatsApp Configuration Modal -->
    <template x-teleport="body">
        <div x-data="{ open: false }" x-show="open" x-cloak @whatsapp-toggle.window="open = !open" class="fixed inset-0 z-[100] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm" @click="open = false"></div>
                
                <div x-show="open" 
                    x-transition:enter="transition ease-out duration-300 transform" 
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                    class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-10 z-10">
                    
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">WhatsApp (Platform Managed)</h3>
                        <button @click="open = false" class="text-slate-400 hover:text-slate-600 transition-colors"><i data-lucide="x" class="w-6 h-6"></i></button>
                    </div>
                    
                    <form action="{{ route('integrations.update') }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="whatsapp_setup" value="1">
                        
                        <div class="space-y-6">
                            <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl">
                                <div class="flex items-center gap-3 text-emerald-800">
                                    <i data-lucide="zap" class="w-5 h-5"></i>
                                    <span class="font-bold text-sm">Infrastructure Ready</span>
                                </div>
                                <p class="text-xs text-emerald-600 mt-2 leading-relaxed">This service is powered by our global Twilio engine. We just need your business number below.</p>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Your Business WhatsApp Number</label>
                                <input type="text" name="business_number" value="{{ $integrations['whatsapp']->settings['business_number'] ?? '' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 font-mono text-sm" placeholder="2547XXXXXXXX" required>
                                <p class="mt-2 text-[9px] text-slate-400 italic">This is the number the agent will use for your specific business identifier.</p>
                            </div>

                            <div class="space-y-4 pt-4 border-t border-slate-100">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-500 font-bold uppercase tracking-wider">Gateway Status</span>
                                    <span class="text-emerald-600 font-black uppercase">Active & Managed</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex gap-3">
                            <button type="button" @click="open = false" class="flex-1 py-3 border border-slate-200 text-slate-500 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
                            <button type="submit" class="flex-1 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-black shadow-lg">Save & Activate</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- HubSpot Modal -->
    <template x-teleport="body">
        <div x-data="{ open: false }" x-show="open" x-cloak @hubspot-toggle.window="open = !open" class="fixed inset-0 z-[100] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm" @click="open = false"></div>
                
                <div x-show="open" 
                    x-transition:enter="transition ease-out duration-300 transform" 
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                    class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-10 z-10">
                    
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">HubSpot CRM Config</h3>
                        <button @click="open = false" class="text-slate-400 hover:text-slate-600 transition-colors"><i data-lucide="x" class="w-6 h-6"></i></button>
                    </div>
                    <form action="{{ route('integrations.update') }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="hubspot_setup" value="1">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Private App Access Token</label>
                                <textarea name="access_token" rows="4" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-orange-500 font-mono text-xs shadow-inner bg-slate-50" required>{{ $integrations['hubspot']->settings['access_token'] ?? '' }}</textarea>
                                <p class="mt-2 text-[9px] text-slate-400 italic">Generate this from Settings > Integrations > Private Apps in your HubSpot portal.</p>
                            </div>
                        </div>
                        <div class="mt-8 flex gap-3">
                            <button type="button" @click="open = false" class="flex-1 py-3 border border-slate-200 text-slate-500 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
                            <button type="submit" class="flex-1 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-black shadow-lg">Save CRM Token</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Google Maps Modal -->
    <template x-teleport="body">
        <div x-data="{ open: false }" x-show="open" x-cloak @maps-toggle.window="open = !open" class="fixed inset-0 z-[100] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm" @click="open = false"></div>
                
                <div x-show="open" 
                    x-transition:enter="transition ease-out duration-300 transform" 
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                    class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-10 z-10">
                    
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Navigation & Location</h3>
                        <button @click="open = false" class="text-slate-400 hover:text-slate-600 transition-colors"><i data-lucide="x" class="w-6 h-6"></i></button>
                    </div>
                    <form action="{{ route('integrations.update') }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="maps_setup" value="1">
                        <div class="space-y-4">
                            <div class="p-4 bg-red-50 border border-red-100 rounded-2xl mb-4 text-center">
                                <p class="text-[10px] text-red-700 leading-relaxed font-black uppercase tracking-wider">Platform API Proxy Connected</p>
                                <p class="text-[9px] text-red-600 mt-1">Maps service is managed. We just need your Location ID.</p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Google Place ID (Your Business)</label>
                                <input type="text" name="place_id" value="{{ $integrations['google_maps']->settings['place_id'] ?? '' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-red-500 font-mono text-sm shadow-inner bg-slate-50" placeholder="ChIJN1t_tDeuEmsRUsoyG83frY4" required>
                                <p class="mt-2 text-[9px] text-slate-400 italic">Ensures students and clients receive exact coordinates via the Voice Engine.</p>
                            </div>
                        </div>
                        <div class="mt-8 flex gap-3">
                            <button type="button" @click="open = false" class="flex-1 py-3 border border-slate-200 text-slate-500 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
                            <button type="submit" class="flex-1 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-black shadow-lg">Save Location</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Hybrid M-Pesa Configuration Modal -->
    <template x-teleport="body">
        <div id="mpesa-modal" 
            x-data="{ 
                open: false, 
                mode: '{{ old('mpesa_setup_mode', $integrations['mpesa']->settings['mpesa_setup_mode'] ?? 'aggregator') }}'
            }" 
            x-show="open" 
            x-cloak
            @mpesa-toggle.window="open = !open"
            class="fixed inset-0 z-[100] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 transition-opacity bg-slate-900/40 backdrop-blur-sm" @click="open = false"></div>

                <div x-show="open" 
                    x-transition:enter="transition ease-out duration-300 transform" 
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                    class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-3xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-10 z-10">
                    
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">M-Pesa Configuration</h3>
                        <button @click="open = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>

                    <form action="{{ route('integrations.update') }}" method="POST">
                        @csrf @method('PATCH')
                        
                        <!-- Mode Selection -->
                        <div class="mb-8 p-1 bg-slate-100 rounded-2xl flex gap-1">
                            <button type="button" 
                                    @click="mode = 'aggregator'"
                                    :class="mode === 'aggregator' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                                    class="flex-1 py-3 px-4 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-center flex items-center justify-center gap-2">
                                <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                                <span>Platform Mode</span>
                            </button>
                            <button type="button" 
                                    @click="mode = 'direct'"
                                    :class="mode === 'direct' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                                    class="flex-1 py-3 px-4 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-center flex items-center justify-center gap-2">
                                <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                                <span>Direct Daraja</span>
                            </button>
                            <input type="hidden" name="mpesa_setup_mode" :value="mode">
                        </div>

                        <!-- Mode: Aggregator (Simple) -->
                        <div x-show="mode === 'aggregator'" class="space-y-4">
                            <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                                <p class="text-[10px] text-emerald-800 font-bold leading-relaxed">Integrated Settlement: Funds flow through the platform hub and settle to your phone instantly. No dev keys required.</p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Recipient M-Pesa Number</label>
                                <input type="text" name="payout_number" 
                                    value="{{ $integrations['mpesa']->settings['payout_number'] ?? '' }}" 
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 transition-all font-mono font-bold bg-slate-50" 
                                    placeholder="2547XXXXXXXX">
                            </div>
                        </div>

                        <!-- Mode: Direct (Enterprise) -->
                        <div x-show="mode === 'direct'" class="space-y-4">
                            <p class="text-[10px] text-slate-500 font-medium leading-relaxed italic mb-4">Enterprise mode. Money goes directly to your Safaricom Till or Paybill number.</p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Shortcode</label>
                                    <input type="text" name="shortcode" value="{{ $integrations['mpesa']->settings['shortcode'] ?? '' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-medium bg-slate-50" placeholder="174379">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Passkey</label>
                                    <input type="password" name="passkey" value="{{ $integrations['mpesa']->settings['passkey'] ?? '' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-medium bg-slate-50">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Consumer Key</label>
                                <input type="text" name="consumer_key" value="{{ $integrations['mpesa']->settings['consumer_key'] ?? '' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-medium bg-slate-50">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Consumer Secret</label>
                                <input type="password" name="consumer_secret" value="{{ $integrations['mpesa']->settings['consumer_secret'] ?? '' }}" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 transition-all text-sm font-medium bg-slate-50">
                            </div>
                        </div>

                        <div class="mt-8 flex gap-3">
                            <button type="button" @click="open = false" class="flex-1 py-3 border border-slate-200 text-slate-500 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
                            <button type="submit" class="flex-1 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-black transition-all shadow-lg">Save Config</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <script>
        function toggleMpesaConfig() {
            window.dispatchEvent(new CustomEvent('mpesa-toggle'));
        }
    </script>
</x-app-layout>