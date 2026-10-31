<footer class="bg-zinc-950 border-t border-zinc-900 relative overflow-hidden">
    <!-- Background Texture -->
    <div
        class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:24px_24px] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]">
    </div>

    <div class="max-w-6xl mx-auto px-6 pt-20 pb-10 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-12 md:gap-8">
            <!-- Brand Column (Span 4) -->
            <div class="md:col-span-4 space-y-6">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-white rounded-xl flex items-center justify-center p-1.5 shadow-lg shadow-white/5">
                        <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                            <rect x="8" y="14" width="4" height="12" rx="2" fill="#09090b" />
                            <rect x="18" y="8" width="4" height="24" rx="2" fill="#09090b" />
                            <rect x="28" y="14" width="4" height="12" rx="2" fill="#09090b" />
                            <path d="M10 20 L20 20 L30 20" stroke="#10b981" stroke-width="2" stroke-linecap="round" opacity="0.8" />
                        </svg>
                    </div>
                    <span class="text-base font-bold tracking-tight text-white">VoiceAgents</span>
                </div>
                <p class="text-sm text-zinc-400 leading-relaxed max-w-sm">
                    Deploy AI receptionists that sound human, understand context, and drive revenue.
                    Built for the next generation of African businesses.
                </p>
                <div class="flex items-center gap-4 pt-2">
                    <a href="#"
                        class="w-8 h-8 rounded-full bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-400 hover:text-white hover:border-zinc-700 transition-colors">
                        <i data-lucide="twitter" class="w-4 h-4"></i>
                    </a>
                    <a href="#"
                        class="w-8 h-8 rounded-full bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-400 hover:text-white hover:border-zinc-700 transition-colors">
                        <i data-lucide="linkedin" class="w-4 h-4"></i>
                    </a>
                    <a href="#"
                        class="w-8 h-8 rounded-full bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-400 hover:text-white hover:border-zinc-700 transition-colors">
                        <i data-lucide="github" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            <!-- Links Column 1: Product -->
            <div class="md:col-span-2 md:col-start-6">
                <h4 class="text-sm font-bold text-white mb-6">Product</h4>
                <ul class="space-y-3">
                    <li><a href="#" class="text-sm text-zinc-500 hover:text-white transition-colors">Features</a>
                    </li>
                    <li><a href="#" class="text-sm text-zinc-500 hover:text-white transition-colors">Pricing</a>
                    </li>
                    <li><a href="#" class="text-sm text-zinc-500 hover:text-white transition-colors">Live Demo</a>
                    </li>
                    <li><a href="#" class="text-sm text-zinc-500 hover:text-white transition-colors">Changelog</a>
                    </li>
                </ul>
            </div>

            <!-- Links Column 2: Resources -->
            <div class="md:col-span-2">
                <h4 class="text-sm font-bold text-white mb-6">Resources</h4>
                <ul class="space-y-3">
                    <li><a href="#" class="text-sm text-zinc-500 hover:text-white transition-colors">Documentation</a>
                    </li>
                    <li><a href="#" class="text-sm text-zinc-500 hover:text-white transition-colors">API
                            Reference</a></li>
                    <li><a href="#" class="text-sm text-zinc-500 hover:text-white transition-colors">Success
                            Stories</a></li>
                    <li><a href="#" class="text-sm text-zinc-500 hover:text-white transition-colors">Community</a>
                    </li>
                </ul>
            </div>

            <!-- Links Column 3: Company -->
            <div class="md:col-span-2">
                <h4 class="text-sm font-bold text-white mb-6">Company</h4>
                <ul class="space-y-3">
                    <li><a href="{{ route('about') }}"
                            class="text-sm text-zinc-500 hover:text-white transition-colors">About</a>
                    </li>
                    <li><a href="#" class="text-sm text-zinc-500 hover:text-white transition-colors">Careers</a>
                    </li>
                    <li><a href="{{ route('privacy') }}"
                            class="text-sm text-zinc-500 hover:text-white transition-colors">Privacy</a>
                    </li>
                    <li><a href="{{ route('terms') }}"
                            class="text-sm text-zinc-500 hover:text-white transition-colors">Terms</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-zinc-900 mt-16 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-xs text-zinc-500">
                © {{ date('Y') }} VoiceAgents Inc. All rights reserved.
            </p>
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-zinc-900 border border-zinc-800">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-[10px] font-bold text-zinc-300 tracking-wide uppercase">Systems Operational</span>
            </div>
        </div>
    </div>
</footer>