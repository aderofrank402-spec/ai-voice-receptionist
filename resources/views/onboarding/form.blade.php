<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Setup | VoiceAgents</title>
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

        .aurora-gradient {
            background-image: radial-gradient(at 10% 20%, rgb(236, 254, 255) 0px, transparent 50%),
                radial-gradient(at 90% 80%, rgb(236, 254, 255) 0px, transparent 50%),
                radial-gradient(at 50% 0%, rgb(236, 254, 255) 0px, transparent 50%),
                radial-gradient(at 50% 100%, rgb(236, 254, 255) 0px, transparent 50%);
            background-size: 100% 100%;
            background-repeat: no-repeat;
            opacity: 0.7;
        }

        .noise-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 100;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 250 250' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            background-size: cover;
            opacity: 0.03;
        }
    </style>
</head>

<body
    class="min-h-screen flex flex-col font-sans antialiased text-zinc-900 bg-zinc-50 selection:bg-zinc-900 selection:text-white">

    <!-- Background Grid -->
    <!-- Background Grid -->
    <div
        class="fixed inset-0 bg-[linear-gradient(to_right,rgba(0,0,0,0.02)_1px,transparent_1px),linear-gradient(to_bottom,rgba(0,0,0,0.02)_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none -z-10">
    </div>

    <div class="noise-overlay"></div>

    <!-- Navigation -->
    <x-navbar />

    <!-- Main Content -->
    <main class="flex-grow flex flex-col items-center justify-center p-4 relative z-10" style="padding-top: 140px;">

        <div
            class="w-full max-w-2xl bg-white border border-zinc-200 rounded-3xl shadow-xl shadow-zinc-200/50 relative z-10 p-8 space-y-8">

            <div class="text-center space-y-2">
                <div
                    class="w-12 h-12 bg-zinc-100 rounded-xl flex items-center justify-center mx-auto text-zinc-900 mb-4 border border-zinc-200">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Manual Business Setup</h1>
                <p class="text-zinc-500 text-sm">Fill in the details below to configure your voice agent manually.</p>
            </div>

            <form action="{{ route('onboarding.submit') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Business Name -->
                <div class="space-y-2">
                    <label for="business_name" class="text-sm font-semibold text-zinc-700">Business Name</label>
                    <input type="text" name="business_name" id="business_name" required
                        class="w-full h-12 bg-white border border-zinc-300 rounded-lg px-4 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all shadow-sm placeholder:text-zinc-400"
                        placeholder="e.g., Juma Auto Garage">
                    @error('business_name') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                </div>

                <!-- Services -->
                <div class="space-y-2">
                    <label for="services" class="text-sm font-semibold text-zinc-700">Services Offered</label>
                    <textarea name="services" id="services" rows="3" required
                        class="w-full bg-white border border-zinc-300 rounded-lg p-4 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all shadow-sm resize-none placeholder:text-zinc-400"
                        placeholder="List your main services..."></textarea>
                    @error('services') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                </div>

                <!-- Pricing -->
                <div class="space-y-2">
                    <label for="pricing" class="text-sm font-semibold text-zinc-700">Pricing Information</label>
                    <textarea name="pricing" id="pricing" rows="2" required
                        class="w-full bg-white border border-zinc-300 rounded-lg p-4 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all shadow-sm resize-none placeholder:text-zinc-400"
                        placeholder="e.g., Oil Change: KES 3000, Brake Pads: KES 5000"></textarea>
                    @error('pricing') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                </div>

                <!-- Hours -->
                <div class="space-y-2">
                    <label for="hours" class="text-sm font-semibold text-zinc-700">Opening Hours</label>
                    <input type="text" name="hours" id="hours" required
                        class="w-full h-12 bg-white border border-zinc-300 rounded-lg px-4 text-sm focus:outline-none focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 transition-all shadow-sm placeholder:text-zinc-400"
                        placeholder="e.g., Mon-Fri: 8am - 6pm">
                    @error('hours') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                </div>

                <!-- Missed Call Action -->
                <div class="space-y-3">
                    <label class="text-sm font-semibold text-zinc-700">When you miss a call...</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label
                            class="flex items-center gap-3 p-4 border border-zinc-200 rounded-xl cursor-pointer hover:border-zinc-400 hover:bg-zinc-50 transition-all has-[:checked]:border-zinc-900 has-[:checked]:bg-zinc-50">
                            <input type="radio" name="missed_call_action" value="take_message"
                                class="w-4 h-4 text-zinc-900 border-zinc-300 focus:ring-zinc-500 accent-zinc-900"
                                checked>
                            <span class="text-sm text-zinc-700 font-medium">Take a Message</span>
                        </label>
                        <label
                            class="flex items-center gap-3 p-4 border border-zinc-200 rounded-xl cursor-pointer hover:border-zinc-400 hover:bg-zinc-50 transition-all has-[:checked]:border-zinc-900 has-[:checked]:bg-zinc-50">
                            <input type="radio" name="missed_call_action" value="forward_call"
                                class="w-4 h-4 text-zinc-900 border-zinc-300 focus:ring-zinc-500 accent-zinc-900">
                            <span class="text-sm text-zinc-700 font-medium">Forward Call</span>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-zinc-100">
                    <a href="{{ route('onboarding.index') }}"
                        class="text-sm text-zinc-500 hover:text-zinc-900 font-medium transition-colors">Back to Chat</a>
                    <button type="submit"
                        class="bg-zinc-900 hover:bg-zinc-800 text-white px-6 py-2.5 rounded-lg font-medium shadow-lg shadow-zinc-200 transition-all transform hover:scale-[1.02] active:scale-95">
                        Complete Setup
                    </button>
                </div>
            </form>
        </div>
    </main>

    <x-footer />
    <script>
        lucide.createIcons();
    </script>
</body>

</html>