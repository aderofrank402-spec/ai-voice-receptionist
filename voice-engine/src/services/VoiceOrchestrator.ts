import axios from 'axios';
import { DeepgramService } from './DeepgramService';
import { GroqService } from './GroqService';
import { ElevenLabsService } from './ElevenLabsService';
import { RecordingService } from './RecordingService';
import { FillerAudioService } from './FillerAudioService';
import { EventEmitter } from 'events';

const LARAVEL_BASE = process.env.LARAVEL_BASE_URL || 'http://127.0.0.1:8000';

// ─────────────────────────────────────────────────────────────────────────────
// THE 8-STATE CONVERSATION MACHINE
// ─────────────────────────────────────────────────────────────────────────────
export enum ConversationState {
    IDLE = 'IDLE',
    LISTENING = 'LISTENING',
    THINKING = 'THINKING',
    DELIVERING = 'DELIVERING',
    SPEAKING = 'SPEAKING',
    INTERRUPTED = 'INTERRUPTED',
    RECOVERY = 'RECOVERY',
    AWAITING_PAYMENT = 'AWAITING_PAYMENT',
}

// ─────────────────────────────────────────────────────────────────────────────
// INTENT CLASSIFICATION DATA
// Tier 1: Immediate kill — any single match triggers INTERRUPTED instantly.
// Language-aware: includes Swahili/Sheng variants for Kenyan callers.
// ─────────────────────────────────────────────────────────────────────────────
const TIER1_INTERRUPTS = new Set([
    'stop', 'wait', 'no', 'actually', 'hold on', 'hang on',
    'acha', 'ngoja', 'cancel', 'forget it', 'never mind',
    'nevermind', 'wrong', 'incorrect', 'pause', 'sawa', 'shh',
]);

// Tier 2: Phrase-level high-probability interrupt patterns
const TIER2_PATTERNS: string[] = [
    "that's not right", "i meant", "let me rephrase", "can i change",
    "actually i want", "i said", "not that", "i meant to say",
    "sorry i meant", "no wait", "that's wrong", "let me correct",
    "hold on a second", "wait a moment",
];

// States where barge-in monitoring is active
const BARGE_IN_STATES = new Set<ConversationState>([
    ConversationState.THINKING,
    ConversationState.DELIVERING,
    ConversationState.SPEAKING,
]);

// ─────────────────────────────────────────────────────────────────────────────
// VOICE ORCHESTRATOR — PRODUCTION ENGINE v1 (Phase 1 Implementation)
// ─────────────────────────────────────────────────────────────────────────────
export class VoiceOrchestrator extends EventEmitter {
    // Services
    private deepgram: DeepgramService;
    private groq: GroqService;
    private elevenLabs: ElevenLabsService;
    private recording: RecordingService;
    private filler: FillerAudioService;

    // State machine
    private state: ConversationState = ConversationState.IDLE;

    // Buffers
    private userTurnBuffer: string = '';  // Accumulates is_final transcript segments
    private sentenceBuffer: string = '';  // Accumulates LLM tokens until sentence boundary

    // Conversation
    private history: Array<{
        role: string;
        content: string;
        tool_calls?: any[];
        tool_call_id?: string;
        name?: string;
    }> = [];

    // Metadata
    private callId: string;
    private agentId: number | string = 1;
    private config: any = { media: { output_format: 'pcm_16000' } };
    private startTime: number = Date.now();

    private vadTimer: NodeJS.Timeout | null = null;
    private fillerTimer: NodeJS.Timeout | null = null;
    private healthCheckTimer: NodeJS.Timeout | null = null;
    private paymentPollTimer: NodeJS.Timeout | null = null;
    private paymentPatienceTimer: NodeJS.Timeout | null = null;
    private paymentTimeoutTimer: NodeJS.Timeout | null = null;
    private speakingWatchdog: NodeJS.Timeout | null = null; // Fix 4: SPEAKING stuck state

    // Fix 3 & 6: Zombie tool call protection + compression lock
    private turnId: number = 0;          // Increments on every new user turn
    private isCompressing: boolean = false; // Prevents concurrent history mutations

    constructor() {
        super();
        this.deepgram = new DeepgramService();
        this.groq = new GroqService();
        this.elevenLabs = new ElevenLabsService();
        this.recording = new RecordingService();
        this.filler = new FillerAudioService();
        this.callId = 'call-' + Date.now();

        // Wire filler audio output directly to call audio stream
        this.filler.on('audio', (buf: Buffer) => {
            this.recording.appendOutbound(buf);
            this.emit('audio_output', buf);
        });

        this.setupPipeline();
    }

    // =========================================================================
    // PUBLIC API
    // =========================================================================

