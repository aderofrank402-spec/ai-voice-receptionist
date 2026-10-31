<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'VoiceAgents') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <script src="https://unpkg.com/lucide@latest"></script>


        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
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
    <body class="font-sans text-zinc-900 antialiased bg-white flex flex-col min-h-screen">
        <!-- Noise Overlay -->
        <div class="noise-overlay"></div>
    
        <!-- Background Grid -->
        <div class="fixed inset-0 bg-[linear-gradient(to_right,rgba(0,0,0,0.02)_1px,transparent_1px),linear-gradient(to_bottom,rgba(0,0,0,0.02)_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none -z-10"></div>

        <x-navbar />

        <div class="flex-grow flex flex-col justify-center items-center pt-32 pb-24 px-6 relative z-10">
            <div class="w-full sm:max-w-md px-8 py-8 bg-white border border-zinc-200 shadow-xl shadow-zinc-200/50 overflow-hidden sm:rounded-3xl relative">
                <!-- Decorative Icon Top Center -->
                 <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -mt-4">
                    <div class="w-12 h-1 bg-gradient-to-r from-zinc-900 to-zinc-600 rounded-full"></div>
                </div>

                <div class="mb-8 text-center">
                    <h2 class="text-2xl font-bold text-zinc-900 mb-1">{{ request()->routeIs('register') ? 'Create Account' : 'Welcome Back' }}</h2>
                    <p class="text-sm text-zinc-500 font-medium">{{ request()->routeIs('register') ? 'Start building your agent today.' : 'Sign in to manage your agents.' }}</p>
                </div>

                {{ $slot }}
            </div>
        </div>

        <x-footer />
        <script>lucide.createIcons();</script>

    </body>
</html>