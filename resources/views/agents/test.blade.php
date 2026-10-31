<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Voice Simulator</h1>
                <p class="text-slate-500 mt-1">Experience your agent as your customers will.</p>
            </div>
            <a href="{{ route('dashboard') }}"
                class="text-sm text-slate-500 hover:text-slate-900 font-medium flex items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="max-w-md mx-auto py-8 h-[calc(100vh-10rem)]" x-data="voiceSimulator()">
        <!-- Phone Simulator Container -->
        <div
            class="bg-white rounded-[2.5rem] shadow-2xl border-[8px] border-slate-900 h-full flex flex-col relative overflow-hidden ring-1 ring-black/5 mx-auto max-w-[400px]">

            <!-- Top Bar (Mock) -->
            <div class="absolute top-0 inset-x-0 h-7 bg-slate-900 z-50 flex items-center justify-between px-6">
                <span class="text-[10px] text-white font-medium">9:41</span>
                <div class="flex gap-1.5">
                    <span class="w-3 h-3 text-white"><i data-lucide="wifi" class="w-3 h-3"></i></span>
                    <span class="w-3 h-3 text-white"><i data-lucide="battery-full" class="w-3 h-3"></i></span>
                </div>
            </div>

            <!-- Call Screen Content -->
            <div
                class="flex-1 flex flex-col items-center pt-8 px-6 bg-gradient-to-b from-slate-50 to-slate-100 relative">

                <!-- Caller Info -->
                <div class="text-center mb-4 animate-fade-in-down">
                    <div
                        class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-100 to-indigo-50 flex items-center justify-center mx-auto mb-4 shadow-inner ring-4 ring-white">
                        <i data-lucide="bot" class="w-10 h-10 text-indigo-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-1">{{ $agent->name }}</h2>
                    <p class="text-sm font-medium text-emerald-600 flex items-center justify-center gap-1.5">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        Voice Active
                    </p>
                </div>

                <!-- Live Transcript / Visualizer Area -->
                <div class="flex-1 w-full flex flex-col items-center justify-center py-4">
                    <!-- Waveform Visualizer (CSS Simulation) -->
                    <div class="flex gap-1 h-12 items-center justify-center mb-4"
                        x-show="status === 'speaking' || status === 'listening'">
                        <template x-for="i in 5">
                            <div class="w-1.5 bg-indigo-500 rounded-full animate-wave"
                                :class="status === 'listening' ? 'bg-indigo-500' : 'bg-emerald-500'"
                                :style="`animation-duration: ${Math.random() * 0.5 + 0.5}s; height: ${Math.random() * 24 + 12}px`">
                            </div>
                        </template>
                    </div>

                    <!-- Transcript Text -->
                    <p class="text-lg font-medium text-slate-700 text-center leading-relaxed transition-all duration-300 px-4"
                        x-text="currentText || (status === 'idle' ? 'Tap the microphone to say hello.' : 'Listening...')"
                        :class="{'opacity-50 blur-[0.5px]': status === 'thinking', 'opacity-100': status !== 'thinking'}">
                    </p>
                </div>

                <!-- Controls -->
                <div class="pb-8 w-full px-8">
                    <div class="flex justify-center gap-8 items-center">

                        <!-- Mute / Secondary (Disabled for demo) -->
                        <button disabled
                            class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
                            <i data-lucide="mic-off" class="w-5 h-5"></i>
                        </button>

                        <!-- End Call / Main Action -->
                        <button @click="toggleSession"
                            class="w-16 h-16 rounded-full flex items-center justify-center shadow-xl transition-all duration-200 transform hover:scale-105 active:scale-95"
                            :class="status === 'listening' 
                                ? 'bg-red-500 text-white shadow-red-200 hover:bg-red-600' 
                                : 'bg-indigo-600 text-white shadow-indigo-200 hover:bg-indigo-700'">
                            <i data-lucide="mic" class="w-7 h-7" x-show="status !== 'listening'"></i>
                            <i data-lucide="square" class="w-7 h-7 fill-current" x-show="status === 'listening'"></i>
                        </button>

                        <!-- Keypad (Disabled for demo) -->
                        <button disabled
                            class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
                            <i data-lucide="grip-horizontal" class="w-5 h-5"></i>
                        </button>

                    </div>
                    <p class="text-center text-xs font-semibold text-slate-400 mt-4 uppercase tracking-wider"
                        x-text="statusLabel"></p>
                </div>

            </div>

            <!-- Error Toast -->
            <div x-show="error" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-full"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="absolute bottom-24 inset-x-6 bg-red-50 text-red-600 px-4 py-3 rounded-xl text-xs font-medium border border-red-100 text-center shadow-lg z-50">
                <span x-text="error"></span>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('voiceSimulator', () => ({
                status: 'idle', // idle, listening, thinking, speaking
                currentText: '',
                messages: [],
                error: null,
                ws: null,
                audioContext: null,
                gainNode: null,
                processor: null,
                stream: null,
                agentId: '{{ $agent->id }}',
                audioQueue: [],
                nextPlayTime: 0,

                get statusLabel() {
                    switch (this.status) {
                        case 'idle': return 'Connect to Start';
                        case 'listening': return 'Live - Speak Now';
                        case 'thinking': return 'Brain Thinking...';
                        case 'speaking': return 'AI Speaking...';
                    }
                },

                async init() {
                    this.playHaptic();
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                },

                playHaptic() {
                    if (navigator.vibrate) navigator.vibrate(50);
                },

                async toggleSession() {
                    if (this.status === 'idle') {
                        await this.startSession();
                    } else {
                        this.stopSession();
                    }
                },

                async startSession() {
                    this.status = 'thinking';
                    this.error = null;
                    this.nextPlayTime = 0;
                    this.audioQueue = [];

                    try {
                        // 1. Setup WebSocket
                        this.ws = new WebSocket(`ws://localhost:3000/voice-test?agent_id=${this.agentId}`);
                        this.ws.binaryType = 'arraybuffer';

                        this.ws.onopen = () => {
                            this.status = 'listening';
                            this.startAudioCapture();
                        };

                        this.ws.onmessage = async (event) => {
                            if (typeof event.data === 'string') {
                                const data = JSON.parse(event.data);
                                this.handleJsonEvent(data);
                            } else {
                                await this.queueAudioChunk(event.data);
                            }
                        };

                        this.ws.onclose = () => this.stopSession();
                        this.ws.onerror = (e) => {
                            this.error = "Connection Failed. Is realtime-server running?";
                            this.stopSession();
                        };

                    } catch (e) {
                        this.error = "Failed to initialize: " + e.message;
                        this.status = 'idle';
                    }
                },

                handleJsonEvent(data) {
                    if (data.type === 'token') {
                        this.status = 'speaking';
                        this.currentText += data.text;
                        this.restoreAudio();
                    } else if (data.type === 'transcript') {
                        // Human speech detected — show thinking state while LLM processes
                        this.status = 'thinking';
                        this.currentText = '';
                    } else if (data.type === 'listening') {
                        // AI finished speaking — reset so user can respond
                        this.status = 'listening';
                        this.currentText = '';
                    } else if (data.type === 'audio_dim') {
                        this.duckAudio();
                    } else if (data.type === 'audio_kill') {
                        this.clearAudioQueue();
                        this.status = 'listening';
                    } else if (data.type === 'error') {
                        this.error = data.message;
                    }
                },

                async startAudioCapture() {
                    this.audioContext = new (window.AudioContext || window.webkitAudioContext)({ sampleRate: 16000 });
                    
                    // APEX AUDIO GRAPH: Add GainNode for Ducking
                    this.gainNode = this.audioContext.createGain();
                    this.gainNode.connect(this.audioContext.destination);

                    // INDUSTRIAL AEC: Explicitly disable noise suppression to let Deepgram's AI handle it natively
                    this.stream = await navigator.mediaDevices.getUserMedia({ 
                        audio: {
                            echoCancellation: true,
                            noiseSuppression: false, // Turned off to prevent word clipping
                            autoGainControl: false,  // Turned off to prevent volume distortion
                            channelCount: 1,
                            sampleRate: 16000
                        } 
                    });
                    
                    const source = this.audioContext.createMediaStreamSource(this.stream);

                    // Binary Processor (Linear PCM 16-bit)
                    this.processor = this.audioContext.createScriptProcessor(4096, 1, 1);
                    source.connect(this.processor);
                    this.processor.connect(this.audioContext.destination);

                    this.processor.onaudioprocess = (e) => {
                        // Audio must flow to Deepgram continuously — even during 'thinking'.
                        // Deepgram needs the audio stream to detect utterance_end (silence).
                        // Only stop during 'idle' (not connected).
                        if (this.status === 'idle') return;
                        
                        const inputData = e.inputBuffer.getChannelData(0);
                        const pcm16 = this.floatTo16BitPcm(inputData);
                        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
                            this.ws.send(pcm16);
                        }
                    };
                },

                duckAudio() {
                    if (this.gainNode) {
                        this.gainNode.gain.setTargetAtTime(0.2, this.audioContext.currentTime, 0.1);
                        console.log("[Apex] Ducking Audio (20%)");
                    }
                },

                restoreAudio() {
                    if (this.gainNode) {
                        this.gainNode.gain.setTargetAtTime(1.0, this.audioContext.currentTime, 0.1);
                    }
                },

                async queueAudioChunk(arrayBuffer) {
                    if (this.status === 'idle') return;
                    this.status = 'speaking';
                    
                    const pcm16 = new Int16Array(arrayBuffer);
                    const audioBuffer = this.audioContext.createBuffer(1, pcm16.length, 16000);
                    const nowBuffering = audioBuffer.getChannelData(0);
                    
                    for (let i = 0; i < pcm16.length; i++) {
                        nowBuffering[i] = pcm16[i] / 32768;
                    }

                    const source = this.audioContext.createBufferSource();
                    source.buffer = audioBuffer;
                    source.connect(this.gainNode); // Connect to GainNode instead of direct destination

                    // Determine play time (gapless sequencing)
                    const currentTime = this.audioContext.currentTime;
                    if (this.nextPlayTime < currentTime) {
                        this.nextPlayTime = currentTime + 0.05; // Small buffer for initial chunk
                    }

                    source.start(this.nextPlayTime);
                    this.nextPlayTime += audioBuffer.duration;
                    
                    this.audioQueue.push(source);
                },

                clearAudioQueue() {
                    console.log("[Apex] HARD PURGE: Wiping playback queue and resetting play clock");
                    
                    // 1. Stop all buffered sources immediately
                    this.audioQueue.forEach(source => {
                        try { 
                            source.stop(0); // Stop NOW
                            source.disconnect(); 
                        } catch(e) {}
                    });
                    
                    // 2. Wipe the analytical state
                    this.audioQueue = [];
                    this.nextPlayTime = 0;
                    
                    // 3. Force GainNode back to normal if it was ducked
                    this.restoreAudio();
                    
                    // 4. (Optional) Flush the audio context state if it was stuttering
                    if (this.audioContext && this.audioContext.state === 'suspended') {
                        this.audioContext.resume();
                    }
                },

                floatTo16BitPcm(input) {
                    let output = new Int16Array(input.length);
                    for (let i = 0; i < input.length; i++) {
                        let s = Math.max(-1, Math.min(1, input[i]));
                        output[i] = s < 0 ? s * 0x8000 : s * 0x7FFF;
                    }
                    return output.buffer;
                },

                stopSession() {
                    this.status = 'idle';
                    this.clearAudioQueue();
                    if (this.ws) this.ws.close();
                    if (this.stream) this.stream.getTracks().forEach(t => t.stop());
                    if (this.audioContext) this.audioContext.close();
                    this.currentText = '';
                }
            }))
        })
    </script>
        <style>
            @keyframes wave {

                0%,
                100% {
                    height: 12px;
                }

                50% {
                    height: 32px;
                }
            }

            .animate-wave {
                animation: wave 1s ease-in-out infinite;
            }

            .animate-fade-in-down {
                animation: fadeInDown 0.5s ease-out;
            }

            @keyframes fadeInDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    @endpush
</x-app-layout>