    public async start(options?: {
        agentId?: number | string;
        media?: { encoding?: string; sample_rate?: number; output_format?: string };
    }): Promise<void> {
        this.agentId = options?.agentId ?? 1;
        if (options?.media) {
            this.config.media = { ...this.config.media, ...options.media };
        }

        // Load agent config
        try {
            const response = await axios.get(`${LARAVEL_BASE}/api/v1/agents/config?agent_id=${this.agentId}`);
            const config = response.data?.config;
            if (config?.prompt) {
                this.groq.setSystemPrompt(config.prompt);
                console.log(`[VoiceOrchestrator] Agent ${this.agentId} loaded: ${config.business_name}`);
            } else {
                console.warn(`[VoiceOrchestrator] No prompt found for agent ${this.agentId} — using default`);
            }
        } catch (err: any) {
            console.warn('[VoiceOrchestrator] Agent config fallback:', err.message);
        }

        // Reset state
        this.startTime = Date.now();
        this.history = [];
        this.userTurnBuffer = '';
        this.sentenceBuffer = '';

        // Start disk recording (Phase 1: writes to OS temp dir)
        this.recording.startRecording(this.callId);

        // Open both pipelines as warm connections from the start
        this.deepgram.connect({
            encoding: options?.media?.encoding,
            sample_rate: options?.media?.sample_rate,
        });
        await this.elevenLabs.connect(options?.media?.output_format);

        // Pre-warm filler audio in background (parallel — doesn't block the call)
        // By the time the first THINKING state occurs, fillers will be ready.
        this.filler.warmUp(options?.media?.output_format).catch(() => {
            console.warn('[Filler] Warm-up failed — will fall back gracefully');
        });

        // Connection health monitor
        this.startHealthMonitor();

        this.transitionTo(ConversationState.IDLE);
        console.log(`[VoiceOrchestrator] Call ${this.callId} started. Warm pipeline active.`);

        // ── Fix 8: Compliance notice — guaranteed, no race condition ─────────────
        // ElevenLabs is already connected here (await above). No setTimeout needed.
        // Plays immediately, before Deepgram can fire speech_started.
        this.elevenLabs.send("This call may be recorded for quality purposes.");
        this.elevenLabs.flush();
        console.log('[Compliance] Recording notice sent.');
    }

    /** Primary audio ingress — called for every RTP/WebSocket audio packet */
    public receiveAudio(chunk: Buffer): void {
        // TAP: Write to disk-incremental recording (Phase 1 stub)
        this.recording.appendInbound(chunk);
        // Forward to STT
        this.deepgram.send(chunk);
    }

    /** Alias for telephony compatibility */
    public processAudio(buffer: Buffer): void {
        this.receiveAudio(buffer);
    }

    /**
     * Inject a user transcript directly into the state machine.
     * Used by: DTMF handlers (0 = transfer, # = repeat), test-engine.ts test harness.
     * Forces a LISTENING → THINKING transition with the given text as the user turn.
     */
    public injectTranscript(text: string): void {
        console.log(`[InjectTranscript] "${text}"`);
        // Ensure we are in a state that can accept input
        // If busy (THINKING/DELIVERING), the interrupt path will handle it
        if (this.state === ConversationState.THINKING ||
            this.state === ConversationState.DELIVERING) {
            this.executeAtomicInterrupt();
        }
        // Now in IDLE or LISTENING — set the buffer and fire utterance end
        this.userTurnBuffer = text;
        if (this.state !== ConversationState.LISTENING) {
            this.transitionTo(ConversationState.LISTENING);
        }
        this.handleUtteranceEnd();
    }

    public async endCall(status: string = 'completed'): Promise<void> {
        console.log(`[VoiceOrchestrator] Ending call ${this.callId}. Status: ${status}`);

        this.stopAllTimers();
        this.clearVadWatchdog();
        this.groq.abort();
        this.deepgram.disconnect();
        this.elevenLabs.disconnect();

        // Finalize recording (Phase 1: closes streams and logs paths)
        await this.recording.finalizeAndUpload(this.callId);

        // Remove all listeners to prevent zombie processing
        this.deepgram.removeAllListeners();
        this.groq.removeAllListeners();
        this.elevenLabs.removeAllListeners();

        await this.logCall(status);
    }

    public getState(): ConversationState {
        return this.state;
    }


    public setToolsEnabled(enabled: boolean): void {
        this.groq.setToolsEnabled(enabled);
    }

    public waitForTtsReady(timeoutMs = 15000): Promise<void> {
        return new Promise((resolve, reject) => {
            if (this.elevenLabs.isOpen()) { resolve(); return; }
            const onOpen = () => { clearTimeout(t); resolve(); };
            this.elevenLabs.once('connection_open', onOpen);
            const t = setTimeout(() => {
                this.elevenLabs.off('connection_open', onOpen);
                reject(new Error('TTS not ready within ' + timeoutMs + 'ms'));
            }, timeoutMs);
        });
    }

    // =========================================================================
    // STATE MACHINE
    // =========================================================================

