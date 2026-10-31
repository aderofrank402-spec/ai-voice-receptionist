<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | VoiceAgent</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;family=JetBrains+Mono:wght@400;500&amp;display=swap" rel="stylesheet">
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
<body class="min-h-screen flex flex-col antialiased text-zinc-900 bg-white selection:bg-emerald-500 selection:text-white">
    <div class="noise-overlay"></div>
    <div class="fixed inset-0 pointer-events-none -z-10 grid-pattern"></div>

    <main class="flex-1 flex flex-col items-center justify-center px-6 relative z-10">
        <div class="text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-zinc-50 border border-zinc-200 text-zinc-600 text-[11px] uppercase font-bold tracking-widest mb-8">
                <span class="w-2 h-2 rounded-full bg-zinc-400"></span>
                Error 404
            </div>

            <h1 class="text-7xl md:text-9xl font-bold tracking-tighter text-zinc-950 mb-6">
                Lost in<br>
                <span class="text-zinc-400">transmission.</span>
            </h1>

            <p class="text-lg text-zinc-500 leading-relaxed max-w-md mx-auto mb-12">
                The page you are looking for has been moved or doesn't exist. Let's get you back to the command center.
            </p>

            <div class="flex flex-col sm:flex-row items-center gap-4 justify-center">
                <a href="{{ route('dashboard') }}" class="h-14 px-8 bg-zinc-950 hover:bg-zinc-800 text-white rounded-xl text-base font-semibold transition-all shadow-xl shadow-zinc-200/50 hover:shadow-2xl hover:-translate-y-0.5 flex items-center gap-2 group">
                    Go to Dashboard
                    <i data-lucide="arrow-right" class="w-4 h-4 text-emerald-400 group-hover:translate-x-1 transition-transform"></i>
                </a>
                <a href="{{ route('home') }}" class="h-14 px-8 bg-white border border-zinc-200 hover:border-zinc-300 hover:bg-zinc-50 text-zinc-900 rounded-xl text-base font-medium transition-all flex items-center gap-2">
                    Back Home
                </a>
            </div>
        </div>
    </main>

    <footer class="py-12 border-t border-zinc-100 relative z-10 text-center">
        <p class="text-xs text-zinc-400 uppercase tracking-widest font-semibold">
            Voice Agent Intelligence • Platform v2.0
        </p>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
