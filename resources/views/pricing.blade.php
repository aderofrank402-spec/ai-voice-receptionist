<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing | VoiceAgents</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

    <main class="flex-grow pt-40 pb-24 px-6 relative z-10">
        <!-- Technical Header: Unit Economics Blueprint -->
        <div class="max-w-6xl mx-auto mb-20 relative">
            <div class="absolute inset-0 -top-40 -bottom-20 bg-zinc-950 -mx-[100vw] px-[100vw] z-0">
                <!-- Unit Economics Blueprint SVG -->
                <div class="absolute inset-0 opacity-20">
                    <svg class="w-full h-full" viewBox="0 0 1200 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="200" cy="200" r="1.5" fill="white" />
                        <circle cx="600" cy="150" r="1.5" fill="white" />
                        <circle cx="1000" cy="250" r="1.5" fill="white" />
                        <path d="M200 200 L600 150 L1000 250" stroke="white" stroke-width="0.5" stroke-dasharray="4 4" />
                        <path d="M200 200 Q400 300 600 150" stroke="#10b981" stroke-width="0.5" opacity="0.3" />
                        <path d="M600 150 Q800 50 1000 250" stroke="#6366f1" stroke-width="0.5" opacity="0.3" />
                        
                        <!-- Pulse Node -->
                        <circle cx="600" cy="150" r="4" fill="#10b981" opacity="0.1">
                            <animate attributeName="r" values="4;12;4" dur="3s" repeatCount="indefinite" />
                        </circle>
                    </svg>
                </div>
            </div>

            <div class="relative z-10 text-center">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] uppercase font-bold tracking-[0.2em] mb-6">
                    Performance Economics
                </div>
                <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 tracking-tighter">Simple, predictive <span class="text-zinc-500 italic">scale.</span></h1>
                <p class="text-xl text-zinc-400 max-w-2xl mx-auto font-medium leading-relaxed">
                    Start for free. Provision resources as you grow. <br class="hidden md:block">
                    Zero setup fees. Human-grade output.
                </p>
            </div>
        </div>

        <div class="max-w-6xl mx-auto grid md:grid-cols-3 gap-8 items-start">
            <!-- Starter -->
            <div
                class="bg-white p-8 rounded-3xl border border-zinc-200 shadow-sm hover:border-zinc-300 transition-colors group">
                <h3 class="text-lg font-bold text-zinc-900 mb-2">Starter (Solo)</h3>
                <div class="flex items-baseline gap-1 mb-6">
                    <span class="text-4xl font-bold text-zinc-900 tracking-tight">2,999</span>
                    <span class="text-lg font-bold text-zinc-900">KES</span>
                    <span class="text-zinc-500 text-sm font-medium">/mo</span>
                </div>
                <p class="text-sm text-zinc-500 mb-8 min-h-[40px]">Perfect for solopreneurs handling their own calls.
                </p>

                <button
                    class="w-full py-3 rounded-xl border border-zinc-200 bg-white text-zinc-900 font-bold hover:bg-zinc-50 hover:border-zinc-300 transition-all mb-8">
                    Get Started
                </button>

                <div class="space-y-4">
                    <p class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Features</p>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3 text-sm text-zinc-600">
                            <i data-lucide="check" class="w-4 h-4 text-zinc-900 mt-0.5"></i>
                            <span>60 AI Minutes / month</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-zinc-600">
                            <i data-lucide="check" class="w-4 h-4 text-zinc-900 mt-0.5"></i>
                            <span>15 KES / min overage</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-zinc-600">
                            <i data-lucide="check" class="w-4 h-4 text-zinc-900 mt-0.5"></i>
                            <span>1 Local Number Included</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-zinc-600">
                            <i data-lucide="check" class="w-4 h-4 text-zinc-900 mt-0.5"></i>
                            <span>SMS Summaries</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Growth (Popular) -->
            <div
                class="bg-zinc-900 p-8 rounded-3xl border border-zinc-800 shadow-2xl relative overflow-hidden transform md:-translate-y-4">
                <!-- Highlight Gradient -->
                <div
                    class="absolute top-0 right-0 w-[300px] h-[300px] bg-emerald-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2">
                </div>

                <div
                    class="absolute top-5 right-5 bg-emerald-500 text-white text-[10px] uppercase font-bold px-3 py-1 rounded-full shadow-lg shadow-emerald-500/20">
                    Most Popular
                </div>

                <h3 class="text-lg font-bold text-white mb-2 relative z-10">Growth (SME)</h3>
                <div class="flex items-baseline gap-1 mb-6 relative z-10">
                    <span class="text-5xl font-bold text-white tracking-tight">9,999</span>
                    <span class="text-lg font-bold text-white">KES</span>
                    <span class="text-zinc-400 text-sm font-medium">/mo</span>
                </div>
                <p class="text-sm text-zinc-400 mb-8 min-h-[40px] relative z-10">For busy clinics and garages needing
                    scale.</p>

                <button
                    class="w-full py-3 rounded-xl bg-emerald-500 text-white font-bold hover:bg-emerald-400 transition-all shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 hover:-translate-y-0.5 mb-8 relative z-10">
                    Start Free Trial
                </button>

                <div class="space-y-4 relative z-10">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Everything in Starter plus:</p>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3 text-sm text-zinc-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 mt-0.5"></i>
                            <span>300 AI Minutes / month</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-zinc-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 mt-0.5"></i>
                            <span>10 KES / min overage</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-zinc-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 mt-0.5"></i>
                            <span>Calendar Sync & CRM</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-zinc-300">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400 mt-0.5"></i>
                            <span>Prioritized Support</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Enterprise -->
            <div
                class="bg-white p-8 rounded-3xl border border-zinc-200 shadow-sm hover:border-zinc-300 transition-colors group">
                <h3 class="text-lg font-bold text-zinc-900 mb-2">Enterprise</h3>
                <div class="flex items-baseline gap-1 mb-6">
                    <span class="text-4xl font-bold text-zinc-900 tracking-tight">Custom</span>
                </div>
                <p class="text-sm text-zinc-500 mb-8 min-h-[40px]">For call centers and franchise networks.</p>

                <button
                    class="w-full py-3 rounded-xl border border-zinc-200 bg-white text-zinc-900 font-bold hover:bg-zinc-50 hover:border-zinc-300 transition-all mb-8">
                    Contact Sales
                </button>

                <div class="space-y-4">
                    <p class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Everything in Growth plus:</p>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3 text-sm text-zinc-600">
                            <i data-lucide="check" class="w-4 h-4 text-zinc-900 mt-0.5"></i>
                            <span>Unlimited Agents & Minutes</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-zinc-600">
                            <i data-lucide="check" class="w-4 h-4 text-zinc-900 mt-0.5"></i>
                            <span>Custom API Integration</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-zinc-600">
                            <i data-lucide="check" class="w-4 h-4 text-zinc-900 mt-0.5"></i>
                            <span>Multiple Phone Numbers</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-zinc-600">
                            <i data-lucide="check" class="w-4 h-4 text-zinc-900 mt-0.5"></i>
                            <span>Dedicated Account Manager</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <x-footer />
    <script>lucide.createIcons();</script>
</body>

</html>