    private transitionTo(newState: ConversationState): void {
        if (this.state === newState) return;
        console.log(`[State] ${this.state} → ${newState}`);
        this.state = newState;
        this.emit('state_change', newState);
    }

    // =========================================================================
    // PIPELINE SETUP — Wires all service events to the state machine
    // =========================================================================

    private setupPipeline(): void {

        // ── DEEPGRAM ─────────────────────────────────────────────────────────

        this.deepgram.on('close', () => {
            console.warn('[Deepgram] Connection closed unexpectedly.');
        });

        this.deepgram.on('error', (err: any) => {
            // Absorb Deepgram errors — prevent Node.js unhandled error crash.
            // The VoiceOrchestrator will enter RECOVERY state if needed.
            const msg = err?.message || err?.error?.message || String(err);
            console.error('[Deepgram] Connection error (absorbed):', msg);
            // If we are mid-conversation, enter recovery
            if (this.state !== ConversationState.IDLE &&
                this.state !== ConversationState.RECOVERY) {
                this.enterRecovery();
            }
        });

        this.deepgram.on('speech_started', () => {
            // Any sound during IDLE moves us to LISTENING
            if (this.state === ConversationState.IDLE) {
                this.userTurnBuffer = '';
                this.transitionTo(ConversationState.LISTENING);
                this.startVadWatchdog();
            }
        });

        // Fires for every is_final=true transcript segment
        this.deepgram.on('transcript', (text: string) => {
            if (!text.trim()) return;
            this.handleFinalTranscript(text.trim());
        });

        // Fires when Deepgram detects end-of-utterance (silence window)
        // THIS is the definitive "user finished their turn" signal
        this.deepgram.on('utterance_end', () => {
            this.handleUtteranceEnd();
        });

        // ── GROQ ─────────────────────────────────────────────────────────────

        this.groq.on('token', async (token: string) => {
            // Discard tokens if we are interrupted or in wrong state
            if (this.state === ConversationState.INTERRUPTED) return;
            if (this.state === ConversationState.AWAITING_PAYMENT) return;

            // First token: THINKING → DELIVERING
            if (this.state === ConversationState.THINKING) {
                this.stopFillerTimer(); // Cancel filler — real response is coming
                this.transitionTo(ConversationState.DELIVERING);
            }

            if (this.state !== ConversationState.DELIVERING) return;

            // Ensure ElevenLabs is open (should be pre-warmed, but guarantee it)
            await this.elevenLabs.ensureOpen(this.config.media.output_format);

            this.emit('token', token);
            this.handleSentenceBoundaryStreaming(token);
        });

        this.groq.on('complete', async (fullText: string) => {
            // Silently discard if we got interrupted mid-response
            if (this.state === ConversationState.INTERRUPTED) return;

            // Wait until ElevenLabs is fully connected before flushing
            await this.elevenLabs.ensureOpen(this.config.media.output_format);

            // Flush any remaining token buffer
            this.flushSentenceBuffer();
            this.elevenLabs.flush(); // IMPORTANT: Triggers ElevenLabs to output the final audio!

            if (fullText.trim()) {
                this.history.push({ role: 'assistant', content: fullText });
            }

            this.transitionTo(ConversationState.SPEAKING);
            this.stopFillerTimer();
            this.saveCallLog();

            // Fix 4: Start SPEAKING watchdog — forces IDLE if isFinal never fires (30s max)
            this.clearSpeakingWatchdog();
            this.speakingWatchdog = setTimeout(() => {
                if (this.state === ConversationState.SPEAKING) {
                    console.warn('[Watchdog] SPEAKING timeout — forcing IDLE');
                    this.transitionTo(ConversationState.IDLE);
                }
            }, 30000);

            // Fix 5 & 6: Compress AFTER turn completes, with lock — not before generateResponse
            if (!this.isCompressing) {
                this.isCompressing = true;
                this.compressHistoryIfNeeded().finally(() => {
                    this.isCompressing = false;
                });
            }
        });

        this.groq.on('error', (err: any) => {
            if (this.state === ConversationState.INTERRUPTED) return;
            console.error('[Groq Error]:', err?.message || err);
            this.enterRecovery();
        });

        this.groq.on('tool_trigger', async (toolData: any) => {
            await this.handleToolCall(toolData);
        });

        // ── ELEVENLABS ───────────────────────────────────────────────────────

        this.elevenLabs.on('audio', (audioChunk: Buffer) => {
            // TAP: Write to disk-incremental recording (Phase 1 stub)
            this.recording.appendOutbound(audioChunk);
            this.emit('audio_output', audioChunk);
        });

        // ElevenLabs signals all audio has been delivered
        this.elevenLabs.on('audio_complete', () => {
            this.clearSpeakingWatchdog(); // Fix 4: cancel watchdog — normal completion
            if (this.state === ConversationState.SPEAKING) {
                this.transitionTo(ConversationState.IDLE);
            }
            // Fix 3: Re-emit so telephony layer and test harness can listen
            this.emit('audio_complete');
        });
    }

