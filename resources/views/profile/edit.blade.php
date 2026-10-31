<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Profile & Settings</h1>
                <p class="text-xs text-slate-500 mt-1 font-medium">Manage your personal identity and platform configurations.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-2 w-full min-w-0" x-data="{ 
        activeTab: new URLSearchParams(window.location.search).get('tab') || 'account'
    }">
        <div class="flex flex-col lg:flex-row gap-8 w-full min-w-0">
            <!-- SETTINGS NAVIGATION -->
            <div class="w-full lg:w-64 flex-shrink-0 space-y-1">
                <button @click="activeTab = 'account'" 
                    :class="activeTab === 'account' ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-500 hover:bg-slate-50'"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    Account Identity
                </button>
                <button @click="activeTab = 'security'" 
                    :class="activeTab === 'security' ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-500 hover:bg-slate-50'"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    Security & Login
                </button>
                <button @click="activeTab = 'api'" 
                    :class="activeTab === 'api' ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-500 hover:bg-slate-50'"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                    <i data-lucide="code-2" class="w-4 h-4"></i>
                    Developer Hub
                </button>
                <button @click="activeTab = 'usage'" 
                    :class="activeTab === 'usage' ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-500 hover:bg-slate-50'"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    Usage Analytics
                </button>
                <div class="pt-4 mt-4 border-t border-slate-100">
                    <button @click="activeTab = 'danger'" 
                        :class="activeTab === 'danger' ? 'bg-red-600 text-white' : 'text-red-400 hover:bg-red-50'"
                        class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        Danger Zone
                    </button>
                </div>
            </div>

            <!-- CONTENT AREA -->
            <div class="flex-1 min-w-0 space-y-8">
                <!-- 1. ACCOUNT IDENTITY -->
                <div x-show="activeTab === 'account'" x-cloak x-transition class="space-y-8">
                    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm p-8 md:p-10 overflow-hidden">
                        <div class="mb-8">
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Personal Information</h3>
                            <p class="text-xs text-slate-500 mt-1 font-medium italic">Update your primary registration and login email.</p>
                        </div>
                        <div class="max-w-xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm p-8 md:p-10 overflow-hidden">
                        <div class="mb-8">
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Business Profile</h3>
                            <p class="text-xs text-slate-500 mt-1 font-medium italic">General settings for your AI agent's company context.</p>
                        </div>
                        <div class="max-w-xl">
                            @include('profile.partials.business-profile-form')
                        </div>
                    </div>
                </div>

                <!-- 2. SECURITY -->
                <div x-show="activeTab === 'security'" x-cloak x-transition class="space-y-8">
                    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm p-8 md:p-10 overflow-hidden">
                        <div class="mb-8">
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Update Password</h3>
                            <p class="text-xs text-slate-500 mt-1 font-medium italic">Ensure your account is using a long, random password to stay secure.</p>
                        </div>
                        <div class="max-w-xl">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>

                <!-- 3. DEVELOPER HUB -->
                <div x-show="activeTab === 'api'" x-cloak x-transition class="space-y-8">
                    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm p-8 md:p-10 overflow-hidden">
                        <div class="mb-8">
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight text-indigo-600">API Access Tokens</h3>
                            <p class="text-xs text-slate-500 mt-1 font-medium italic">Connect your Voice Agents to external CRMs like Salesforce or custom apps.</p>
                        </div>
                        @include('profile.partials.api-keys-manager')
                    </div>
                </div>

                <!-- 4. USAGE ANALYTICS -->
                <div x-show="activeTab === 'usage'" x-cloak x-transition class="space-y-8">
                    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm p-8 md:p-10 overflow-hidden">
                        <div class="mb-8">
                            <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Real-time Consumption</h3>
                            <p class="text-xs text-slate-500 mt-1 font-medium italic">Monitor your Voice Engine usage and costs in real-time.</p>
                        </div>
                        @include('profile.partials.usage-stats-dashboard')
                    </div>
                </div>

                <!-- 5. DANGER ZONE -->
                <div x-show="activeTab === 'danger'" x-cloak x-transition class="space-y-8">
                    <div class="bg-red-50 rounded-[2.5rem] border border-red-100 shadow-sm p-8 md:p-10 overflow-hidden">
                        <div class="mb-8">
                            <h3 class="text-xl font-black text-red-600 uppercase tracking-tight">Delete Account</h3>
                            <p class="text-xs text-red-500 mt-1 font-medium italic">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
                        </div>
                        <div class="max-w-xl text-slate-900">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>