<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VoiceAgents | Aligned</title>
    <!-- Replaced CDN with Local Vite Pipeline -->
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
            font-feature-settings: "cv02", "cv03", "cv04", "cv11";
            -webkit-font-smoothing: antialiased;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Animations */
        @keyframes subtle-float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-4px);
            }
        }

        @keyframes voice-bar-anim {

            0%,
            100% {
                height: 4px;
                opacity: 0.5;
            }

            50% {
                height: 16px;
                opacity: 1;
            }
        }

        .v-bar {
            animation: voice-bar-anim 1s ease-in-out infinite;
        }

        .v-bar:nth-child(odd) {
            animation-duration: 0.8s;
        }

        .v-bar:nth-child(2) {
            animation-duration: 1.2s;
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Premium Noise Overlay */
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

        /* Subtle Grid Background */
        .grid-pattern {
            background-size: 40px 40px;
            background-image: linear-gradient(to right, rgba(0, 0, 0, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
            mask-image: radial-gradient(circle at center, black 40%, transparent 100%);
        }
    </style>
</head>

<body class="min-h-screen flex flex-col font-sans antialiased text-zinc-900 bg-white selection:bg-emerald-500 selection:text-white overflow-x-hidden">

    <div class="noise-overlay"></div>
    <div class="fixed inset-0 pointer-events-none -z-10 grid-pattern"></div>

    <x-navbar />

    <main class="pt-32 pb-0 relative">
        <!-- Hero Section: Premium Monochrome -->
        <div class="max-w-6xl mx-auto px-6 mb-16 relative z-10 w-full">
            <div class="max-w-4xl mx-auto text-center flex flex-col items-center">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-zinc-50 border border-zinc-200 text-zinc-600 text-[11px] uppercase font-bold tracking-widest mb-8 hover:border-zinc-300 transition-colors cursor-default">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></span>
                    Voice AI v2.0
                </div>

                <h1 class="text-6xl md:text-8xl font-bold tracking-tighter text-zinc-950 mb-8 leading-[0.95]">
                    Automate reception<br>
                    <span class="text-zinc-400">with a conversation.</span>
                </h1>

                <p class="text-xl text-zinc-500 leading-relaxed max-w-2xl mb-12 font-regular">
                    Skip the complex dashboards. Chat with our Builder Agent to deploy a <span
                        class="text-zinc-900 font-medium">human-like Voice AI</span> for your business in 2 minutes.
                </p>

                <div class="flex items-center gap-4 w-full justify-center">
                    <a href="{{ route('register') }}"
                        class="h-14 px-8 bg-zinc-950 hover:bg-zinc-800 text-white rounded-xl text-base font-semibold transition-all shadow-xl shadow-zinc-200/50 hover:shadow-2xl hover:-translate-y-0.5 flex items-center gap-2 group">
                        Start Interview
                        <i data-lucide="arrow-right"
                            class="w-4 h-4 text-emerald-400 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                    <a href="{{ route('how-it-works') }}"
                        class="h-14 px-8 bg-white border border-zinc-200 hover:border-zinc-300 hover:bg-zinc-50 text-zinc-900 rounded-xl text-base font-medium transition-all flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-zinc-400"></i>
                        Learn More
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Interface Visual -->
        <div class="max-w-6xl mx-auto px-6 mb-24 relative z-10 w-full">
            <div
                class="rounded-2xl border border-zinc-200 bg-white shadow-2xl shadow-zinc-200/50 grid lg:grid-cols-2 overflow-hidden h-[600px] ring-1 ring-zinc-950/5">

                <!-- Left Column: The Chat (Input) -->
                <div
                    class="bg-zinc-50 border-b lg:border-b-0 lg:border-r border-zinc-200 flex flex-col h-full relative">
                    <!-- Header -->
                    <div class="h-14 border-b border-zinc-200 px-6 flex items-center justify-between bg-white">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded bg-zinc-900 flex items-center justify-center">
                                <i data-lucide="bot" class="w-4 h-4 text-white"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-zinc-900">Architect</span>
                                <span class="text-[10px] text-zinc-500">Builder Agent</span>
                            </div>
                        </div>
                        <span
                            class="text-[10px] text-emerald-700 font-bold bg-emerald-50 px-2 py-1 rounded border border-emerald-100 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Online
                        </span>
                    </div>

                    <!-- Chat Area -->
                    <div class="flex-1 p-6 overflow-y-auto space-y-6">
                        <!-- AI Message -->
                        <div class="flex gap-4">
                            <div
                                class="w-8 h-8 rounded bg-white border border-zinc-200 flex-shrink-0 flex items-center justify-center shadow-sm">
                                <i data-lucide="sparkles" class="w-4 h-4 text-zinc-900"></i>
                            </div>
                            <div class="space-y-1">
                                <div
                                    class="bg-white border border-zinc-200 p-4 rounded-2xl rounded-tl-none shadow-sm max-w-sm">
                                    <p class="text-sm text-zinc-700 leading-relaxed">Let's set up your phone agent. What
                                        is the name of your business and what services do you offer?</p>
                                </div>
                                <span class="text-[10px] text-zinc-400 pl-1 font-mono">10:42 AM</span>
                            </div>
                        </div>

                        <!-- User Message -->
                        <div class="flex flex-col items-end gap-1">
                            <div class="bg-zinc-900 text-white p-4 rounded-2xl rounded-tr-none shadow-md max-w-sm">
                                <p class="text-sm leading-relaxed">It's "Nairobi Dentists". We do checkups, cleaning,
                                    and root canals.</p>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] text-zinc-400">Read</span>
                                <i data-lucide="check" class="w-3 h-3 text-emerald-500"></i>
                            </div>
                        </div>

                        <!-- AI Message -->
                        <div class="flex gap-4">
                            <div
                                class="w-8 h-8 rounded bg-white border border-zinc-200 flex-shrink-0 flex items-center justify-center shadow-sm">
                                <i data-lucide="sparkles" class="w-4 h-4 text-zinc-900"></i>
                            </div>
                            <div class="space-y-1">
                                <div
                                    class="bg-white border border-zinc-200 p-4 rounded-2xl rounded-tl-none shadow-sm max-w-sm">
                                    <p class="text-sm text-zinc-700 leading-relaxed">Got it. Since you are a dental
                                        clinic, I need to know your consultation fees. How much for a basic checkup?</p>
                                </div>
                                <div class="flex gap-2 mt-2">
                                    <span
                                        class="px-2 py-1 bg-zinc-100 text-zinc-600 text-[10px] font-medium rounded border border-zinc-200 flex items-center gap-1">
                                        <i data-lucide="database" class="w-3 h-3"></i> Context: Dental
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- User Message -->
                        <div class="flex flex-col items-end gap-1">
                            <div class="bg-zinc-900 text-white p-4 rounded-2xl rounded-tr-none shadow-md max-w-sm">
                                <p class="text-sm leading-relaxed">Checkups are 2000 KES. Cleaning is 5000.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="p-4 bg-white border-t border-zinc-200">
                        <div class="relative">
                            <input type="text" placeholder="Type your reply..."
                                class="w-full h-12 bg-zinc-50 border border-zinc-200 rounded-xl pl-4 pr-12 text-sm focus:outline-none focus:border-zinc-400 focus:bg-white transition-all placeholder:text-zinc-400">
                            <button class="absolute right-3 top-3 text-zinc-400 hover:text-zinc-900 transition-colors">
                                <i data-lucide="send" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column: The Result (Output) -->
                <div class="bg-white flex flex-col h-full relative overflow-hidden">
                    <!-- Background Grid -->
                    <div class="absolute inset-0 grid-pattern opactiy-50"></div>

                    <!-- Header -->
                    <div
                        class="h-14 border-b border-zinc-100 px-6 flex items-center justify-between relative z-10 bg-white/80 backdrop-blur">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-xs font-bold text-zinc-900 tracking-tight">Live Preview</span>
                        </div>
                        <div class="flex gap-1.5">
                            <div class="w-2 h-2 rounded-full bg-zinc-200"></div>
                            <div class="w-2 h-2 rounded-full bg-zinc-200"></div>
                            <div class="w-2 h-2 rounded-full bg-zinc-200"></div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 flex flex-col items-center justify-center p-8 relative z-10">

                        <!-- The Code Generated Block -->
                        <div
                            class="absolute top-6 right-6 bg-zinc-950 text-zinc-400 p-5 rounded-xl shadow-2xl max-w-[200px] border border-zinc-800 text-[10px] font-mono leading-5 opacity-0 lg:opacity-100 transition-opacity duration-700 animate-subtle-float">
                            <span class="text-emerald-400">const</span> pricing = {<br>
                            &nbsp;&nbsp;checkup: <span class="text-white">2000</span>,<br>
                            &nbsp;&nbsp;cleaning: <span class="text-white">5000</span><br>
                            };<br>
                            <span class="text-zinc-600">// Auto-generated</span>
                        </div>

                        <!-- Phone Card -->
                        <div
                            class="w-72 bg-white rounded-[2rem] shadow-2xl border-[6px] border-zinc-950 overflow-hidden relative">
                            <!-- Dynamic Island/Notch -->
                            <div
                                class="absolute top-0 left-1/2 -translate-x-1/2 w-24 h-5 bg-zinc-950 rounded-b-xl z-20">
                            </div>

                            <!-- Phone Header -->
                            <div class="bg-zinc-50 pt-8 pb-4 border-b border-zinc-100 flex flex-col items-center gap-2">
                                <div
                                    class="w-16 h-16 rounded-full bg-zinc-200 flex items-center justify-center text-zinc-600 font-bold text-lg border-2 border-white shadow-sm">
                                    ND
                                </div>
                                <div class="text-center">
                                    <div class="text-sm font-bold text-zinc-900">Nairobi Dentists</div>
                                    <div class="text-[10px] text-zinc-500 font-medium tracking-wide">00:12 • INCOMING
                                        CALL</div>
                                </div>
                            </div>

                            <!-- Phone Body -->
                            <div class="p-6 flex flex-col items-center gap-8 bg-white min-h-[200px]">
                                <div class="flex items-center gap-1.5 h-8">
                                    <div class="w-1.5 bg-zinc-900 rounded-full v-bar"></div>
                                    <div class="w-1.5 bg-zinc-900 rounded-full v-bar"></div>
                                    <div class="w-1.5 bg-zinc-900 rounded-full v-bar"></div>
                                    <div class="w-1.5 bg-zinc-900 rounded-full v-bar"></div>
                                    <div class="w-1.5 bg-zinc-900 rounded-full v-bar"></div>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-medium text-zinc-900 leading-snug">"Yes, a checkup is 2,000
                                        KES. Shall I book it?"</p>
                                    <p
                                        class="text-[10px] text-emerald-600 mt-2 font-medium bg-emerald-50 px-2 py-1 rounded-full inline-block">
                                        Responding from Knowledge Base</p>
                                </div>
                            </div>

                            <!-- Phone Actions -->
                            <div class="p-4 bg-zinc-50 grid grid-cols-2 gap-3">
                                <button
                                    class="h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center hover:bg-red-200 transition-colors">
                                    <i data-lucide="phone-off" class="w-5 h-5 fill-current"></i>
                                </button>
                                <button
                                    class="h-12 bg-zinc-900 text-white rounded-full flex items-center justify-center hover:bg-zinc-800 transition-colors">
                                    <i data-lucide="mic-off" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- System Rack: Enterprise Capabilities -->
    <section class="border-t border-zinc-900 bg-zinc-950 py-32 relative overflow-hidden">
        <!-- Subtle Ambient Glows -->
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-emerald-500/5 rounded-full blur-[128px]"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-indigo-500/5 rounded-full blur-[128px]"></div>

        <div class="max-w-6xl mx-auto px-6 relative z-10">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-20 gap-8">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] uppercase font-bold tracking-[0.2em] mb-6">
                        System Configuration
                    </div>
                    <h2 class="text-4xl md:text-5xl font-bold text-white tracking-tighter mb-4 leading-tight">Enterprise Capabilities</h2>
                    <p class="text-lg text-zinc-400 max-w-lg font-medium">Standard modules included in every deployment to ensure peak operational efficiency.</p>
                </div>
                <a href="{{ route('how-it-works') }}#features"
                    class="h-12 px-6 bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-zinc-300 hover:text-white rounded-xl text-sm font-bold transition-all flex items-center gap-2 group">
                    Full Spec Sheet
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>

            <div class="grid md:grid-cols-3 gap-1 shadow-[0_0_100px_rgba(0,0,0,0.5)] border border-zinc-800 rounded-[2rem] overflow-hidden bg-zinc-800/20 backdrop-blur-sm">
                <!-- Module 01: Instant Deployment -->
                <div class="group bg-zinc-950 p-10 hover:bg-zinc-900/50 transition-all duration-300 border-r border-b border-zinc-800 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-10">
                        <span class="text-[9px] font-mono text-zinc-500 font-bold tracking-widest uppercase">[CORE_SYNC: 01]</span>
                        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/5 border border-emerald-500/10">
                            <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[8px] font-bold text-emerald-500 uppercase tracking-tighter">Live</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                        <i data-lucide="zap" class="w-6 h-6 text-emerald-500"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-4">Instant Deployment</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed group-hover:text-zinc-300 transition-colors">
                        From interview to live production in less than 5 minutes. No coding or infrastructure management required.
                    </p>
                </div>

                <!-- Module 02: Smart Scheduling -->
                <div class="group bg-zinc-950 p-10 hover:bg-zinc-900/50 transition-all duration-300 border-r border-b border-zinc-800 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-10">
                        <span class="text-[9px] font-mono text-zinc-500 font-bold tracking-widest uppercase">[CAL_BRIDGE: 02]</span>
                        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/5 border border-emerald-500/10">
                            <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[8px] font-bold text-emerald-500 uppercase tracking-tighter">Active</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-indigo-500/10 border border-indigo-500/20 rounded-xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                        <i data-lucide="calendar" class="w-6 h-6 text-indigo-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-4">Smart Scheduling</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed group-hover:text-zinc-300 transition-colors">
                        Automated Google Calendar synchronization. The agent manages availability and books slots in real-time.
                    </p>
                </div>

                <!-- Module 03: Escalation -->
                <div class="group bg-zinc-950 p-10 hover:bg-zinc-900/50 transition-all duration-300 border-b border-zinc-800 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-10">
                        <span class="text-[9px] font-mono text-zinc-500 font-bold tracking-widest uppercase">[SEC_BYPASS: 03]</span>
                        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/5 border border-emerald-500/10">
                            <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[8px] font-bold text-emerald-500 uppercase tracking-tighter">Secure</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-white/5 border border-white/10 rounded-xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                        <i data-lucide="shield" class="w-6 h-6 text-zinc-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-4">Escalation Protocols</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed group-hover:text-zinc-300 transition-colors">
                        Intelligent keyword detection (e.g., "Emergency") triggers immediate call bypass to human staff.
                    </p>
                </div>

                <!-- Module 04: Local Knowledge -->
                <div class="group bg-zinc-950 p-10 hover:bg-zinc-900/50 transition-all duration-300 border-r border-zinc-800 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-10">
                        <span class="text-[9px] font-mono text-zinc-500 font-bold tracking-widest uppercase">[KEN_LOC: 04]</span>
                        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/5 border border-emerald-500/10">
                            <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[8px] font-bold text-emerald-500 uppercase tracking-tighter">Optimized</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-white/5 border border-white/10 rounded-xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                        <i data-lucide="globe" class="w-6 h-6 text-zinc-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-4">Local Context</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed group-hover:text-zinc-300 transition-colors">
                        Trained on proprietary local datasets (M-PESA, estates, landmarks) for natural Kenyan conversations.
                    </p>
                </div>

                <!-- Module 05: Analytics -->
                <div class="group bg-zinc-950 p-10 hover:bg-zinc-900/50 transition-all duration-300 border-r border-zinc-800 flex flex-col h-full">
                    <div class="flex items-center justify-between mb-10">
                        <span class="text-[9px] font-mono text-zinc-500 font-bold tracking-widest uppercase">[DATA_STRM: 05]</span>
                        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/5 border border-emerald-500/10">
                            <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[8px] font-bold text-emerald-500 uppercase tracking-tighter">Live</span>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform">
                        <i data-lucide="bar-chart-3" class="w-6 h-6 text-emerald-500"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-4">Telemetry Stream</h3>
                    <p class="text-sm text-zinc-400 leading-relaxed group-hover:text-zinc-300 transition-colors">
                        Full transcripts and sentiment analysis for every inbound interaction. identify pattern-of-loss instantly.
                    </p>
                </div>

                <!-- Module 06: CTA -->
                <div class="group bg-emerald-600 p-10 hover:bg-emerald-500 transition-all duration-300 flex flex-col justify-center items-center text-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_rgba(255,255,255,0.2)_0%,_transparent_70%)] opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative z-10">
                        <div class="text-[9px] font-mono text-emerald-200 font-bold tracking-[0.3em] uppercase mb-6">[SYSTEM_AUTH: 06]</div>
                        <h3 class="text-2xl font-bold text-white mb-6">Redefine Your Reception</h3>
                        <a href="{{ route('register') }}"
                            class="inline-flex h-12 px-8 bg-zinc-950 text-white rounded-xl text-sm font-bold items-center hover:bg-zinc-900 transition-all shadow-2xl">
                            Start Building Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: Audio Demos -->
    <section class="py-24 bg-white border-t border-zinc-200 relative overflow-hidden">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-zinc-900 tracking-tight mb-4">Hear the difference.</h2>
                <p class="text-zinc-500 max-w-2xl mx-auto font-medium">Standard IVR vs. our Neural Voice Engine.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <!-- The Old Way -->
                <div
                    class="p-8 rounded-3xl bg-zinc-50 border border-zinc-200 opacity-60 grayscale hover:opacity-100 hover:grayscale-0 transition-all duration-500">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-zinc-200 flex items-center justify-center">
                            <i data-lucide="tape" class="w-6 h-6 text-zinc-500"></i>
                        </div>
                        <h3 class="font-bold text-lg text-zinc-900">Standard Voicemail</h3>
                    </div>
                    <div class="bg-white p-2 rounded-xl border border-zinc-200 shadow-sm mb-6">
                        <div class="flex items-center gap-3">
                            <button
                                class="w-10 h-10 rounded-full bg-zinc-100 flex items-center justify-center hover:bg-zinc-200 transition-colors">
                                <i data-lucide="play" class="w-4 h-4 text-zinc-600 fill-current ml-0.5"></i>
                            </button>
                            <div class="flex-1 h-8 flex items-center gap-0.5 opacity-20">
                                <div class="w-1 h-3 bg-zinc-900 rounded-full"></div>
                                <div class="w-1 h-5 bg-zinc-900 rounded-full"></div>
                                <div class="w-1 h-2 bg-zinc-900 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-zinc-500 italic font-medium">"Please leave a message after the tone..."</p>
                </div>

                <!-- VoiceAgents -->
                <div
                    class="p-8 rounded-3xl bg-zinc-900 text-white shadow-2xl shadow-zinc-900/10 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-zinc-800 to-zinc-950"></div>

                    <!-- Green Glow -->
                    <div
                        class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2">
                    </div>

                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-8">
                            <div
                                class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
                                <i data-lucide="sparkles" class="w-6 h-6 text-emerald-400"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-white">Neural Voice</h3>
                                <div class="text-[10px] text-emerald-400 font-bold uppercase tracking-widest">Enhanced
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-white/5 p-2 rounded-xl border border-white/10 mb-6 backdrop-blur-sm group-hover:bg-white/10 transition-colors">
                            <div class="flex items-center gap-3">
                                <button
                                    class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center hover:bg-emerald-400 transition-colors shadow-lg shadow-emerald-500/20">
                                    <i data-lucide="play" class="w-4 h-4 text-white fill-current ml-0.5"></i>
                                </button>
                                <div class="flex-1 h-8 flex items-center gap-0.5">
                                    <!-- Animated Bars White -->
                                    <div class="w-1 bg-white rounded-full animate-[voice-bar-anim_1s_ease-in-out_infinite]"
                                        style="height: 12px; animation-delay: 0.1s"></div>
                                    <div class="w-1 bg-white rounded-full animate-[voice-bar-anim_1s_ease-in-out_infinite]"
                                        style="height: 24px; animation-delay: 0.2s"></div>
                                    <div class="w-1 bg-white rounded-full animate-[voice-bar-anim_1s_ease-in-out_infinite]"
                                        style="height: 16px; animation-delay: 0.3s"></div>
                                    <div class="w-1 bg-white rounded-full animate-[voice-bar-anim_1s_ease-in-out_infinite]"
                                        style="height: 28px; animation-delay: 0.1s"></div>
                                    <div class="w-1 bg-white rounded-full animate-[voice-bar-anim_1s_ease-in-out_infinite]"
                                        style="height: 12px; animation-delay: 0.4s"></div>
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-zinc-300 leading-relaxed">"Hi! Thanks for calling Nairobi Dentists. I can
                            help you book a checkup. Would Tuesday work?"</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: Use Cases -->
    <section class="py-24 bg-zinc-50 border-t border-zinc-200">
        <div class="max-w-6xl mx-auto px-6">
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-zinc-900 tracking-tight mb-4">Built for your industry.</h2>
                <p class="text-zinc-500 max-w-2xl">Tailored conversation flows for specific business needs.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div
                    class="bg-white p-2 rounded-2xl border border-zinc-200 hover:border-emerald-500/30 transition-colors group">
                    <div
                        class="h-48 bg-zinc-100 rounded-xl mb-4 relative overflow-hidden flex items-center justify-center">
                        <i data-lucide="stethoscope"
                            class="w-12 h-12 text-zinc-400 group-hover:text-emerald-500 group-hover:scale-110 transition-all duration-500"></i>
                    </div>
                    <div class="px-4 pb-4">
                        <h3 class="font-bold text-zinc-900 mb-1">Medical & Dental</h3>
                        <p class="text-sm text-zinc-500 leading-relaxed mb-4 min-h-[40px]">Handle bookings and FAQs
                            while with patients.</p>
                        <ul class="space-y-2">
                            <li class="flex items-center gap-2 text-xs text-zinc-600 font-medium">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-500 font-bold"></i>
                                Calendar Sync
                            </li>
                            <li class="flex items-center gap-2 text-xs text-zinc-600 font-medium">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-500 font-bold"></i>
                                Pricing Info
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Card 2 -->
                <div
                    class="bg-white p-2 rounded-2xl border border-zinc-200 hover:border-emerald-500/30 transition-colors group">
                    <div
                        class="h-48 bg-zinc-100 rounded-xl mb-4 relative overflow-hidden flex items-center justify-center">
                        <i data-lucide="wrench"
                            class="w-12 h-12 text-zinc-400 group-hover:text-emerald-500 group-hover:scale-110 transition-all duration-500"></i>
                    </div>
                    <div class="px-4 pb-4">
                        <h3 class="font-bold text-zinc-900 mb-1">Auto & Trades</h3>
                        <p class="text-sm text-zinc-500 leading-relaxed mb-4 min-h-[40px]">Quote repairs without
                            stepping away from the job.</p>
                        <ul class="space-y-2">
                            <li class="flex items-center gap-2 text-xs text-zinc-600 font-medium">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-500 font-bold"></i> Repair
                                Quotes
                            </li>
                            <li class="flex items-center gap-2 text-xs text-zinc-600 font-medium">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-500 font-bold"></i>
                                Location Pin
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Card 3 -->
                <div
                    class="bg-white p-2 rounded-2xl border border-zinc-200 hover:border-emerald-500/30 transition-colors group">
                    <div
                        class="h-48 bg-zinc-100 rounded-xl mb-4 relative overflow-hidden flex items-center justify-center">
                        <i data-lucide="scale"
                            class="w-12 h-12 text-zinc-400 group-hover:text-emerald-500 group-hover:scale-110 transition-all duration-500"></i>
                    </div>
                    <div class="px-4 pb-4">
                        <h3 class="font-bold text-zinc-900 mb-1">Legal & Professional</h3>
                        <p class="text-sm text-zinc-500 leading-relaxed mb-4 min-h-[40px]">Screen cases and schedule
                            consultations 24/7.</p>
                        <ul class="space-y-2">
                            <li class="flex items-center gap-2 text-xs text-zinc-600 font-medium">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-500 font-bold"></i> Client
                                Intake
                            </li>
                            <li class="flex items-center gap-2 text-xs text-zinc-600 font-medium">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-500 font-bold"></i> VIP
                                Routing
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: How It Works -->
    <section class="py-24 bg-white border-t border-zinc-200">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-20">
                <h2 class="text-3xl font-bold text-zinc-900 tracking-tight mb-4">Go live in minutes.</h2>
                <p class="text-zinc-500 font-medium">No complex flowcharts. Just a conversation.</p>
            </div>

            <div class="relative">
                <!-- Connecting Line -->
                <div class="absolute top-8 left-0 w-full h-px bg-zinc-100 hidden md:block z-0"></div>

                <div class="grid md:grid-cols-3 gap-12 relative z-10">
                    <!-- Step 1 -->
                    <div class="flex flex-col items-center text-center group">
                        <div
                            class="w-16 h-16 bg-white border border-zinc-200 rounded-2xl flex items-center justify-center mb-6 shadow-sm group-hover:border-zinc-900 group-hover:bg-zinc-900 group-hover:text-white transition-all duration-300">
                            <span class="font-mono font-bold text-xl">1</span>
                        </div>
                        <h3 class="font-bold text-zinc-900 mb-2">Train your Agent</h3>
                        <p class="text-sm text-zinc-500 leading-relaxed max-w-xs">Chat with our Builder AI to define
                            your services, pricing, and business rules.</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex flex-col items-center text-center group">
                        <div
                            class="w-16 h-16 bg-white border border-zinc-200 rounded-2xl flex items-center justify-center mb-6 shadow-sm group-hover:border-zinc-900 group-hover:bg-zinc-900 group-hover:text-white transition-all duration-300">
                            <span class="font-mono font-bold text-xl">2</span>
                        </div>
                        <h3 class="font-bold text-zinc-900 mb-2">Get a Number</h3>
                        <p class="text-sm text-zinc-500 leading-relaxed max-w-xs">We instantly provision a dedicated
                            phone number for your business.</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex flex-col items-center text-center group">
                        <div
                            class="w-16 h-16 bg-white border border-zinc-200 rounded-2xl flex items-center justify-center mb-6 shadow-sm group-hover:border-zinc-900 group-hover:bg-zinc-900 group-hover:text-white transition-all duration-300">
                            <span class="font-mono font-bold text-xl">3</span>
                        </div>
                        <h3 class="font-bold text-zinc-900 mb-2">Start Receiving Calls</h3>
                        <p class="text-sm text-zinc-500 leading-relaxed max-w-xs">Forwards to your mobile or handle
                            directly. View logs in real-time.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: FAQ -->
    <section class="py-24 bg-zinc-50 border-t border-zinc-200">
        <div class="max-w-3xl mx-auto px-6">
            <h2 class="text-3xl font-bold text-zinc-900 tracking-tight mb-12 text-center">Frequently asked questions
            </h2>

            <div class="space-y-4">
                <!-- FAQ Item -->
                <div class="bg-white rounded-xl border border-zinc-200 p-1 shadow-sm hover:border-zinc-300 transition-colors"
                    x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full text-left p-5">
                        <span class="font-semibold text-sm text-zinc-900">Does it sound robotic?</span>
                        <i data-lucide="plus" class="w-4 h-4 text-zinc-400 transition-transform"
                            :class="{'rotate-45': open}"></i>
                    </button>
                    <div x-show="open" class="px-5 pb-5 text-sm text-zinc-600 leading-relaxed" style="display: none;">
                        Not at all. We use the latest ultra-low latency voice models. They breathe, pause, and speak
                        with natural intonation indistinguishable from human speech.
                    </div>
                </div>

                <!-- FAQ Item -->
                <div class="bg-white rounded-xl border border-zinc-200 p-1 shadow-sm hover:border-zinc-300 transition-colors"
                    x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full text-left p-5">
                        <span class="font-semibold text-sm text-zinc-900">Is call data private?</span>
                        <i data-lucide="plus" class="w-4 h-4 text-zinc-400 transition-transform"
                            :class="{'rotate-45': open}"></i>
                    </button>
                    <div x-show="open" class="px-5 pb-5 text-sm text-zinc-600 leading-relaxed" style="display: none;">
                        Absolutely. All transcripts are end-to-end encrypted. We do not use your customer data to train
                        public models.
                    </div>
                </div>

                <!-- FAQ Item -->
                <div class="bg-white rounded-xl border border-zinc-200 p-1 shadow-sm hover:border-zinc-300 transition-colors"
                    x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full text-left p-5">
                        <span class="font-semibold text-sm text-zinc-900">Does it work in Kenya?</span>
                        <i data-lucide="plus" class="w-4 h-4 text-zinc-400 transition-transform"
                            :class="{'rotate-45': open}"></i>
                    </button>
                    <div x-show="open" class="px-5 pb-5 text-sm text-zinc-600 leading-relaxed" style="display: none;">
                        Yes. Our "Nairobi Mode" is specifically tuned for Kenyan accents, local entities (MPESA,
                        estates), and mixed English-Swahili conversations.
                    </div>
                </div>
            </div>

            <!-- Final CTA -->
            <div class="mt-24 text-center">
                <h3 class="text-2xl font-bold text-zinc-900 mb-4">Ready to modernize your business?</h3>
                <p class="text-zinc-500 mb-10 text-base">Build your agent for free today.</p>
                <a href="{{ route('register') }}"
                    class="inline-flex items-center px-10 py-5 bg-zinc-900 hover:bg-zinc-800 text-white rounded-xl font-bold transition-all shadow-xl shadow-zinc-200/50 hover:shadow-2xl hover:-translate-y-1">
                    Start Building Now
                    <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer: Simple, Top Border Only -->
    <x-footer />

    <script>
        lucide.createIcons();
    </script>

</body>

</html>