    // =========================================================================
    // TRANSCRIPT HANDLING
    // =========================================================================

    private handleFinalTranscript(text: string): void {
        this.clearVadWatchdog(); // Real words received, cancel false positive listener
        
        // Always emit for dashboard display
        this.emit('transcript', text);

        // ── AWAITING_PAYMENT: Narrow Exception ──────────────────────────────
        // Only Tier 1 hotwords are active. All other speech is silently dropped.
        if (this.state === ConversationState.AWAITING_PAYMENT) {
            if (this.isTier1Match(text)) {
                console.log(`[Payment] Cancellation hotword detected: "${text}"`);
                this.cancelPaymentWait();
                this.history.push({ role: 'user', content: text });
                this.transitionTo(ConversationState.THINKING);
                this.groq.generateResponse(this.history, false);
            }
            // All other speech: silently swallowed
            return;
        }

        // ── BARGE-IN CHECK ───────────────────────────────────────────────────
        if (BARGE_IN_STATES.has(this.state)) {
            const signal = this.classifyInterrupt(text);

            if (signal === 'TIER1' || signal === 'TIER2') {
                console.log(`[Barge-In] ${signal} interrupt: "${text}"`);
                this.executeAtomicInterrupt();
                // Push the interrupting speech as the new user turn
                this.userTurnBuffer = text;
                return;
            }

            if (signal === 'TIER3_CHECK') {
                // Fire Tier 3 micro-call asynchronously — never block the pipeline
                this.checkTier3Interrupt(text).then(isInterrupt => {
                    if (isInterrupt && BARGE_IN_STATES.has(this.state)) {
                        console.log(`[Barge-In] Tier 3 confirmed: "${text}"`);
                        this.executeAtomicInterrupt();
                        this.userTurnBuffer = text;
                    }
                }).catch(() => { /* Default: don't interrupt */ });
                return;
            }

            // Not an interrupt — but we are AI-busy, so we accumulate
            // (user may be adding to a thought while AI is thinking)
            this.userTurnBuffer = this.userTurnBuffer
                ? `${this.userTurnBuffer} ${text}`
                : text;
            return;
        }

        // ── LISTENING: Accumulate ────────────────────────────────────────────
        if (this.state === ConversationState.LISTENING || this.state === ConversationState.IDLE) {
            if (this.state === ConversationState.IDLE) {
                this.userTurnBuffer = '';
                this.transitionTo(ConversationState.LISTENING);
            }
            this.userTurnBuffer = this.userTurnBuffer
                ? `${this.userTurnBuffer} ${text}`
                : text;
            console.log(`[Listening] Buffer: "${this.userTurnBuffer}"`);
        }
    }

    /**
     * utterance_end: The definitive "user finished talking" signal from Deepgram.
     * Only transitions from LISTENING → THINKING.
     */
    private handleUtteranceEnd(): void {
        if (this.state !== ConversationState.LISTENING) return;

        const fullUtterance = this.userTurnBuffer.trim();
        if (!fullUtterance) return;

        this.userTurnBuffer = '';
        console.log(`[Utterance End] Full turn: "${fullUtterance}"`);

        this.history.push({ role: 'user', content: fullUtterance });
        this.turnId++;  // Fix 3: new turn invalidates any in-flight tool calls
        this.transitionTo(ConversationState.THINKING);

        // Start filler timer: if LLM takes > 400ms, play a natural filler
        this.startFillerTimer();

        // Pre-warm ElevenLabs socket in background while Groq thinks
        this.elevenLabs.ensureOpen(this.config.media.output_format).catch(() => { });

        // Trigger LLM
        this.groq.generateResponse(this.history, false);
    }

    // =========================================================================
    // SENTENCE-BOUNDARY STREAMING
    // =========================================================================

    /**
     * Accumulates LLM tokens. Flushes to ElevenLabs at every sentence boundary.
     * This creates the parallel pipeline: sentence N plays while sentence N+1 generates.
     */
    private handleSentenceBoundaryStreaming(token: string): void {
        this.sentenceBuffer += token;

        // Flush on natural sentence boundary with minimum content
        const hasBoundary = /[.!?;:\n]/.test(token);
        if (hasBoundary && this.sentenceBuffer.trim().length > 8) {
            const sentence = this.injectProsodyHints(this.sentenceBuffer.trim());
            this.sentenceBuffer = '';
            this.elevenLabs.send(sentence);
            return;
        }

        // Soft flush: if buffer grows too large, break at the last word boundary
        // This prevents hanging when the LLM writes a very long sentence
        if (this.sentenceBuffer.length > 150) {
            const lastSpace = this.sentenceBuffer.lastIndexOf(' ');
            if (lastSpace > 80) {
                const chunk = this.sentenceBuffer.substring(0, lastSpace).trim();
                this.sentenceBuffer = this.sentenceBuffer.substring(lastSpace + 1);
                if (chunk.length > 0) {
                    this.elevenLabs.send(chunk);
                }
            }
        }
    }

