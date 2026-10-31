<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>How it Works | VoiceAgents</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* Noise Overlay */
        .noise-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
            opacity: 0.03;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
        }

        ::selection {
            background-color: #10b981;
            color: white;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col font-sans text-zinc-900 bg-white overflow-x-hidden">
    <!-- Noise Overlay -->
    <div class="noise-overlay"></div>

    <!-- Background Grid -->
    <div
        class="fixed inset-0 bg-[linear-gradient(to_right,rgba(0,0,0,0.02)_1px,transparent_1px),linear-gradient(to_bottom,rgba(0,0,0,0.02)_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none -z-10">
    </div>

    <x-navbar />

    <main class="flex-grow pt-32 pb-24 px-6 relative z-10">
        <!-- Hero Section -->
        <div class="max-w-4xl mx-auto text-center mb-24">
            <div
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] uppercase font-bold tracking-wider mb-6">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Process Overview
            </div>
            <h1 class="text-5xl md:text-6xl font-bold text-zinc-900 mb-6 tracking-tight">
                From Zero to AI Agent in<br><span class="text-emerald-600">Minutes</span>
            </h1>
            <p class="text-xl text-zinc-600 max-w-2xl mx-auto font-medium leading-relaxed">
                No complex flowcharts or coding required. We use a conversational approach to build your custom voice
                agent.
            </p>
        </div>

        <!-- The Master Pipeline: The 3-Step Process -->
        <div class="max-w-5xl mx-auto mb-48 relative">
            <!-- The Central Pipeline SVG -->
            <div class="absolute left-1/2 top-0 bottom-0 -translate-x-1/2 w-8 hidden md:block z-0">
                <svg class="w-full h-full" viewBox="0 0 40 800" preserveAspectRatio="none" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 0 V 800" stroke="#f4f4f5" stroke-width="4" stroke-dasharray="8 8" />
                    <!-- The Active Flow Path -->
                    <path d="M20 0 V 800" stroke="url(#pipeline-gradient)" stroke-width="2" />
                    
                    <!-- Traveling Pulses -->
                    <circle r="3" fill="#10b981">
                        <animateMotion path="M20 0 V 800" dur="6s" repeatCount="indefinite" />
                    </circle>
                    <circle r="2" fill="#6366f1">
                        <animateMotion path="M20 0 V 800" dur="6s" begin="2s" repeatCount="indefinite" />
                    </circle>

                    <defs>
                        <linearGradient id="pipeline-gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#10b981" />
                            <stop offset="50%" stop-color="#6366f1" />
                            <stop offset="100%" stop-color="#10b981" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>

            <div class="space-y-32 relative z-10">
                <!-- Step 01: The Interview -->
                <div class="grid md:grid-cols-2 gap-16 items-center">
                    <div class="order-last md:order-first">
                        <div class="aspect-square bg-white rounded-3xl border border-zinc-100 shadow-2xl relative overflow-hidden group">
                            <div class="absolute inset-0 grid-pattern opacity-30"></div>
                            <div class="absolute inset-0 flex items-center justify-center p-12">
                                <div class="relative">
                                    <div class="absolute -inset-8 bg-emerald-500/5 blur-3xl rounded-full group-hover:bg-emerald-500/10 transition-colors"></div>
                                    <div class="relative w-32 h-32 bg-white border border-zinc-100 rounded-[2.5rem] shadow-xl flex items-center justify-center transform group-hover:scale-105 transition-transform duration-500">
                                        <i data-lucide="message-square-text" class="w-14 h-14 text-emerald-600"></i>
                                    </div>
                                    <div class="absolute -bottom-4 left-1/2 -translate-x-1/2 px-3 py-1 bg-zinc-950 rounded-full shadow-lg">
                                        <span class="text-[8px] font-mono text-emerald-400 font-bold tracking-widest uppercase">Signal_Input</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="w-12 h-12 bg-zinc-900 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-xl shadow-zinc-900/20">
                            01
                        </div>
                        <h3 class="text-3xl font-bold text-zinc-950 tracking-tight">The Interview</h3>
                        <p class="text-zinc-600 leading-relaxed text-lg font-medium">
                            Chat with our <span class="text-zinc-950 font-bold">Builder Agent</span>. No technical configuration is required.
                        </p>
                        <p class="text-zinc-500 leading-relaxed">
                            Our AI consultant interviews you about your business logic, hours, services, and pricing. 
                            It understands nuance and maps your business identity in real-time.
                        </p>
                    </div>
                </div>

                <!-- Step 02: Neural Synthesis -->
                <div class="grid md:grid-cols-2 gap-16 items-center">
                    <div class="space-y-6 text-right flex flex-col items-end">
                        <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-xl shadow-indigo-600/20">
                            02
                        </div>
                        <h3 class="text-3xl font-bold text-zinc-950 tracking-tight">Neural Synthesis</h3>
                        <p class="text-zinc-600 leading-relaxed text-lg font-medium">
                            We train your <span class="text-indigo-600 font-bold">Custom Model</span> instantly.
                        </p>
                        <p class="text-zinc-500 leading-relaxed max-w-md">
                            The architecture process extracts key data points from your interview to provision a 
                            dedicated phone number and synchronize your local business data into the AI's core.
                        </p>
                    </div>
                    <div class="relative">
                        <div class="aspect-square bg-zinc-950 rounded-[3rem] border border-zinc-800 shadow-2xl relative overflow-hidden group">
                            <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_center,_#6366f1_0%,_transparent_70%)]"></div>
                            <div class="absolute inset-0 flex items-center justify-center p-12">
                                <div class="w-full aspect-square border border-zinc-800 rounded-full relative animate-spin [animation-duration:10s]">
                                    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-4 h-4 bg-indigo-500 rounded-full shadow-[0_0_15px_#6366f1]"></div>
                                    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-2 h-2 bg-zinc-700 rounded-full"></div>
                                </div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="text-center">
                                        <i data-lucide="cpu" class="w-16 h-16 text-white mb-2"></i>
                                        <span class="block text-[9px] font-mono text-zinc-400 font-bold tracking-widest uppercase">Processing_Data</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 03: Global Deployment -->
                <div class="grid md:grid-cols-2 gap-16 items-center">
                    <div class="order-last md:order-first">
                        <div class="aspect-square bg-emerald-600 rounded-[2rem] shadow-2xl relative overflow-hidden group">
                            <div class="absolute inset-0 bg-[linear-gradient(45deg,transparent_25%,rgba(255,255,255,0.05)_50%,transparent_75%)] bg-[size:20px_20px] animate-[pulse_2s_infinite]"></div>
                            <div class="absolute inset-0 flex items-center justify-center p-12 text-white">
                                <div class="text-center">
                                    <div class="relative mb-6">
                                        <div class="absolute -inset-4 bg-white/20 blur-xl rounded-full"></div>
                                        <i data-lucide="radio" class="w-20 h-20 relative z-10"></i>
                                    </div>
                                    <div class="px-4 py-2 bg-emerald-950/40 backdrop-blur rounded-lg border border-white/10">
                                        <span class="text-[10px] font-mono font-bold tracking-tighter uppercase whitespace-nowrap">Node_Status: Live_Transmission</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-xl shadow-emerald-500/20">
                            03
                        </div>
                        <h3 class="text-3xl font-bold text-zinc-950 tracking-tight">Global Deployment</h3>
                        <p class="text-zinc-600 leading-relaxed text-lg font-medium">
                            Your agent goes live <span class="text-emerald-600 font-bold">Immediately</span>.
                        </p>
                        <p class="text-zinc-500 leading-relaxed">
                            Receive calls, book appointments, and sync with your calendar automatically. 
                            The system handles everything from answering to closing the lead while you focus on work.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Grid -->
        <div id="features" class="max-w-6xl mx-auto mb-24">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-zinc-900 mb-4">What Your Agent Can Do</h2>
                <p class="text-lg text-zinc-600 max-w-2xl mx-auto">
                    Built-in capabilities that handle your business needs automatically.
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Feature 1 -->
                <div
                    class="bg-white p-8 rounded-2xl border border-zinc-200 shadow-lg shadow-zinc-200/50 hover:border-zinc-400 transition-colors">
                    <div class="mb-4">
                        <i class="fa-regular fa-calendar-check text-2xl text-zinc-800"></i>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">Appointment Booking</h3>
                    <p class="text-zinc-600 text-sm">
                        Schedule appointments directly into your calendar with automatic confirmation.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div
                    class="bg-white p-8 rounded-2xl border border-zinc-200 shadow-lg shadow-zinc-200/50 hover:border-zinc-400 transition-colors">
                    <div class="mb-4">
                        <i class="fa-regular fa-circle-question text-2xl text-zinc-800"></i>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">Answer FAQs</h3>
                    <p class="text-zinc-600 text-sm">
                        Responds to common questions about your services, hours, and pricing instantly.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div
                    class="bg-white p-8 rounded-2xl border border-zinc-200 shadow-lg shadow-zinc-200/50 hover:border-zinc-400 transition-colors">
                    <div class="mb-4">
                        <i class="fa-solid fa-phone-volume text-2xl text-zinc-800"></i>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">Call Routing</h3>
                    <p class="text-zinc-600 text-sm">
                        Transfers calls to the right department or person based on customer needs.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div
                    class="bg-white p-8 rounded-2xl border border-zinc-200 shadow-lg shadow-zinc-200/50 hover:border-zinc-400 transition-colors">
                    <div class="mb-4">
                        <i class="fa-regular fa-address-card text-2xl text-zinc-800"></i>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">Lead Capture</h3>
                    <p class="text-zinc-600 text-sm">
                        Collects customer information and stores it in your CRM automatically.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div
                    class="bg-white p-8 rounded-2xl border border-zinc-200 shadow-lg shadow-zinc-200/50 hover:border-zinc-400 transition-colors">
                    <div class="mb-4">
                        <i class="fa-regular fa-clock text-2xl text-zinc-800"></i>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">24/7 Availability</h3>
                    <p class="text-zinc-600 text-sm">
                        Never miss a call, even outside business hours or during high-volume periods.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div
                    class="bg-white p-8 rounded-2xl border border-zinc-200 shadow-lg shadow-zinc-200/50 hover:border-zinc-400 transition-colors">
                    <div class="mb-4">
                        <i class="fa-solid fa-chart-simple text-2xl text-zinc-800"></i>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900 mb-2">Call Analytics</h3>
                    <p class="text-zinc-600 text-sm">
                        Track call volume, duration, outcomes, and customer satisfaction metrics.
                    </p>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="max-w-4xl mx-auto text-center">
            <div
                class="bg-gradient-to-br from-zinc-900 to-zinc-800 rounded-3xl p-12 shadow-2xl shadow-zinc-900/30 relative overflow-hidden">
                <div
                    class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:24px_24px]">
                </div>
                <div class="relative z-10">
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                        Ready to Build Your AI Agent?
                    </h2>
                    <p class="text-xl text-zinc-300 mb-8 max-w-2xl mx-auto">
                        Get started today with flexible pricing plans.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-xl transition-colors shadow-lg shadow-emerald-500/30">
                            Get Started
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>
                        <a href="{{ route('pricing') }}"
                            class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-bold rounded-xl transition-colors border border-white/20">
                            View Pricing
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <x-footer />
    <script>lucide.createIcons();</script>
</body>

</html>