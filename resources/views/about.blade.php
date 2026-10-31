<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | VoiceAgents</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;family=JetBrains+Mono:wght@400;500&amp;display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .grid-pattern {
            background-size: 40px 40px;
            background-image: linear-gradient(to right, rgba(0, 0, 0, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
            mask-image: radial-gradient(circle at center, black 40%, transparent 100%);
        }

        .noise-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
            opacity: 0.04;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
        }
    </style>
</head>

<body class="min-h-screen flex flex-col font-sans antialiased text-zinc-900 bg-white selection:bg-emerald-500 selection:text-white overflow-x-hidden">

    <div class="noise-overlay"></div>
    <div class="fixed inset-0 pointer-events-none -z-10 grid-pattern"></div>

    <x-navbar />

    <main class="pt-32 pb-24 relative overflow-hidden">
        <!-- Hero: The Vision -->
        <div class="max-w-6xl mx-auto px-6 mb-24 relative z-10">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div>
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-zinc-50 border border-zinc-200 text-zinc-600 text-[11px] uppercase font-bold tracking-widest mb-8">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Our Mission
                    </div>
                    <h1 class="text-6xl md:text-8xl font-bold tracking-tighter text-zinc-950 mb-8 leading-[0.95]">
                        Leveling the playing field<br>
                        <span class="text-zinc-400">for every business.</span>
                    </h1>
                    <p class="text-xl text-zinc-500 leading-relaxed font-regular max-w-2xl">
                        We believe that a professional front desk shouldn't be a luxury. VoiceAgents was built to give
                        every service provider—from Nairobi clinics to global law firms—the power of a 24/7 human-like
                        receptionist.
                    </p>
                </div>

                <!-- The Operational Core (Flow-Centric Redesign) -->
                <div class="relative hidden lg:block h-[600px] w-full py-20">
                    <!-- The Master Pipeline (Visual Anchor) -->
                    <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 pointer-events-none px-20">
                        <div class="relative w-full h-[2px] bg-zinc-100">
                            <!-- Flow Path (SVG) -->
                            <svg class="absolute -top-10 left-0 w-full h-24 overflow-visible" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 40 C 200 40, 300 40, 400 40 S 600 40, 800 40" stroke="url(#flow-track)"
                                    stroke-width="8" stroke-linecap="round" class="opacity-[0.05]" />
                                <path d="M0 40 C 100 40, 300 40, 400 40 S 700 40, 800 40" stroke="url(#flow-pulse)"
                                    stroke-width="2" stroke-linecap="round" class="animate-[flow_5s_linear_infinite]" />

                                <defs>
                                    <linearGradient id="flow-track" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" stop-color="#6366f1" />
                                        <stop offset="100%" stop-color="#10b981" />
                                    </linearGradient>
                                    <linearGradient id="flow-pulse" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" stop-color="#6366f1" stop-opacity="0" />
                                        <stop offset="30%" stop-color="#6366f1" />
                                        <stop offset="70%" stop-color="#10b981" />
                                        <stop offset="100%" stop-color="#10b981" stop-opacity="0" />
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    </div>

                    <!-- The Process Modules -->
                    <div class="relative h-full flex items-center justify-between px-20">

                        <!-- Module 1: Inbound (The Entry) -->
                        <div class="flex flex-col items-center gap-8 -translate-x-12">
                            <div class="relative group">
                                <div
                                    class="absolute -inset-4 bg-indigo-500/5 blur-2xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                </div>
                                <div
                                    class="relative w-32 h-32 bg-white/40 backdrop-blur-md border border-zinc-200 rounded-[2.5rem] shadow-2xl flex items-center justify-center transition-all duration-500 group-hover:-translate-y-2">
                                    <i data-lucide="phone-incoming" class="w-10 h-10 text-indigo-600"></i>
                                    <!-- Capture Ring -->
                                    <div
                                        class="absolute inset-0 rounded-[2.5rem] border border-indigo-500/30 scale-100 group-hover:scale-110 transition-transform">
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <span
                                    class="bg-indigo-50 text-indigo-600 text-[10px] font-bold px-3 py-1 rounded-full border border-indigo-100 uppercase tracking-widest">Entry</span>
                                <div class="mt-3">
                                    <h4 class="text-sm font-bold text-zinc-950">Inbound Signal</h4>
                                    <p class="text-[10px] text-zinc-500 font-mono">Capture_v2.01</p>
                                </div>
                            </div>
                        </div>

                        <!-- Module 2: The Core (The Processor) -->
                        <div class="flex flex-col items-center gap-8">
                            <div class="relative group">
                                <div class="absolute -inset-10 bg-emerald-500/10 blur-3xl opacity-50 animate-pulse">
                                </div>
                                <div
                                    class="relative w-44 h-44 bg-zinc-950 border border-zinc-800 rounded-[3rem] shadow-2xl flex items-center justify-center transition-all duration-700 group-hover:scale-105 overflow-hidden">
                                    <!-- Neural Grid Overlay -->
                                    <div
                                        class="absolute inset-0 opacity-10 bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:8px_8px]">
                                    </div>
                                    <div class="relative z-10 flex flex-col items-center gap-3">
                                        <i data-lucide="brain-circuit" class="w-16 h-16 text-emerald-400"></i>
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                            <span
                                                class="text-[9px] font-mono text-emerald-500 font-bold tracking-[0.2em] uppercase">Processing</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <span
                                    class="bg-zinc-900 text-white text-[10px] font-bold px-3 py-1 rounded-full border border-zinc-800 uppercase tracking-widest">Core</span>
                                <div class="mt-3">
                                    <h4 class="text-sm font-bold text-zinc-950">AI Architect</h4>
                                    <p class="text-[10px] text-zinc-500 font-mono">Neural_Synapse_LVM</p>
                                </div>
                            </div>
                        </div>

                        <!-- Module 3: Outcome (The Result) -->
                        <div class="flex flex-col items-center gap-8 translate-x-12">
                            <div class="relative group">
                                <div
                                    class="absolute -inset-4 bg-emerald-500/5 blur-2xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                </div>
                                <div
                                    class="relative w-48 h-32 bg-white/40 backdrop-blur-md border border-zinc-200 rounded-[2rem] shadow-2xl p-6 flex flex-col justify-center gap-3 transition-all duration-500 group-hover:translate-x-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                                            <i data-lucide="sparkles" class="w-4 h-4 text-emerald-600"></i>
                                        </div>
                                        <span class="text-[11px] font-bold text-zinc-950 tracking-tight">Revenue
                                            Captured</span>
                                    </div>
                                    <div class="h-[1px] w-full bg-zinc-100"></div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                                            <i data-lucide="calendar-check" class="w-4 h-4 text-indigo-600"></i>
                                        </div>
                                        <span class="text-[11px] font-bold text-zinc-950 tracking-tight">Booking
                                            Secured</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <span
                                    class="bg-emerald-50 text-emerald-600 text-[10px] font-bold px-3 py-1 rounded-full border border-emerald-100 uppercase tracking-widest">Output</span>
                                <div class="mt-3">
                                    <h4 class="text-sm font-bold text-zinc-950">Value Chain</h4>
                                    <p class="text-[10px] text-zinc-500 font-mono">Payload::Success</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <style>
                    @keyframes flow {
                        0% {
                            transform: translateX(-100%);
                            opacity: 0;
                        }

                        50% {
                            opacity: 1;
                        }

                        100% {
                            transform: translateX(100%);
                            opacity: 0;
                        }
                    }
                </style>
            </div>
        </div>

        <!-- The Why: The Problem We Solve -->
        <section class="max-w-6xl mx-auto px-6 mb-32 relative z-10">
            <div class="grid md:grid-cols-2 gap-20 items-center">
                <!-- Visual on the Left -->
                <div class="relative order-last md:order-first">
                    <div
                        class="aspect-square bg-white rounded-[2.5rem] border border-zinc-100 overflow-hidden relative shadow-2xl transition-all hover:shadow-emerald-500/5 duration-700">
                        <!-- Abstract Visual: The Revenue Safeguard -->
                        <div class="absolute inset-0 grid-pattern opacity-40"></div>

                        <div class="absolute inset-0 flex items-center justify-center p-12">
                            <div class="relative w-full h-full">
                                <!-- The Safeguard Blueprint (SVG) -->
                                <svg class="absolute inset-0 w-full h-full" viewBox="0 0 400 400" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <!-- The Disrupted Line -->
                                    <path d="M40 200 H 160 M 240 200 H 360" stroke="#f4f4f5" stroke-width="6"
                                        stroke-linecap="round" />
                                    <path d="M160 200 L 200 320 L 240 200" stroke="#f4f4f5" stroke-width="2"
                                        stroke-dasharray="4 4" />

                                    <!-- The Heavy Bridge (The Solution) -->
                                    <path d="M150 200 C 150 120, 250 120, 250 200" stroke="url(#safeguard-gradient)"
                                        stroke-width="8" stroke-linecap="round"
                                        class="animate-[draw_4s_ease-in-out_infinite]" />

                                    <!-- Energy Pulses -->
                                    <circle r="4" fill="#10b981" class="animate-[pulse_2s_infinite]">
                                        <animateMotion path="M150 200 C 150 120, 250 120, 250 200" dur="4s"
                                            repeatCount="indefinite" />
                                    </circle>

                                    <defs>
                                        <linearGradient id="safeguard-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                            <stop offset="0%" stop-color="#6366f1" />
                                            <stop offset="100%" stop-color="#10b981" />
                                        </linearGradient>
                                    </defs>
                                </svg>

                                <!-- Technical Modules -->
                                <!-- 01. The Leak (Rupture) -->
                                <div class="absolute left-0 top-1/2 -translate-y-1/2 flex flex-col items-center gap-3">
                                    <div
                                        class="w-16 h-16 bg-white border border-zinc-200 rounded-2xl shadow-xl flex items-center justify-center group overflow-hidden">
                                        <div
                                            class="absolute inset-0 bg-red-50/50 opacity-0 group-hover:opacity-100 transition-opacity">
                                        </div>
                                        <i data-lucide="phone-missed"
                                            class="w-8 h-8 text-zinc-950 group-hover:text-red-500 transition-colors"></i>
                                    </div>
                                    <span
                                        class="text-[9px] font-mono text-zinc-400 font-bold uppercase tracking-tighter">Drain_Detected</span>
                                </div>

                                <!-- 02. The Core (Processor) -->
                                <div class="absolute left-1/2 top-4 -translate-x-1/2">
                                    <div class="relative group">
                                        <div
                                            class="absolute -inset-6 bg-emerald-500/10 blur-3xl opacity-0 group-hover:opacity-100 transition-opacity">
                                        </div>
                                        <div
                                            class="relative w-24 h-24 bg-zinc-950 border border-zinc-800 rounded-[2rem] shadow-2xl flex items-center justify-center transform group-hover:rotate-12 transition-transform duration-500">
                                            <i data-lucide="shield-check" class="w-10 h-10 text-emerald-400"></i>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <span class="block text-[10px] font-bold text-zinc-900 tracking-widest uppercase">SafeGuard_v4</span>
                                    </div>
                                </div>

                                <!-- 03. The Recovered Resource (The Goal) -->
                                <div
                                    class="absolute right-0 top-1/2 -translate-y-1/2 flex flex-col items-center gap-3 text-right">
                                    <div
                                        class="w-20 h-20 bg-emerald-50 border border-emerald-100 rounded-3xl shadow-xl flex items-center justify-center group">
                                        <i data-lucide="coins"
                                            class="w-10 h-10 text-emerald-600 transition-transform group-hover:scale-110"></i>
                                    </div>
                                    <span
                                        class="text-[9px] font-mono text-emerald-600 font-bold uppercase tracking-tighter">Loop_Closed</span>
                                </div>

                                <!-- Live Metrics -->
                                <div class="absolute bottom-4 inset-x-0 flex justify-between px-4">
                                    <div class="px-2 py-1 bg-white border border-zinc-100 rounded shadow-sm">
                                        <span class="text-[8px] font-mono text-zinc-400">LEAKAGE: <span
                                                class="text-red-500">0.00%</span></span>
                                    </div>
                                    <div class="px-2 py-1 bg-zinc-950 rounded shadow-lg">
                                        <span class="text-[8px] font-mono text-emerald-400 font-bold tracking-widest">RECOVERY:
                                            ACTIVE</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Copy on the Right -->
                <div class="space-y-8">
                    <div>
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-600 text-[10px] uppercase font-bold tracking-widest mb-6">
                            The Challenge
                        </div>
                        <h2 class="text-4xl font-bold text-zinc-950 tracking-tight leading-tight">The "Missed Call" Gap</h2>
                    </div>

                    <p class="text-zinc-600 leading-relaxed text-lg">
                        In most service industries, whoever answers the phone first gets the business. Yet, thousands
                        of SMBs lose up to 30% of their revenue because they are simply too busy working to pick up.
                    </p>
                    <p class="text-zinc-500 leading-relaxed">
                        Standard IVRs ("Press 1 for Sales") are hated by customers and feel cold. Human receptionists
                        are expensive and unavailable after-hours. We bridge this gap with voice AI that doesn't just
                        record messages—it solves problems.
                    </p>

                    <div class="grid grid-cols-3 gap-8 pt-8 border-t border-zinc-100">
                        <div class="space-y-1">
                            <span class="text-3xl font-bold text-zinc-950 tracking-tighter">24/7</span>
                            <span
                                class="block text-[10px] text-zinc-400 uppercase font-bold tracking-widest">Availability</span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-3xl font-bold text-zinc-950 tracking-tighter">0ms</span>
                            <span
                                class="block text-[10px] text-zinc-400 uppercase font-bold tracking-widest">Wait Time</span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-3xl font-bold text-zinc-950 tracking-tighter">98%</span>
                            <span
                                class="block text-[10px] text-zinc-400 uppercase font-bold tracking-widest">Latency Reduction</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- The Local Advantage -->
        <section class="bg-zinc-950 py-32 relative overflow-hidden">
            <!-- Dark Grid -->
            <div class="absolute inset-0 opacity-10 bg-[linear-gradient(to_right,rgba(255,255,255,0.1)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.1)_1px,transparent_1px)] bg-[size:40px_40px]"></div>
            
            <div class="max-w-6xl mx-auto px-6 relative z-10 text-center">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-8 tracking-tight">Built for Africa.<br><span class="text-zinc-600">Scaling Globally.</span></h2>
                <div class="max-w-3xl mx-auto space-y-6 text-zinc-400 text-lg leading-relaxed">
                    <p>
                        We didn't just build another AI wrapper. VoiceAgents is specifically engineered to handle the nuances of growing markets. Our "Nairobi Mode" is optimized for Kenyan accents, local entities like MPESA, and the specific way business is done in East Africa.
                    </p>
                    <p>
                        By combining global state-of-the-art LLMs with localized orchestration, we insure that your customers feel heard, understood, and respected in every single conversation.
                    </p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8 mt-20 text-left">
                    <div class="p-8 border border-zinc-800 rounded-2xl bg-zinc-900/50 backdrop-blur">
                        <i data-lucide="globe-2" class="w-8 h-8 text-emerald-500 mb-6"></i>
                        <h3 class="text-lg font-bold text-white mb-4">Hyper-Localized</h3>
                        <p class="text-sm text-zinc-500 italic">Optimized for regional accents and local payment integrations like M-PESA.</p>
                    </div>
                    <div class="p-8 border border-zinc-800 rounded-2xl bg-zinc-900/50 backdrop-blur">
                        <i data-lucide="shield-check" class="w-8 h-8 text-emerald-500 mb-6"></i>
                        <h3 class="text-lg font-bold text-white mb-4">Privacy First</h3>
                        <p class="text-sm text-zinc-500 italic">End-to-end encryption for transcripts and strict data sovereignty protocols.</p>
                    </div>
                    <div class="p-8 border border-zinc-800 rounded-2xl bg-zinc-900/50 backdrop-blur">
                        <i data-lucide="cpu" class="w-8 h-8 text-emerald-500 mb-6"></i>
                        <h3 class="text-lg font-bold text-white mb-4">Neural Architecture</h3>
                        <p class="text-sm text-zinc-500 italic">Ultra-low latency inference engine ensuring natural, zero-lag conversations.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="max-w-6xl mx-auto px-6 py-32 text-center">
            <h2 class="text-3xl font-bold text-zinc-900 mb-6">Join the infrastructure of tomorrow.</h2>
            <p class="text-zinc-500 mb-12 max-w-xl mx-auto">Stop missing revenue. Start deploying intelligence.</p>
            <div class="flex items-center justify-center gap-4">
                <a href="{{ route('register') }}"
                    class="h-14 px-8 bg-zinc-950 hover:bg-zinc-800 text-white rounded-xl text-base font-semibold transition-all shadow-xl shadow-zinc-200/50 flex items-center gap-2 group">
                    Get Started
                    <i data-lucide="arrow-right" class="w-4 h-4 text-emerald-400 group-hover:translate-x-1 transition-transform"></i>
                </a>
                <a href="{{ route('home') }}"
                    class="h-14 px-8 bg-white border border-zinc-200 text-zinc-900 rounded-xl text-base font-medium hover:bg-zinc-50 transition-all flex items-center">
                    Back to Home
                </a>
            </div>
        </section>
    </main>

    <x-footer />

    <script>
        lucide.createIcons();
    </script>

</body>

</html>