    /** Flush remaining token buffer and send EOS to ElevenLabs */
    private flushSentenceBuffer(): void {
        if (this.sentenceBuffer.trim().length > 0) {
            this.elevenLabs.send(this.injectProsodyHints(this.sentenceBuffer.trim()));
            this.sentenceBuffer = '';
        }
        this.elevenLabs.flush();
    }

    // =========================================================================
    // INTENT CLASSIFICATION (3-TIER)
    // =========================================================================

    private isTier1Match(text: string): boolean {
        const lower = text.toLowerCase().trim();

        // 1. Exact full match
        if (TIER1_INTERRUPTS.has(lower)) return true;

        // 2. Check multi-word TIER1 phrases are CONTAINED anywhere in input
        //    e.g. "never mind" or "forget it" wouldn't match word-by-word
        for (const phrase of TIER1_INTERRUPTS) {
            if (phrase.includes(' ') && lower.includes(phrase)) return true;
        }

        // 3. Any of the first 3 words is a single-word Tier 1 match
        const words = lower.split(/\s+/).slice(0, 3);
        return words.some(w => TIER1_INTERRUPTS.has(w));
    }

    private classifyInterrupt(text: string): 'TIER1' | 'TIER2' | 'TIER3_CHECK' | 'NONE' {
        const lower = text.toLowerCase().trim();

        if (this.isTier1Match(text)) return 'TIER1';

        const isTier2 = TIER2_PATTERNS.some(p => lower.includes(p));
        if (isTier2) return 'TIER2';

        // Only send to Tier 3 if > 3 words (single words already handled by Tier 1/2)
        if (text.split(/\s+/).length >= 3) return 'TIER3_CHECK';

        return 'NONE';
    }

    private async checkTier3Interrupt(text: string): Promise<boolean> {
        try {
            return await this.groq.classifyInterrupt(text);
        } catch {
            return false; // Safety: default to NOT interrupting
        }
    }

    // =========================================================================
    // INTERRUPTED STATE — ATOMIC CLEANUP
    // =========================================================================

    private executeAtomicInterrupt(): void {
        this.transitionTo(ConversationState.INTERRUPTED);

        // Step 1: Kill LLM generation immediately
        this.groq.abort();

        // Step 2: Kill TTS stream gracefully
        this.elevenLabs.closeStream();

        // Step 3: Kill frontend audio buffer
        this.emit('audio_output_kill');

        // Step 4: Clear all text buffers
        this.sentenceBuffer = '';

        // Step 5: Cancel pending timers including SPEAKING watchdog
        this.stopFillerTimer();
        this.clearSpeakingWatchdog(); // Fix 4

        // Step 6 (Fix 3): Increment turnId to invalidate any zombie tool calls
        this.turnId++;

        // Ready for next user turn
        this.transitionTo(ConversationState.LISTENING);

        console.log('[Interrupted] Atomic cleanup complete. Ready for user.');
    }

    // =========================================================================
    // RECOVERY STATE
    // =========================================================================

    private enterRecovery(): void {
        this.transitionTo(ConversationState.RECOVERY);
        console.warn('[Recovery] API failure detected. Entering recovery.');

        const fillers = [
            "Sorry, give me just one moment.",
            "One second, let me get that for you.",
            "Apologies for the pause, let me try that again.",
        ];
        const filler = fillers[Math.floor(Math.random() * fillers.length)];

        // Play filler immediately so call feels alive
        this.elevenLabs.ensureOpen(this.config.media.output_format).then(() => {
            if (this.state === ConversationState.RECOVERY) {
                this.elevenLabs.send(filler);
                this.elevenLabs.flush();
            }
        }).catch(() => { });

        // Retry the last turn after 2 seconds
        setTimeout(() => {
            if (this.state === ConversationState.RECOVERY && this.history.length > 0) {
                console.log('[Recovery] Retrying last turn...');
                this.transitionTo(ConversationState.THINKING);
                this.groq.generateResponse(this.history, false);
            }
        }, 2000);
    }

    // =========================================================================
    // AWAITING_PAYMENT STATE
    // =========================================================================

