<nav class="fixed top-0 w-full z-50 border-b border-zinc-200 bg-white/80 backdrop-blur-md">
    <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 hover:opacity-80 transition-opacity">
            <div class="w-8 h-8 bg-zinc-950 rounded-xl flex items-center justify-center p-1.5 shadow-lg shadow-zinc-200/50">
                <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                    <rect x="8" y="14" width="4" height="12" rx="2" fill="white" />
                    <rect x="18" y="8" width="4" height="24" rx="2" fill="white" />
                    <rect x="28" y="14" width="4" height="12" rx="2" fill="white" />
                    <path d="M10 20 L20 20 L30 20" stroke="#10b981" stroke-width="2" stroke-linecap="round" opacity="0.5" />
                </svg>
            </div>
            <span class="text-base font-bold tracking-tight text-zinc-950">VoiceAgents</span>
        </a>
        <div class="flex items-center gap-8">
            <a href="{{ route('home') }}"
                class="text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">Home</a>
            <a href="{{ route('how-it-works') }}"
                class="text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">How it Works</a>
            <a href="{{ route('pricing') }}"
                class="text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">Pricing</a>
            <a href="{{ route('about') }}"
                class="text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">About</a>
            <div class="h-4 w-px bg-zinc-200"></div>
            <!-- Updated Login Link to route -->
            @auth
                <!-- Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false"
                        class="flex items-center gap-2 text-sm font-medium text-zinc-600 hover:text-zinc-900 transition-colors">
                        <span>{{ Auth::user()->name }}</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform"
                            :class="{ 'rotate-180': open }"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95" style="display: none;"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl shadow-zinc-200/50 border border-zinc-100 py-1 z-50">

                        <a href="{{ route('dashboard') }}"
                            class="block px-4 py-2 text-xs text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900">
                            Command Center
                        </a>
                        <a href="{{ route('profile.edit') }}"
                            class="block px-4 py-2 text-xs text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900">
                            Profile Settings
                        </a>
                        <div class="border-t border-zinc-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-xs text-red-600 hover:bg-red-50">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 transition-colors">Login</a>
                <a href="{{ route('register') }}"
                    class="bg-zinc-900 text-white text-sm font-bold px-4 py-2 rounded-lg hover:bg-zinc-800 transition-all hover:shadow-lg hover:-translate-y-0.5">
                    Start Building
                </a>
            @endauth
        </div>
    </div>
</nav>