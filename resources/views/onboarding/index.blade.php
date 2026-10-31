<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Architect | Builder Agent</title>
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

        .message-bubble {
            line-height: 1.6 !important;
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
        }

        /* Premium Background Pattern */
        .grid-pattern {
            background-size: 40px 40px;
            background-image: linear-gradient(to right, rgba(0, 0, 0, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
            mask-image: radial-gradient(circle at center, black 60%, transparent 100%);
        }

        /* Toast Animations */
        @keyframes slide-in-right {
            from {
                opacity: 0;
                transform: translateX(100%);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slide-out-right {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }

        .animate-slide-in-right {
            animation: slide-in-right 0.3s ease-out forwards;
        }

        .toast-notification {
            transition: opacity 0.3s ease-out, transform 0.3s ease-out;
        }
    </style>
</head>

<body
    class="min-h-screen flex flex-col font-sans antialiased text-zinc-900 bg-zinc-50 selection:bg-emerald-500 selection:text-white">

    <!-- Background -->
    <div class="fixed inset-0 pointer-events-none -z-10 grid-pattern"></div>

    <x-navbar />

    <main class="flex-grow flex flex-col pb-6 px-4 md:px-6 relative z-10" style="padding-top: 110px;">
        <div class="max-w-5xl mx-auto w-full flex-grow flex flex-col">

            <!-- Context Header (Simplified) -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-zinc-200 text-zinc-600 text-[11px] font-bold uppercase tracking-wider mb-3 shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    System Configuration
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-zinc-900">Configure your Receptionist</h1>
            </div>

            <!-- Chat Interface (Full Width) -->
            <div
                class="w-full h-[calc(100vh-280px)] min-h-[500px] bg-white border border-zinc-200 rounded-2xl shadow-2xl shadow-zinc-200/50 flex flex-col overflow-hidden relative ring-1 ring-zinc-950/5">

                <!-- Chat Header -->
                <div class="h-16 border-b border-zinc-100 px-6 flex items-center justify-between bg-white z-20">
                    <div class="flex items-center gap-3">
                        <h2 class="text-sm font-semibold text-zinc-800">Interviewer Agent</h2>
                        <div id="scan-badge"
                            class="hidden inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <svg class="animate-spin -ml-1 mr-1.5 h-3 w-3 text-blue-600"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Scanning: <span id="scan-timer" class="ml-1 font-mono">0.0s</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-lg bg-zinc-900 flex items-center justify-center shadow-lg shadow-zinc-900/10">
                            <i data-lucide="bot" class="w-5 h-5 text-white"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-zinc-900">Architect</span>
                            <span class="text-[11px] text-zinc-500 font-medium">Builder Agent</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Online Badge -->
                        <span
                            class="text-[10px] text-emerald-700 font-bold bg-emerald-50 px-2 py-1 rounded border border-emerald-100 flex items-center gap-1.5 mr-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Online
                        </span>

                        <!-- Restart Button -->
                        <form action="{{ route('onboarding.reset') }}" method="POST"
                            onsubmit="return confirm('Start over? This will clear the current chat.');">
                            @csrf
                            <button type="submit"
                                class="text-xs text-zinc-400 hover:text-red-500 font-medium transition-colors flex items-center gap-1.5 group"
                                title="Restart Interview">
                                <i data-lucide="refresh-cw"
                                    class="w-3.5 h-3.5 group-hover:rotate-180 transition-transform duration-500"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Toast Container (Top-Right) -->
                <div id="toast-container" class="fixed top-20 right-6 z-50 flex flex-col gap-2 pointer-events-none">
                    <!-- Toasts will be injected here -->
                </div>

                <!-- Phased progress view (Google-style: steps + message + page list) -->
                <div id="crawler-terminal-view" class="hidden flex-grow flex flex-col overflow-hidden bg-white border-b border-zinc-200">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100">
                        <h3 class="text-base font-semibold text-zinc-800">Analyzing your website</h3>
                        <div id="terminal-scan-badge" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-600">
                            <svg id="terminal-spinner" class="animate-spin h-3.5 w-3.5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="terminal-scan-timer" class="font-mono">0.0s</span>
                        </div>
                    </div>
                    <!-- Step indicator -->
                    <div class="px-5 py-4 flex items-center gap-2 flex-wrap">
                        <span id="progress-step-1" class="progress-step flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                            <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold">1</span> Analyzing
                        </span>
                        <span class="text-zinc-300">→</span>
                        <span id="progress-step-2" class="progress-step flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-500 border border-zinc-200">
                            <span class="w-5 h-5 rounded-full bg-zinc-300 text-zinc-600 flex items-center justify-center text-[10px] font-bold">2</span> Scanning
                        </span>
                        <span class="text-zinc-300">→</span>
                        <span id="progress-step-3" class="progress-step flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-500 border border-zinc-200">
                            <span class="w-5 h-5 rounded-full bg-zinc-300 text-zinc-600 flex items-center justify-center text-[10px] font-bold">3</span> Extracting
                        </span>
                        <span class="text-zinc-300">→</span>
                        <span id="progress-step-4" class="progress-step flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-500 border border-zinc-200">
                            <span class="w-5 h-5 rounded-full bg-zinc-300 text-zinc-600 flex items-center justify-center text-[10px] font-bold">4</span> Ready
                        </span>
                    </div>
                    <!-- Current message -->
                    <div class="px-5 pb-2">
                        <p id="progress-message" class="text-sm text-zinc-600">Preparing to scan your website...</p>
                    </div>
                    <!-- Error alert only; Live log panel removed -->
                    <div id="crawler-error-alert" class="hidden mx-5 mb-4 p-4 rounded-lg border border-red-200 bg-red-50">
                        <p class="text-xs font-semibold text-red-800 uppercase tracking-wider mb-1">Crawl error</p>
                        <pre id="crawler-error-text" class="text-sm text-red-700 whitespace-pre-wrap break-words font-mono overflow-x-auto"></pre>
                    </div>
                    <!-- Discovered pages list -->
                    <div class="flex-grow overflow-auto min-h-0 px-5 pb-4">
                        <p class="text-xs font-medium text-zinc-500 mb-2">Pages found</p>
                        <ul id="progress-pages-list" class="space-y-1.5 text-sm text-zinc-700">
                            <li id="progress-pages-empty" class="text-zinc-400 italic">No pages yet.</li>
                        </ul>
                    </div>
                    <div id="terminal-completed-badge" class="hidden px-5 py-4 border-t border-zinc-200 bg-emerald-50 flex items-center gap-2 text-emerald-700 text-sm font-medium">
                        <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
                        <span>Ready — Switching back to chat...</span>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="flex-grow overflow-y-auto p-6 space-y-8 bg-zinc-50/30" id="chat-messages">
                    @if(empty($history) || count($history) === 0)
                        <!-- Initial Welcome Message -->
                        <div class="flex gap-4">
                            <div
                                class="w-8 h-8 rounded bg-white border border-zinc-200 flex-shrink-0 flex items-center justify-center shadow-sm">
                                <i data-lucide="sparkles" class="w-4 h-4 text-zinc-900"></i>
                            </div>
                            <div class="space-y-1">
                                <div
                                    class="bg-white border border-zinc-200 p-5 rounded-2xl rounded-tl-none shadow-sm max-w-lg">
                                    <p class="text-sm text-zinc-700 leading-relaxed font-medium">
                                        Hello {{ Auth::user()->name }}! I'm Architect. I'll help you configure your AI
                                        receptionist.<br><br>
                                        <strong>To get started, what is the name of your business and what services do
                                            you
                                            offer?</strong>
                                    </p>
                                </div>
                                <span class="text-[10px] text-zinc-400 pl-1 font-mono">Now</span>
                            </div>
                        </div>
                    @else
                        <!-- Render History -->
                        @foreach($history as $msg)
                            @if($msg['role'] === 'user')
                                <div class="flex flex-col items-end gap-1">
                                    <div
                                        class="bg-zinc-900 text-white p-4 rounded-2xl rounded-tr-none shadow-md max-w-lg text-sm leading-relaxed message-bubble break-all">
                                        {{ $msg['content'] }}
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] text-zinc-400">Read</span>
                                        <i data-lucide="check" class="w-3 h-3 text-emerald-500"></i>
                                    </div>
                                </div>
                            @elseif($msg['role'] === 'assistant')
                                <div class="flex gap-4">
                                    <div
                                        class="w-8 h-8 rounded bg-white border border-zinc-200 flex-shrink-0 flex items-center justify-center shadow-sm">
                                        <i data-lucide="sparkles" class="w-4 h-4 text-zinc-900"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <div
                                            class="bg-white border border-zinc-200 p-4 rounded-2xl rounded-tl-none shadow-sm max-w-lg">
                                            <div class="text-sm text-zinc-700 leading-relaxed message-bubble font-medium">
                                                {!! \Illuminate\Support\Str::markdown($msg['content']) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif

                    <!-- Typing Indicator -->
                    <div id="typing-indicator" class="hidden flex gap-4">
                        <div
                            class="w-8 h-8 rounded bg-white border border-zinc-200 flex-shrink-0 flex items-center justify-center shadow-sm">
                            <i data-lucide="sparkles" class="w-4 h-4 text-zinc-900"></i>
                        </div>
                        <div
                            class="bg-white border border-zinc-200 px-4 py-4 rounded-2xl rounded-tl-none shadow-sm flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-zinc-400 rounded-full animate-bounce"
                                style="animation-delay: 0ms"></span>
                            <span class="w-1.5 h-1.5 bg-zinc-400 rounded-full animate-bounce"
                                style="animation-delay: 150ms"></span>
                            <span class="w-1.5 h-1.5 bg-zinc-400 rounded-full animate-bounce"
                                style="animation-delay: 300ms"></span>
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="p-4 bg-white border-t border-zinc-100 z-20">
                    <form id="chat-form" class="max-w-4xl mx-auto">
                        <!-- Consent Checkbox -->
                        <div class="flex items-center justify-center gap-2 mb-3">
                            <input type="checkbox" id="consent-check"
                                class="rounded border-zinc-300 text-emerald-600 shadow-sm focus:ring-emerald-500 cursor-pointer">
                            <label for="consent-check" class="text-xs text-zinc-500 select-none cursor-pointer">
                                I agree to the <a href="{{ route('terms') }}" target="_blank"
                                    class="text-emerald-600 underline hover:text-emerald-500">Terms of Service</a>
                                and
                                <a href="{{ route('privacy') }}" target="_blank"
                                    class="text-emerald-600 underline hover:text-emerald-500">Privacy Policy</a>.
                            </label>
                        </div>

                        <div
                            class="relative flex items-end gap-2 bg-zinc-50 border border-zinc-200 rounded-xl p-2 focus-within:border-zinc-400 focus-within:bg-white transition-all shadow-sm">
                            <textarea id="user-input" rows="1" placeholder="Type your answer here..." maxlength="2000"
                                class="flex-1 min-h-[40px] max-h-[150px] bg-transparent border-none pl-3 py-2 text-sm focus:outline-none placeholder:text-zinc-400 resize-none overflow-y-auto"
                                autocomplete="off" style="overflow-wrap: break-word; word-wrap: break-word;"></textarea>
                            <button type="submit" id="send-btn"
                                class="flex-shrink-0 w-9 h-9 flex items-center justify-center bg-zinc-900 hover:bg-zinc-800 text-white rounded-lg transition-all disabled:opacity-50 disabled:bg-zinc-400">
                                <i data-lucide="send" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <p class="text-center text-[10px] text-zinc-400 mt-2">
                            Press Enter to send • Shift+Enter for new line
                        </p>
                    </form>
                </div>

            </div>
        </div>
        </div>
    </main>
    <x-footer />

    <!-- FIX: Manually load Echo/Pusher via CDN to bypass broken build assets -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        console.log("📢 Initializing Echo manually...");
        window.Pusher = Pusher;

        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ env('REVERB_APP_KEY') }}',
            wsHost: '{{ env('REVERB_HOST', 'localhost') }}',
            wsPort: {{ env('REVERB_PORT', 8080) }},
            wssPort: {{ env('REVERB_PORT', 8080) }},
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '{{ url("/broadcasting/auth") }}',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            },
        });
        try {
            var pusher = window.Echo.connector && window.Echo.connector.pusher;
            if (pusher && pusher.connection) {
                pusher.connection.bind('error', function (err) { console.error('Reverb connection error:', err); });
                pusher.connection.bind('state_change', function (states) { console.log('Reverb state:', states.current); });
            }
        } catch (e) { console.warn('Echo debug bindings skipped:', e); }
        console.log("✅ Echo initialized:", window.Echo);
    </script>
    <script>
        lucide.createIcons();
        const chatForm = document.getElementById('chat-form');
        const userInput = document.getElementById('user-input');
        const chatMessages = document.getElementById('chat-messages');
        const sendBtn = document.getElementById('send-btn');
        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        scrollToBottom();

        // Auto-resize textarea as user types
        userInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 200) + 'px';
        });

        // Handle Enter to submit, Shift+Enter for new line
        userInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });

        // Safe appendMessage that handles null/undefined
        function appendMessage(role, text) {
            const div = document.createElement('div');
            // FIX: Prevent 'undefined.trim()' crash
            const cleanedText = (text || '').toString().trim();
            if (!cleanedText && role === 'assistant') return; // Skip empty assistant bubbles

            const displayText = parseMarkdown(cleanedText);
            if (role === 'user') {
                div.className = 'flex flex-col items-end gap-1';
                div.innerHTML = `
                    <div class="bg-zinc-900 text-white p-4 rounded-2xl rounded-tr-none shadow-md max-w-lg text-sm leading-relaxed message-bubble break-all">
                        ${escapeHtml(cleanedText)}
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] text-zinc-400">Read</span>
                        <i data-lucide="check" class="w-3 h-3 text-emerald-500"></i>
                    </div>
                `;
            } else {
                div.className = 'flex gap-4';
                div.innerHTML = `
                    <div class="w-8 h-8 rounded bg-white border border-zinc-200 flex-shrink-0 flex items-center justify-center shadow-sm">
                        <i data-lucide="sparkles" class="w-4 h-4 text-zinc-900"></i>
                    </div>
                    <div class="space-y-1">
                        <div class="bg-white border border-zinc-200 p-4 rounded-2xl rounded-tl-none shadow-sm max-w-lg">
                            <div class="text-sm text-zinc-700 leading-relaxed message-bubble font-medium">
                                ${displayText}
                            </div>
                        </div>
                    </div>
                `;
            }
            const typingIndicator = document.getElementById('typing-indicator');
            chatMessages.insertBefore(div, typingIndicator);
            lucide.createIcons();
            scrollToBottom();
        }

        function parseMarkdown(text) {
            // Basic markdown parsing for bold/italic if needed, or just return text
            // The system uses a backend markdown parser usually, but let's keep it simple or restore if complex.
            // Looking at previous valid versions, it was simple. 
            // Actually, the `appendMessage` uses `parseMarkdown`.
            // I'll define `parseMarkdown` here as well if it's missing, or just assume it's not needed if `appendMessage` handles it?
            // `appendMessage` calls `parseMarkdown`.
            // Wait, `parseMarkdown` is ALSO missing! It was deleted too.
            // I need to restore `parseMarkdown` AND `chatForm.addEventListener`.
            return text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>');
        }

        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Validate Consent
            const consentCheck = document.getElementById('consent-check');
            if (consentCheck && !consentCheck.checked) {
                alert("Please agree to the Terms of Service and Privacy Policy to continue.");
                return;
            }

            const message = userInput.value.trim();
            if (!message) return;
            appendMessage('user', message);
            userInput.value = '';
            userInput.style.height = 'auto'; // Reset height after sending
            userInput.disabled = true;
            sendBtn.disabled = true;
            const typingIndicator = document.getElementById('typing-indicator');

            // URL Detection -> Switch to Terminal and start Scraper
            if (message.includes('http')) {
                isScraping = true;
                console.log('🌐 URL detected, switching to Terminal view...');
                switchToTerminalView();
                startCrawlerListener();
            }

            typingIndicator.classList.remove('hidden');
            scrollToBottom();
            try {
                const response = await fetch('{{ route("onboarding.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message })
                });

                // Stop Timer if not scraping (or if scraping failed immediately)
                if (timerUpdateInterval != null && !isScraping) {
                    clearInterval(timerUpdateInterval);
                    timerUpdateInterval = null;
                    const badge = document.getElementById('scan-badge');
                    if (badge) badge.classList.add('hidden');
                }

                typingIndicator.classList.add('hidden');

                const data = await response.json();
                if (!response.ok) throw new Error(data.error || 'Network error');

                // Handle "Silent Start" (Controller returns empty reply for URLs)
                if (data.silent_start) {
                    // Do nothing, let WebSocket handle logs
                } else if (data.reply) {
                    appendMessage('assistant', data.reply);
                }

                // SECURITY: Use server-provided flag
                if (data.completed) {
                    if (data.reply) appendMessage('assistant', data.reply);
                    appendMessage('assistant', "🎉 **Interview Complete!** Generating your business profile...");
                    userInput.placeholder = "Redirecting to Review...";
                    userInput.disabled = true;
                    sendBtn.disabled = true;
                    if (data.redirect) {
                        setTimeout(() => { window.location.href = data.redirect; }, 1500);
                    }
                    return;
                }

            } catch (error) {
                document.getElementById('typing-indicator').classList.add('hidden');
                appendMessage('assistant', '⚠️ Error: ' + (error.message || 'Unknown error'));
            } finally {
                if (userInput.placeholder !== "Redirecting to Review...") {
                    userInput.disabled = false;
                    sendBtn.disabled = false;
                    userInput.focus();
                }
                scrollToBottom();
            }
        });

        // --- Terminal View (switch when crawling URL) ---
        const chatMessagesEl = document.getElementById('chat-messages');
        const crawlerTerminalView = document.getElementById('crawler-terminal-view');
        const terminalCompletedBadge = document.getElementById('terminal-completed-badge');
        const terminalScanTimer = document.getElementById('terminal-scan-timer');
        const terminalSpinner = document.getElementById('terminal-spinner');

        const progressMessageEl = document.getElementById('progress-message');
        const progressPagesList = document.getElementById('progress-pages-list');

        function setProgressStep(step) {
            const active = 'bg-emerald-100 text-emerald-800 border-emerald-200';
            const inactive = 'bg-zinc-100 text-zinc-500 border-zinc-200';
            const activeNum = 'bg-emerald-500 text-white';
            const inactiveNum = 'bg-zinc-300 text-zinc-600';
            for (let i = 1; i <= 4; i++) {
                const el = document.getElementById('progress-step-' + i);
                if (!el) continue;
                const num = el.querySelector('span:first-child');
                if (i <= step) {
                    el.className = 'progress-step flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium ' + active + ' border';
                    if (num) num.className = 'w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold ' + activeNum;
                } else {
                    el.className = 'progress-step flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium ' + inactive + ' border';
                    if (num) num.className = 'w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold ' + inactiveNum;
                }
            }
        }

        function addDiscoveredPage(page) {
            if (!progressPagesList || !page || shownPages.has(page.url)) return;
            shownPages.add(page.url);
            const emptyEl = document.getElementById('progress-pages-empty');
            if (emptyEl) emptyEl.classList.add('hidden');
            const li = document.createElement('li');
            li.className = 'flex items-center gap-2 text-zinc-700';
            li.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0"></span><span class="truncate" title="' + escapeHtml(page.url) + '">' + escapeHtml(page.title || page.url) + '</span>';
            progressPagesList.appendChild(li);
        }

        function switchToTerminalView() {
            shownPages.clear();
            currentProgressStep = 1;
            if (chatMessagesEl) chatMessagesEl.classList.add('hidden');
            if (crawlerTerminalView) crawlerTerminalView.classList.remove('hidden');
            setProgressStep(1);
            if (progressMessageEl) progressMessageEl.textContent = 'Preparing to scan your website...';
            if (progressPagesList) {
                progressPagesList.innerHTML = '';
                const empty = document.createElement('li');
                empty.id = 'progress-pages-empty';
                empty.className = 'text-zinc-400 italic';
                empty.textContent = 'No pages yet.';
                progressPagesList.appendChild(empty);
            }
            if (terminalCompletedBadge) terminalCompletedBadge.classList.add('hidden');
            var errorAlertEl = document.getElementById('crawler-error-alert');
            if (errorAlertEl) errorAlertEl.classList.add('hidden');
        }

        function switchBackToChat() {
            if (crawlerTerminalView) crawlerTerminalView.classList.add('hidden');
            if (chatMessagesEl) chatMessagesEl.classList.remove('hidden');
            if (terminalCompletedBadge) terminalCompletedBadge.classList.add('hidden');
            lucide.createIcons();
        }

        // --- Real-Time Crawler Monitoring ---
        let isScraping = false;
        let shownPages = new Set();
        let crawlerChannel = null;
        let timerUpdateInterval = null;
        let lastLogTime = 0;
        let currentProgressStep = 1;
        const TERMINAL_VIEW_DELAY_MS = 10000; // How long to keep terminal view visible before switching back (e.g. for screenshots)

        function startCrawlerListener() {
            const sessionId = '{{ Auth::id() }}';
            console.log('🎧 Starting WebSocket listener for session:', sessionId);

            if (crawlerChannel) {
                window.Echo.leave(`crawler.${sessionId}`);
            }

            lastLogTime = Date.now();
            const startTime = Date.now();
            if (terminalScanTimer) terminalScanTimer.textContent = '0.0s';

            // No frontend timeout: timer just shows elapsed time until backend sends done/error
            timerUpdateInterval = setInterval(() => {
                const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
                if (terminalScanTimer) terminalScanTimer.textContent = elapsed + 's';
            }, 100);

            crawlerChannel = window.Echo.private(`crawler.${sessionId}`)
                .listen('.progress', (data) => {
                    lastLogTime = Date.now();

                    if (data.phase || data.userMessage) {
                        if (data.userMessage && progressMessageEl) progressMessageEl.textContent = data.userMessage;
                        const stepMap = { analyzing: 1, crawling: 2, extracting: 3, completed: 4 };
                        if (data.phase && stepMap[data.phase] !== undefined) {
                            currentProgressStep = stepMap[data.phase];
                            setProgressStep(currentProgressStep);
                        }
                    }

                    if (data.pageDiscovered) {
                        const page = data.pageDiscovered;
                        addDiscoveredPage(page);
                        showPageDiscoveryToast(page.title || 'Page Found', page.url);
                        if (currentProgressStep === 1) {
                            currentProgressStep = 2;
                            setProgressStep(2);
                            if (progressMessageEl) progressMessageEl.textContent = 'Scanning your website...';
                        }
                    }

                    if (data.done && data.finalJson) {
                        console.log('✅ Crawl Complete!', data.finalJson);
                        isScraping = false;
                        setProgressStep(4);
                        if (progressMessageEl) progressMessageEl.textContent = 'Ready. You can ask me anything about your business.';
                        if (terminalSpinner) terminalSpinner.classList.add('hidden');
                        if (terminalCompletedBadge) terminalCompletedBadge.classList.remove('hidden');
                        stopCrawlTimer();
                        window.Echo.leave(`crawler.${sessionId}`);
                        crawlerChannel = null;
                        lucide.createIcons();
                        setTimeout(switchBackToChat, TERMINAL_VIEW_DELAY_MS);
                    }

                    if (data.error) {
                        console.error('❌ Crawl Error:', data.error);
                        isScraping = false;
                        var errorAlertEl = document.getElementById('crawler-error-alert');
                        var errorTextEl = document.getElementById('crawler-error-text');
                        if (errorAlertEl) errorAlertEl.classList.remove('hidden');
                        if (errorTextEl) errorTextEl.textContent = data.error;
                        showErrorToast(data.error);
                        stopCrawlTimer();
                        window.Echo.leave(`crawler.${sessionId}`);
                        crawlerChannel = null;
                        setTimeout(switchBackToChat, TERMINAL_VIEW_DELAY_MS);
                    }

                    if (data.done && !data.finalJson && !data.error) {
                        isScraping = false;
                        stopCrawlTimer();
                        window.Echo.leave(`crawler.${sessionId}`);
                        crawlerChannel = null;
                        setTimeout(switchBackToChat, TERMINAL_VIEW_DELAY_MS);
                    }
                });
        }

        function stopCrawlTimer() {
            if (terminalSpinner) terminalSpinner.classList.add('hidden');
            if (timerUpdateInterval) {
                clearInterval(timerUpdateInterval);
                timerUpdateInterval = null;
            }
        }

        function showErrorToast(error) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'bg-red-500 text-white px-4 py-3 rounded-lg shadow-lg pointer-events-auto';
            toast.style.animation = 'slide-in-right 0.3s ease-out';
            toast.innerHTML = `
                <div class="font-semibold">❌ Crawl Failed</div>
                <div class="text-sm opacity-90">${escapeHtml(error)}</div>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.animation = 'slide-out-right 0.3s ease-in';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        function parseLogForPageDiscovery(logText) {
            const discoveries = [];
            const lines = logText.split('\n');

            lines.forEach(line => {
                const match = line.match(/Processing (https?:\/\/[^\s]+) - (.+)/);
                if (match) {
                    discoveries.push({
                        url: match[1],
                        title: match[2].trim(),
                        timestamp: Date.now()
                    });
                }
            });

            return discoveries;
        }

        function showPageDiscoveryToast(pageTitle, pageUrl) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'toast-notification animate-slide-in-right pointer-events-auto';
            toast.innerHTML = `
                <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-lg p-3 shadow-lg max-w-sm">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-emerald-900 truncate">${escapeHtml(pageTitle)}</p>
                        <p class="text-xs text-emerald-600 truncate">${escapeHtml(pageUrl)}</p>
                    </div>
                </div>
            `;

            container.appendChild(toast);
            lucide.createIcons();

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        function displayFinalJSON(pages) {
            const container = document.getElementById('json-display');
            const content = document.getElementById('json-content');
            const count = document.getElementById('page-count');

            if (!container || !content || !count) return;

            container.classList.remove('hidden');
            count.textContent = pages.length;

            // Pretty-print JSON
            const pre = document.createElement('pre');
            pre.className = 'text-zinc-700';
            pre.textContent = JSON.stringify(pages, null, 2);
            content.innerHTML = '';
            content.appendChild(pre);

            // Show completion toast
            showPageDiscoveryToast(`Crawl Complete - ${pages.length} pages`, 'Ready for review');
        }

        // --- WebSocket Integration ---


        // --- WebSocket Integration ---

        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        async function pollStatus() {
            try {
                const response = await fetch('{{ route("onboarding.status") }}?t=' + new Date().getTime());
                const data = await response.json();

                // Real-Time Page Discovery
                if (data.crawler_logs && data.crawler_logs.length > lastLogLength) {
                    lastLogLength = data.crawler_logs.length;
                    const discoveries = parseLogForPageDiscovery(data.crawler_logs);

                    discoveries.forEach(page => {
                        if (!shownPages.has(page.url)) {
                            showPageDiscoveryToast(page.title, page.url);
                            shownPages.add(page.url);
                        }
                    });
                }

                // Crawl completed (poll fallback): switch back to chat, no JSON
                if (data.crawler_raw && data.scraper_status === 'COMPLETED' && isScraping) {
                    console.log("🚀 Scrape Complete (poll).");
                    isScraping = false;
                    stopCrawlTimer();
                    switchBackToChat();
                    showPageDiscoveryToast('Crawl complete', 'You can continue the conversation.');
                }

                // Handle Failure
                if (data.scraper_status === 'FAILED' && isScraping) {
                    console.error("❌ Crawl Failed!");
                    isScraping = false;
                    stopCrawlTimer();
                    showErrorToast(data.crawler_logs || 'Crawl failed');
                    switchBackToChat();
                }

            } catch (e) { console.error("Polling error", e); }
        }

        function stopTimerUI() {
            stopCrawlTimer();
        }

        async function sendHiddenMessage(msg) {
            const typingIndicator = document.getElementById('typing-indicator');
            typingIndicator.classList.remove('hidden');

            try {
                const response = await fetch('{{ route("onboarding.chat") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ message: msg })
                });
                const data = await response.json();
                typingIndicator.classList.add('hidden');
                if (data.reply) appendMessage('assistant', data.reply);
            } catch (e) { console.error(e); }
        }

        function markCollected(el) {
            if (el.classList.contains('collected')) return;
            el.classList.add('collected');
            el.classList.remove('text-zinc-500');
            el.classList.add('text-zinc-900', 'font-medium');

            const circle = el.querySelector('div');
            circle.classList.remove('border-zinc-300', 'bg-white');
            circle.classList.add('bg-emerald-500', 'border-emerald-500', 'text-white');

            const check = el.querySelector('span');
            check.classList.remove('opacity-0', 'scale-50');
        }

        // ✅ DISABLED: Polling replaced with WebSocket real-time updates
        // setInterval(pollStatus, 3000);
        // pollStatus();
        // Note: pollStatus still available for checklist updates if needed
    </script>
</body>

</html>