    private enterPaymentWait(): void {
        this.transitionTo(ConversationState.AWAITING_PAYMENT);
        console.log('[Payment] Entering AWAITING_PAYMENT state. Polling every 3s.');

        // Patience message every 30 seconds
        this.paymentPatienceTimer = setInterval(() => {
            if (this.state !== ConversationState.AWAITING_PAYMENT) return;
            this.elevenLabs.ensureOpen(this.config.media.output_format).then(() => {
                this.elevenLabs.send("Still waiting on your end — no rush at all.");
                this.elevenLabs.flush();
            }).catch(() => { });
        }, 30000);

        // 3-minute timeout
        this.paymentTimeoutTimer = setTimeout(() => {
            if (this.state !== ConversationState.AWAITING_PAYMENT) return;
            console.log('[Payment] 3-minute timeout reached.');
            this.cancelPaymentWait();

            this.elevenLabs.ensureOpen(this.config.media.output_format).then(() => {
                this.elevenLabs.send("I didn't receive a payment confirmation. Would you like me to resend the prompt?");
                this.elevenLabs.flush();
            }).catch(() => { });

            this.transitionTo(ConversationState.IDLE);
        }, 180000);

        // Poll M-Pesa callback endpoint every 3 seconds
        this.paymentPollTimer = setInterval(async () => {
            if (this.state !== ConversationState.AWAITING_PAYMENT) return;
            try {
                const res = await axios.get(`${LARAVEL_BASE}/api/v1/payments/status?call_id=${this.callId}`);
                if (res.data?.status === 'completed') {
                    console.log('[Payment] Confirmed! Resuming conversation.');
                    this.cancelPaymentWait();

                    this.history.push({
                        role: 'tool',
                        tool_call_id: 'mpesa_' + this.callId,
                        name: 'trigger_mpesa_payment',
                        content: 'Payment confirmed successfully.',
                    } as any);
                    this.history.push({ role: 'user', content: 'My payment went through.' });
                    this.transitionTo(ConversationState.THINKING);
                    this.groq.generateResponse(this.history, false);
                }
            } catch {
                // Silently continue polling
            }
        }, 3000);
    }

    private cancelPaymentWait(): void {
        if (this.paymentPollTimer) { clearInterval(this.paymentPollTimer); this.paymentPollTimer = null; }
        if (this.paymentPatienceTimer) { clearInterval(this.paymentPatienceTimer); this.paymentPatienceTimer = null; }
        if (this.paymentTimeoutTimer) { clearTimeout(this.paymentTimeoutTimer); this.paymentTimeoutTimer = null; }
    }

    // =========================================================================
    // TOOL CALL HANDLER (Phase 4: Validation-First + Self-Correction)
    // =========================================================================

    /**
     * Local schema validation — runs BEFORE any API call is made.
     * Returns the name of the first missing required field, or null if valid.
     */
    private validateToolCall(name: string, args: any): string | null {
        if (name === 'book_appointment') {
            if (!args.name || String(args.name).trim() === '') return 'name';
            if (!args.date) return 'date';
            if (!args.time) return 'time';
        }
        if (name === 'trigger_mpesa_payment') {
            if (!args.amount || Number(args.amount) <= 0) return 'amount';
            // phone_number: optional — we ask if missing but don't block
        }
        return null; // Valid — proceed to API
    }

    /**
     * Self-correction: re-prompts the LLM to ask the user for a missing field.
     * This prevents 422 API errors by never sending incomplete tool calls.
     */
    private async requestMissingField(toolName: string, missingField: string): Promise<void> {
        const fieldPrompts: Record<string, string> = {
            name: 'Ask the user naturally for their name.',
            date: 'Ask the user which date they would like.',
            time: 'Ask the user what time works for them.',
            amount: 'Ask the user how much they would like to pay.',
        };
        const instruction = fieldPrompts[missingField]
            || `Ask the user for their ${missingField}.`;

        console.log(`[Tool Validation] Missing '${missingField}' for '${toolName}'. Self-correcting...`);

        // Inject a temporary system correction at the end of history
        this.history.push({
            role: 'system' as any,
            content: `[INTERNAL]: The '${toolName}' tool requires '${missingField}' but it was not provided. ${instruction} Do not mention tools or technical details.`,
        });

        this.transitionTo(ConversationState.THINKING);
        this.groq.generateResponse(this.history, true);
    }

