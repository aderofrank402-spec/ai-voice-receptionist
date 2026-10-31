<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body
    class="bg-slate-50 font-sans antialiased text-slate-900 selection:bg-indigo-500 selection:text-white relative min-h-screen bg-fixed"
    style="background-image: radial-gradient(at 0% 0%, rgba(224, 231, 255, 1) 0, transparent 50%), radial-gradient(at 100% 0%, rgba(238, 242, 255, 1) 0, transparent 50%), radial-gradient(at 100% 100% , rgba(241, 245, 249, 1) 0, transparent 50%), radial-gradient(at 0% 100%, rgba(219, 234, 254, 0.8) 0, transparent 50%);"
    x-data="{ 
        sidebarOpen: false, 
        showAddKnowledge: false,
        showViewModal: false,
        viewSource: null
    }" @open-add-knowledge.window="showAddKnowledge = true"
    @view-knowledge.window="viewSource = $event.detail; showViewModal = true">



    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar (Desktop) -->
        <aside class="hidden md:flex flex-col w-64 bg-white border-r border-slate-200 flex-shrink-0">
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-slate-100">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-2 font-bold text-xl tracking-tight text-slate-900">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                        <i data-lucide="mic-2" class="w-5 h-5"></i>
                    </div>
                    VoiceAgents
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto mt-2">
                <!-- Group 1: Operations -->
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Operations</p>
                <a href="{{ route('dashboard') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 group' }} transition-all duration-200">
                    <i data-lucide="layout-dashboard"
                        class="w-5 h-5 mr-3 {{ request()->routeIs('dashboard') ? 'text-indigo-700' : 'text-slate-400 group-hover:text-indigo-600' }}"></i>
                    Dashboard Overview
                </a>
                <a href="{{ route('calls.index') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('calls.index') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 group' }} transition-all duration-200">
                    <i data-lucide="archive"
                        class="w-5 h-5 mr-3 {{ request()->routeIs('calls.index') ? 'text-indigo-700' : 'text-slate-400 group-hover:text-indigo-600' }}"></i>
                    Call Archive
                </a>

                <!-- Group 2: The Agent -->
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2 mt-8">Agent
                    Management</p>
                <a href="{{ route('agent.edit') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('agent.edit') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 group' }} transition-all duration-200">
                    <i data-lucide="user-cog"
                        class="w-5 h-5 mr-3 {{ request()->routeIs('agent.edit') ? 'text-indigo-700' : 'text-slate-400 group-hover:text-indigo-600' }}"></i>
                    Agent Persona
                </a>
                <a href="{{ route('agent.knowledge') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('agent.knowledge') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 group' }} transition-all duration-200">
                    <i data-lucide="brain-circuit"
                        class="w-5 h-5 mr-3 {{ request()->routeIs('agent.knowledge') ? 'text-indigo-700' : 'text-slate-400 group-hover:text-indigo-600' }}"></i>
                    Knowledge Base
                </a>

                <!-- Group 3: Infrastructure -->
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2 mt-8">Infrastructure
                </p>
                <a href="{{ route('integrations.index') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('integrations.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 group' }} transition-all duration-200">
                    <i data-lucide="webhook"
                        class="w-5 h-5 mr-3 {{ request()->routeIs('integrations.*') ? 'text-indigo-700' : 'text-slate-400 group-hover:text-indigo-600' }}"></i>
                    Integrations
                </a>
                <a href="{{ route('billing.index') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('billing.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 group' }} transition-all duration-200">
                    <i data-lucide="credit-card"
                        class="w-5 h-5 mr-3 {{ request()->routeIs('billing.*') ? 'text-indigo-700' : 'text-slate-400 group-hover:text-indigo-600' }}"></i>
                    Billing & Plan
                </a>

                <!-- Group 4: Account -->
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2 mt-8">Personal</p>
                <a href="{{ route('profile.edit') }}"
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('profile.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 group' }} transition-all duration-200">
                    <i data-lucide="settings"
                        class="w-5 h-5 mr-3 {{ request()->routeIs('profile.*') ? 'text-indigo-700' : 'text-slate-400 group-hover:text-indigo-600' }}"></i>
                    Profile Settings
                </a>

                <div class="pt-6 mt-6 border-t border-slate-100">
                    <a href="{{ route('home') }}"
                        class="flex items-center px-3 py-2 text-xs font-semibold rounded-lg text-slate-400 hover:bg-slate-50 hover:text-indigo-600 transition-all">
                        <i data-lucide="external-link" class="w-4 h-4 mr-3"></i>
                        Exit to Website
                    </a>
                </div>
            </nav>

            <!-- User Menu -->
            <div class="p-4 border-t border-slate-100">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold text-xs shadow-inner">
                        {{ substr(Auth::user()->name, 0, 2) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-900 truncate">{{ Auth::user()->name }}</p>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="text-xs text-slate-500 hover:text-red-600 transition-colors flex items-center gap-1 mt-0.5">
                                <i data-lucide="log-out" class="w-3 h-3"></i> Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/50 z-40 md:hidden"
            @click="sidebarOpen = false"></div>

        <!-- Mobile Sidebar -->
        <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-300 transform" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 w-64 bg-white z-50 md:hidden flex flex-col shadow-xl">

            <div class="h-16 flex items-center justify-between px-6 border-b border-slate-100">
                <span class="font-bold text-lg text-slate-900">Menu</span>
                <button @click="sidebarOpen = false" class="text-slate-500 hover:text-slate-800">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center px-3 py-2 text-base font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>
                    Overview
                </a>
                <a href="{{ route('calls.index') }}"
                    class="flex items-center px-3 py-2 text-base font-medium rounded-lg {{ request()->routeIs('calls.index') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }}">
                    <i data-lucide="archive" class="w-5 h-5 mr-3"></i>
                    Call Archive
                </a>
                <a href="{{ route('onboarding.index') }}"
                    class="flex items-center px-3 py-2 text-base font-medium rounded-lg {{ request()->routeIs('onboarding.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }}">
                    <i data-lucide="users" class="w-5 h-5 mr-3"></i>
                    Assistants
                </a>
                <div class="border-t border-slate-100 my-4"></div>
                <a href="{{ route('profile.edit') }}"
                    class="flex items-center px-3 py-2 text-base font-medium rounded-lg {{ request()->routeIs('profile.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-700 hover:bg-slate-50' }}">
                    <i data-lucide="settings" class="w-5 h-5 mr-3"></i>
                    Settings
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center px-3 py-2 text-base font-medium rounded-lg text-red-600 hover:bg-red-50">
                        <i data-lucide="log-out" class="w-5 h-5 mr-3"></i>
                        Log Out
                    </button>
                </form>
            </nav>
        </div>

        <!-- Main Content -->
        <main {{ $attributes->merge(['class' => 'flex-1 flex flex-col relative min-w-0']) }}>

            <!-- Mobile Header -->
            <header
                class="md:hidden h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 z-20 sticky top-0">
                <div class="flex items-center gap-2">
                    <button @click="sidebarOpen = true" class="text-slate-500 hover:text-slate-800 p-1">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    <span class="font-bold text-slate-900">VoiceAgents</span>
                </div>
                <div
                    class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold text-xs">
                    {{ substr(Auth::user()->name, 0, 2) }}
                </div>
            </header>

            <!-- Scrollable Content Area -->
            <div class="flex-1 overflow-y-auto p-4 md:p-8 min-w-0">
                <div class="max-w-7xl mx-auto w-full min-w-0">
                    <!-- Dynamic Header Slot -->
                    @if (isset($header))
                        <div class="mb-8">
                            {{ $header }}
                        </div>
                    @endif

                    <!-- Dynamic Content Slot -->
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>

    <!-- GLOBAL MODAL ARCHITECTURE (Root Stacking System) -->
    <template x-teleport="body">
        <div class="z-[100000] relative">
            <!-- 1. ADD KNOWLEDGE MODAL -->
            <div x-show="showAddKnowledge"
                class="fixed inset-0 flex items-center justify-center p-4 bg-slate-900/95 backdrop-blur-xl"
                style="z-index: 100000 !important;" x-cloak x-transition.opacity>

                <div class="bg-white w-full max-w-xl rounded-[2.5rem] shadow-[0_32px_128px_-10px_rgba(0,0,0,0.5)] overflow-hidden relative"
                    @click.away="showAddKnowledge = false" x-show="showAddKnowledge"
                    x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

                    <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Expand Wisdom</h3>
                            <p class="text-[10px] text-slate-400 font-black mt-1 uppercase tracking-[0.2em]">Select
                                Intelligence Stream</p>
                        </div>
                        <button @click="showAddKnowledge = false"
                            class="text-slate-300 hover:text-slate-600 p-2 rounded-xl transition-all">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>

                    <form action="{{ route('agent.knowledge.store') }}" method="POST" enctype="multipart/form-data"
                        class="p-8 space-y-8" x-data="{ 
                            addType: '{{ old('type', 'snippet') }}', 
                            isSyncing: false,
                            showErrors: {{ $errors->hasAny(['title', 'file', 'url', 'content']) ? 'true' : 'false' }},
                            selectedFileName: '',
                            handleFileSelect(e) {
                                if (e.target.files.length > 0) {
                                    this.selectedFileName = e.target.files[0].name;
                                }
                            },
                            submitKnowledge(e) {
                                if (e.target.checkValidity()) {
                                    this.isSyncing = true;
                                }
                            },
                            init() {
                                if (this.showErrors) {
                                    // Properly dispatch to the global layout state
                                    this.$nextTick(() => {
                                        window.dispatchEvent(new CustomEvent('open-add-knowledge'));
                                    });
                                }
                            }
                        }" @submit="submitKnowledge($event)">
                        @csrf

                        <div class="grid grid-cols-3 gap-3">
                            <button type="button" @click="addType = 'file'"
                                :class="addType === 'file' ? 'bg-slate-900 text-white shadow-xl' : 'bg-slate-50 text-slate-500 border-slate-100 hover:bg-slate-100'"
                                class="flex flex-col items-center gap-3 p-4 rounded-2xl border border-transparent transition-all">
                                <i data-lucide="file-text" class="w-5 h-5"></i>
                                <span class="text-[9px] font-black uppercase tracking-widest">Document</span>
                            </button>
                            <button type="button" @click="addType = 'link'"
                                :class="addType === 'link' ? 'bg-slate-900 text-white shadow-xl' : 'bg-slate-50 text-slate-500 border-slate-100 hover:bg-slate-100'"
                                class="flex flex-col items-center gap-3 p-4 rounded-2xl border border-transparent transition-all">
                                <i data-lucide="globe" class="w-5 h-5"></i>
                                <span class="text-[9px] font-black uppercase tracking-widest">Web Link</span>
                            </button>
                            <button type="button" @click="addType = 'snippet'"
                                :class="addType === 'snippet' ? 'bg-slate-900 text-white shadow-xl' : 'bg-slate-50 text-slate-500 border-slate-100 hover:bg-slate-100'"
                                class="flex flex-col items-center gap-3 p-4 rounded-2xl border border-transparent transition-all">
                                <i data-lucide="quote" class="w-5 h-5"></i>
                                <span class="text-[9px] font-black uppercase tracking-widest">Expert Fact</span>
                            </button>
                        </div>

                        <input type="hidden" name="type" :value="addType">

                        <div class="space-y-5">
                            <div>
                                <label
                                    class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block ml-1">Source
                                    Identifier</label>
                                <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g., Services Catalog 2024"
                                    class="w-full px-5 py-4 bg-slate-50 border {{ $errors->has('title') ? 'border-red-500' : 'border-slate-200' }} rounded-xl text-sm font-bold placeholder-slate-300 focus:outline-none focus:border-slate-900 transition-all">
                                @error('title')
                                    <p class="mt-1 text-[10px] text-red-500 font-bold ml-1 uppercase tracking-tighter">{{ $message }}</p>
                                @enderror
                            </div>

                            <div x-show="addType === 'file'" x-cloak>
                                <label
                                    class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block ml-1">Knowledge
                                    File</label>
                                <div class="relative group">
                                    <input type="file" name="file" class="hidden" id="kb_file_input_final"
                                        accept=".pdf,.txt,.docx" @change="handleFileSelect($event)">
                                    <label for="kb_file_input_final"
                                        :class="selectedFileName ? 'border-emerald-500 bg-emerald-50/30' : ({{ $errors->has('file') ? 'true' : 'false' }} ? 'border-red-500 bg-red-50' : 'border-slate-200 bg-slate-50 hover:bg-slate-100')"
                                        class="flex flex-col items-center justify-center gap-2 w-full h-32 border-2 border-dashed rounded-xl cursor-pointer transition-all">
                                        
                                        <template x-if="!selectedFileName">
                                            <div class="flex flex-col items-center">
                                                <i data-lucide="upload-cloud" class="w-8 h-8 text-slate-300"></i>
                                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2">Attach PDF, TXT or DOCX</span>
                                            </div>
                                        </template>

                                        <template x-if="selectedFileName">
                                            <div class="flex flex-col items-center">
                                                <div class="w-10 h-10 bg-emerald-500 text-white rounded-xl flex items-center justify-center mb-2 shadow-lg shadow-emerald-100">
                                                    <i data-lucide="check" class="w-6 h-6"></i>
                                                </div>
                                                <span class="text-[11px] text-emerald-700 font-black truncate max-w-[200px]" x-text="selectedFileName"></span>
                                            </div>
                                        </template>
                                    </label>
                                </div>
                                @error('file')
                                    <p class="mt-2 text-[10px] text-red-500 font-bold ml-1 uppercase tracking-tighter">{{ $message }}</p>
                                @enderror
                            </div>

                            <div x-show="addType === 'link'" x-cloak>
                                <label
                                    class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block ml-1">Sync
                                    URL</label>
                                <input type="url" name="url" value="{{ old('url') }}" placeholder="https://yourbusiness.com/about"
                                    class="w-full px-5 py-4 bg-slate-50 border {{ $errors->has('url') ? 'border-red-500' : 'border-slate-200' }} rounded-xl text-sm font-bold placeholder-slate-300 focus:outline-none focus:border-slate-900 transition-all">
                                @error('url')
                                    <p class="mt-1 text-[10px] text-red-500 font-bold ml-1 uppercase tracking-tighter">{{ $message }}</p>
                                @enderror
                            </div>

                            <div x-show="addType === 'snippet'" x-cloak>
                                <label
                                    class="text-[9px) font-black text-slate-400 uppercase tracking-widest mb-2 block ml-1">Knowledge
                                    Snippet</label>
                                <textarea name="content" rows="4"
                                    placeholder="Paste specific facts or guidelines here..."
                                    class="w-full px-5 py-4 bg-slate-50 border {{ $errors->has('content') ? 'border-red-500' : 'border-slate-200' }} rounded-xl text-sm font-bold placeholder-slate-300 focus:outline-none focus:border-slate-900 transition-all">{{ old('content') }}</textarea>
                                @error('content')
                                    <p class="mt-1 text-[10px] text-red-500 font-bold ml-1 uppercase tracking-tighter">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                            <button type="button" @click="showAddKnowledge = false"
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Cancel</button>
                            <button type="submit"
                                class="px-8 py-4 bg-slate-900 text-white rounded-xl font-bold flex items-center gap-3 shadow-xl hover:bg-black transition-all">
                                <span x-show="!isSyncing" class="text-sm">Initiate Sync</span>
                                <span x-show="isSyncing" x-cloak class="flex items-center gap-2 text-sm">
                                    <div
                                        class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin">
                                    </div>
                                    Indexing Source...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 2. WISDOM INSPECTOR MODAL -->
            <div x-show="showViewModal"
                class="fixed inset-0 flex items-center justify-center p-4 bg-slate-900/98 backdrop-blur-2xl"
                style="z-index: 100001 !important;" x-cloak x-transition.opacity>

                <div class="bg-white w-full max-w-2xl rounded-[2rem] shadow-[0_32px_128px_-10px_rgba(0,0,0,0.6)] overflow-hidden relative"
                    @click.away="showViewModal = false" x-show="showViewModal"
                    x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

                    <template x-if="viewSource">
                        <div class="h-full flex flex-col">
                            <div class="p-8 border-b border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-5">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-slate-900 flex items-center justify-center text-white shadow-lg">
                                        <template x-if="viewSource.type === 'core'"><i data-lucide="shield-check"
                                                class="w-6 h-6"></i></template>
                                        <template x-if="viewSource.type === 'file'"><i data-lucide="file-text"
                                                class="w-6 h-6"></i></template>
                                        <template x-if="viewSource.type === 'link'"><i data-lucide="globe"
                                                class="w-6 h-6"></i></template>
                                        <template x-if="viewSource.type === 'snippet'"><i data-lucide="quote"
                                                class="w-6 h-6"></i></template>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-black text-slate-900 tracking-tight"
                                            x-text="viewSource.title"></h3>
                                        <div class="flex items-center gap-3 mt-1">
                                            <span
                                                class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.1em]"
                                                x-text="viewSource.type + ' registry'"></span>
                                            <div class="w-1 h-1 rounded-full bg-slate-200"></div>
                                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest"
                                                x-text="'Sync Status: ' + viewSource.status"></span>
                                        </div>
                                    </div>
                                </div>
                                <button @click="showViewModal = false"
                                    class="text-slate-300 hover:text-slate-500 p-2 transition-all">
                                    <i data-lucide="x" class="w-6 h-6"></i>
                                </button>
                            </div>

                            <div class="p-8 overflow-y-auto max-h-[60vh] custom-scrollbar">
                                <!-- CORE DATA RENDERER -->
                                <template x-if="viewSource.type === 'core'">
                                    <div class="space-y-10">
                                        <div class="flex items-center gap-10">
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Entity
                                                    Name</span>
                                                <span class="text-xl font-black text-slate-900"
                                                    x-text="JSON.parse(viewSource.content).business_name"></span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Operational
                                                    Category</span>
                                                <span class="text-xl font-black text-slate-900"
                                                    x-text="JSON.parse(viewSource.content).business_type"></span>
                                            </div>
                                        </div>

                                        <div>
                                            <span
                                                class="text-[9px] font-black text-indigo-600 uppercase tracking-[0.2em] mb-4 block">Core
                                                Intelligence: Services Registry</span>
                                            <div class="grid grid-cols-1 gap-3">
                                                <template
                                                    x-for="service in JSON.parse(viewSource.content).services || []">
                                                    <div
                                                        class="flex items-center justify-between p-5 bg-slate-50 rounded-xl border border-transparent hover:border-slate-200 transition-all">
                                                        <div>
                                                            <p class="text-sm font-black text-slate-900"
                                                                x-text="service.name"></p>
                                                            <p class="text-[11px] text-slate-500 font-medium mt-0.5"
                                                                x-text="service.description"></p>
                                                        </div>
                                                        <div class="text-right">
                                                            <p class="text-sm font-black text-slate-900"
                                                                x-text="'KES ' + service.price"></p>
                                                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter"
                                                                x-text="service.price_type"></p>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-6 pt-6 border-t border-slate-100">
                                            <div>
                                                <span
                                                    class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Available
                                                    Hours</span>
                                                <div class="px-4 py-3 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700"
                                                    x-text="JSON.parse(viewSource.content).hours?.schedule || 'Always Active'">
                                                </div>
                                            </div>
                                            <div>
                                                <span
                                                    class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Agent
                                                    Fallback</span>
                                                <div class="px-4 py-3 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700"
                                                    x-text="JSON.parse(viewSource.content).missed_call_action?.type || 'Record Message'">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- TEXT CONTENT RENDERER -->
                                <template x-if="viewSource.type !== 'core'">
                                    <div class="leading-relaxed text-slate-600 whitespace-pre-wrap font-medium text-sm"
                                        x-text="viewSource.content || 'Awaiting initial indexing...'">
                                    </div>
                                </template>
                            </div>

                            <div class="p-8 border-t border-slate-100 flex items-center justify-between bg-slate-50">
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Data
                                        Integrity Check</span>
                                    <span class="text-[10px] text-emerald-600 font-bold">Verified Synchronization
                                        Hub</span>
                                </div>
                                <button @click="showViewModal = false"
                                    class="px-10 py-4 bg-slate-900 text-white rounded-xl font-black text-xs uppercase tracking-widest shadow-xl hover:bg-black transition-all">Dismiss</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>




    @stack('scripts')
</body>

</html>