    private async handleToolCall(toolData: { name: string; arguments: any; id: string }): Promise<void> {
        console.log(`[Tool] Validating: ${toolData.name}`, toolData.arguments);
        this.stopFillerTimer();

        // Fix 3: Capture turn at time of call. If user interrupts, turnId increments.
        const myTurnId = this.turnId;
        const isStaleTurn = () => this.turnId !== myTurnId;

        // Validate BEFORE calling any API
        const missingField = this.validateToolCall(toolData.name, toolData.arguments);
        if (missingField) {
            await this.requestMissingField(toolData.name, missingField);
            return;
        }

        if (isStaleTurn()) { console.log('[Tool] Stale turn — aborting zombie tool call.'); return; }

        console.log(`[Tool] Executing: ${toolData.name}`);
        let toolResult = '';

        try {
            switch (toolData.name) {
                case 'check_availability': {
                    const date = toolData.arguments?.date || new Date().toISOString().split('T')[0];
                    const res = await axios.get(`${LARAVEL_BASE}/api/v1/appointments/availability?date=${date}`);
                    if (isStaleTurn()) return;
                    toolResult = `Available slots for ${date}: ${res.data.slots.join(', ')}`;
                    break;
                }
                case 'book_appointment': {
                    const { name, date, time, reason } = toolData.arguments;
                    const res = await axios.post(`${LARAVEL_BASE}/api/v1/appointments/book`, { name, date, time, reason });
                    if (isStaleTurn()) return;
                    toolResult = `Booking confirmed! Booking ID: ${res.data.booking_id}`;
                    break;
                }
                case 'trigger_mpesa_payment': {
                    const { amount, phone_number } = toolData.arguments;
                    await axios.post(`${LARAVEL_BASE}/api/v1/payments/stk`, {
                        amount: Number(amount),
                        phone_number: phone_number || '254700000000',
                        account_reference: this.callId,
                    });
                    if (isStaleTurn()) return;
                    toolResult = `STK Push sent for KES ${amount}.`;
                    this.pushToolCallToHistory(toolData, toolResult);
                    this.enterPaymentWait();
                    return;
                }
                case 'transfer_to_human': {
                    const { reason } = toolData.arguments;
                    this.emit('transfer_request', reason);
                    toolResult = 'Transfer initiated. Tell the user you are connecting them to a live agent right now.';
                    break;
                }
                default: {
                    toolResult = `Unknown tool: ${toolData.name}. Apologize naturally.`;
                }
            }
        } catch (err: any) {
            if (isStaleTurn()) return;
            console.error(`[Tool Error] ${toolData.name}:`, err.message);
            toolResult = `Tool ${toolData.name} encountered an issue. Apologize naturally and offer an alternative approach.`;
        }

        if (isStaleTurn()) return;
        this.pushToolCallToHistory(toolData, toolResult);
        this.groq.generateResponse(this.history, true);
    }

    private pushToolCallToHistory(toolData: { name: string; arguments: any; id: string }, result: string): void {
        const id = toolData.id || ('call_' + Date.now());

        this.history.push({
            role: 'assistant',
            content: '',
            tool_calls: [{
                id,
                type: 'function',
                function: {
                    name: toolData.name,
                    arguments: JSON.stringify(toolData.arguments),
                },
            }],
        } as any);

        this.history.push({
            role: 'tool',
            tool_call_id: id,
            name: toolData.name,
            content: result,
        } as any);
    }

    // =========================================================================
    // PHASE 7A: MEMORY COMPRESSION
    // =========================================================================

    private countUserTurns(): number {
        return this.history.filter(h => h.role === 'user').length;
    }

    /**
     * After every 6 user turns, compress older context into a 3-bullet summary.
     * Keeps the last 4 messages intact. Swaps the rest with the summary.
     * This prevents context overflow and keeps LLM responses fast.
     */
    private async compressHistoryIfNeeded(): Promise<void> {
        const userTurns = this.countUserTurns();
        if (userTurns < 6 || userTurns % 6 !== 0) return;
        if (this.history.length < 10) return;

        // isCompressing lock is set by caller — no double-guard needed here
        const keepLast = 4;
        const toCompress = this.history.slice(0, this.history.length - keepLast);
        const recentHistory = this.history.slice(this.history.length - keepLast);

        try {
            const summary = await this.groq.compressHistory(toCompress);
            // Only update history if nothing else modified it while we were awaiting
            if (!this.isCompressing) return; // Edge case: lock was released externally
            this.history = [
                { role: 'system' as any, content: `[CONVERSATION SUMMARY — what has been discussed so far]: ${summary}` },
                ...recentHistory,
            ];
            console.log(`[Memory] History compressed at turn ${userTurns}. Reduced to ${this.history.length} messages.`);
        } catch {
            // Silent fail — keep full history
        }
    }

    // =========================================================================
    // PHASE 7B: PROSODY HINTS (Natural Speech Rhythm)
    // =========================================================================

    /**
     * Fix 1: SSML tags removed — ElevenLabs WebSocket streaming does NOT parse SSML.
     * <break time="150ms"/> would be spoken verbatim by the TTS.
     * Replaced with comma-based pauses and strategic whitespace — natural and safe.
     */
    private injectProsodyHints(text: string): string {
        return text
            // Natural comma pause after confirmation words
            .replace(/\b(confirmed|booked|scheduled|reserved|approved|ready)\b([^,])/gi, '$1, $2')
            // Comma pause before day names in appointment context ("for Tuesday" → "for, Tuesday")
            .replace(/\bfor\s+(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)\b/gi, 'for $1')
            // Natural pause rhythm before time ("at 10 AM" reads well as-is, no change needed)
            // KES amount — add comma before for natural stress
            .replace(/(KES)\s*(\d+)/gi, '$1 $2')
            // Clean up any double spaces
            .replace(/  +/g, ' ')
            .trim();
    }

    // =========================================================================
    // FILLER TIMER
    // =========================================================================

    /**
     * Filler Timer — fires at 400ms in THINKING state.
     * Plan spec (Part 8.1): Uses PRE-RECORDED audio, NOT TTS.
     * If FillerAudioService is warmed, plays instantly from cache.
     * If not yet warmed (first-second race), falls back to a silent skip.
     * This ensures zero TTS latency for backchanneling.
     */
    private startFillerTimer(): void {
        this.stopFillerTimer();
        this.fillerTimer = setTimeout(() => {
            if (this.state !== ConversationState.THINKING) return;

            if (this.filler.isWarmed()) {
                // INSTANT: emit pre-recorded buffer directly — no TTS call
                this.filler.playRandom();
                console.log('[Filler] Pre-recorded filler played.');
            } else {
                // Fallback: TTS (only if warm-up hasn't completed yet)
                // This path should rarely hit after the first call
                const texts = ["One moment.", "Let me see..."];
                const text = texts[Math.floor(Math.random() * texts.length)];
                this.elevenLabs.ensureOpen(this.config.media.output_format).then(() => {
                    if (this.state === ConversationState.THINKING) {
                        this.elevenLabs.send(text);
                        this.elevenLabs.flush();
                        console.log('[Filler] TTS fallback filler played.');
                    }
                }).catch(() => { });
            }
        }, 400); // 400ms threshold per plan (Part 8.1)
    }

    private stopFillerTimer(): void {
        if (this.fillerTimer) {
            clearTimeout(this.fillerTimer);
            this.fillerTimer = null;
        }
    }

    // =========================================================================
    // VAD FALSE POSITIVE WATCHDOG
    // =========================================================================

    private startVadWatchdog(): void {
        this.clearVadWatchdog();
        // If 3.5 seconds pass in LISTENING without a single transcript word arriving,
        // it was likely mic static or environmental echo. Reset to IDLE.
        this.vadTimer = setTimeout(() => {
            if (this.state === ConversationState.LISTENING && this.userTurnBuffer.trim() === '') {
                console.log('[VAD Watchdog] False positive noise detected (no words). Resetting to IDLE.');
                this.transitionTo(ConversationState.IDLE);
            }
        }, 3500); 
    }

    private clearVadWatchdog(): void {
        if (this.vadTimer) {
            clearTimeout(this.vadTimer);
            this.vadTimer = null;
        }
    }

    // =========================================================================
    // CONNECTION HEALTH MONITOR
    // =========================================================================

    private startHealthMonitor(): void {
        this.healthCheckTimer = setInterval(() => {
            if (!this.elevenLabs.isOpen() &&
                this.state !== ConversationState.IDLE &&
                this.state !== ConversationState.INTERRUPTED) {
                console.warn('[Health] ElevenLabs offline — silently reconnecting...');
                this.elevenLabs.connect(this.config.media.output_format).catch(() => { });
            }
        }, 5000);
    }

    // =========================================================================
    // UTILITIES
    // =========================================================================

    private stopAllTimers(): void {
        this.stopFillerTimer();
        this.clearSpeakingWatchdog(); // Fix 4
        this.cancelPaymentWait();
        if (this.healthCheckTimer) {
            clearInterval(this.healthCheckTimer);
            this.healthCheckTimer = null;
        }
    }

    // Fix 4: Watchdog helper — one method to cancel it from any call site
    private clearSpeakingWatchdog(): void {
        if (this.speakingWatchdog) {
            clearTimeout(this.speakingWatchdog);
            this.speakingWatchdog = null;
        }
    }

    private async saveCallLog(): Promise<void> {
        try {
            await axios.post(`${LARAVEL_BASE}/api/v1/calls/log`, {
                call_id: this.callId,
                agent_id: this.agentId,
                status: 'in_progress',
                duration: Math.round((Date.now() - this.startTime) / 1000),
                transcript: JSON.stringify(this.history),
                outcome: 'Ongoing',
            });
        } catch { /* Silent fail — never block the conversation */ }
    }

    private async logCall(status: string): Promise<void> {
        try {
            // Generate the summary right before logging
            const summary = await this.groq.summarizeCall(this.history);

            await axios.post(`${LARAVEL_BASE}/api/v1/calls/log`, {
                call_id: this.callId,
                agent_id: this.agentId,
                status,
                duration: Math.round((Date.now() - this.startTime) / 1000),
                transcript: JSON.stringify(this.history),
                summary: summary,
                outcome: 'AI Handled',
                tokens_used: this.groq.getUsage(),
                characters_used: this.elevenLabs.getUsage(),
                stt_seconds: this.deepgram.getUsage(),
                started_at: new Date(this.startTime).toISOString(),
                ended_at: new Date().toISOString(),
            });
        } catch (err: any) {
            console.error('[Call Log] Failed:', err.message);
        }
    }
}
