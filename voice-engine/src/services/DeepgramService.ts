import { createClient, LiveTranscriptionEvents } from '@deepgram/sdk';
import { EventEmitter } from 'events';
import { setDefaultResultOrder } from 'dns';
import ws from 'ws';
import axios from 'axios';

// Fix for Node.js ETIMEDOUT on some networks (favors IPv4)
setDefaultResultOrder('ipv4first');

function getDeepgramClientOptions(): Record<string, unknown> {
    const apiKey = process.env.DEEPGRAM_API_KEY ?? '';
    const opts: Record<string, unknown> = { apiKey: apiKey || undefined };

    const ipv4Lookup = (hostname: string, dnsOpts: any, cb: any) => {
        require('dns').lookup(hostname, { ...dnsOpts, family: 4 }, cb);
    };

    const proxyUrl = process.env.HTTPS_PROXY || process.env.HTTP_PROXY;
    const wsOptions = proxyUrl
        ? (() => {
            try {
                const { HttpsProxyAgent } = require('https-proxy-agent');
                const agent = new HttpsProxyAgent(proxyUrl);
                return class ProxyWebSocket extends ws {
                    constructor(url: string, _protocols?: unknown, options?: ws.ClientOptions) {
                        super(url, { ...options, agent, lookup: ipv4Lookup } as ws.ClientOptions);
                    }
                };
            } catch {
                return null;
            }
        })()
        : class IPv4WebSocket extends ws {
            constructor(url: string, _protocols?: unknown, options?: ws.ClientOptions) {
                super(url, { ...options, lookup: ipv4Lookup } as ws.ClientOptions);
            }
        };

    if (wsOptions) {
        opts.global = { websocket: { client: wsOptions } };
    }

    return opts;
}

export class DeepgramService extends EventEmitter {
    private deepgram: any;
    private deepgramLive: any;
    private connectionStartTime: number | null = null;
    private totalSecondsUsed: number = 0;
    private keepAliveTimer: NodeJS.Timeout | null = null;

    constructor() {
        super();
        this.deepgram = createClient(getDeepgramClientOptions());
    }

    private async reportTrace(action: string, latencyMs: number, statusCode = 200, isSuccess = true) {
        try {
            const url = `${process.env.LARAVEL_BASE_URL}/api/v1/system/trace`;
            await axios.post(url, {
                provider: 'deepgram',
                action,
                latency_ms: latencyMs,
                status_code: statusCode,
                is_success: isSuccess,
                metadata: { model: 'nova-2', project_id: process.env.DEEPGRAM_PROJECT_ID },
            }, {
                headers: { 'X-Crawler-Secret': process.env.CRAWLER_SERVICE_SECRET },
                timeout: 1000,
            });
        } catch {
            // Silent — tracing must never block the voice pipeline
        }
    }

    public reinit(apiKey: string): void {
        this.deepgram = createClient(apiKey);
        console.log('[Deepgram] Re-initialized with new key');
    }

    public connect(options?: { encoding?: string; sample_rate?: number }): void {
        const startTime = Date.now();
        const projectId = process.env.DEEPGRAM_PROJECT_ID;
        const encoding    = options?.encoding    || 'linear16';
        const sample_rate = options?.sample_rate || 16000;

        console.log(`[Deepgram] Connecting... Mode: ${encoding}/${sample_rate} Project: ${projectId || 'default'}`);

        this.deepgramLive = this.deepgram.listen.live({
            // ─── Model ────────────────────────────────────────────────────────
            model:   'nova-2',

            // ─── Kenyan English Configuration ────────────────────────────────
            // 'en' covers Kenyan English accent patterns broadly.
            // Note: detect_language is intentionally omitted — it is mutually
            // exclusive with specifying a language. For Swahili-heavy use cases,
            // switch language to 'multi' separately per agent configuration.
            language: 'en',

            // ─── Audio format ─────────────────────────────────────────────────
            encoding:    encoding,
            sample_rate: sample_rate,
            channels:    1,

            // ─── Transcription behavior ───────────────────────────────────────
            smart_format:     true,
            interim_results:  true,   // We need these for speech_started signal
            vad_events:       true,   // REQUIRED in v4 for SpeechStarted and reliable UtteranceEnd

            // ─── Endpointing ─────────────────────────────────────────────────
            // 800ms: Calibrated for Kenyan conversation pace (slightly slower than en-US callers).
            // utterance_end_ms: The definitive "user finished" signal at 1000ms.
            endpointing:      800,
            utterance_end_ms: 1000,
        });

        this.deepgramLive.on(LiveTranscriptionEvents.Open, async () => {
            const latency = Date.now() - startTime;
            this.connectionStartTime = Date.now();
            console.log(`[Deepgram] ✅ Connected! Latency: ${latency}ms`);
            await this.reportTrace('connection', latency);
            this.emit('connection_open');
            this.startKeepAlive();
        });

        this.deepgramLive.on(LiveTranscriptionEvents.Transcript, (data: any) => {
            this.handleTranscript(data);
        });

        // SpeechStarted: Deepgram's separate event for when speech audio is first detected.
        // This is a DISTINCT event type in the SDK (not a field on transcript data).
        // Confirmed from @deepgram/sdk source: ListenLiveClient.ts emits this separately
        // when `data.type === 'SpeechStarted'`.
        this.deepgramLive.on(LiveTranscriptionEvents.SpeechStarted, () => {
            console.log('[Deepgram] Speech started');
            this.emit('speech_started');
        });

        // UtteranceEnd: Deepgram's definitive end-of-turn signal
        // This is what we use to trigger the LLM — NOT is_final alone
        this.deepgramLive.on(LiveTranscriptionEvents.UtteranceEnd, (data: any) => {
            console.log('[Deepgram] Utterance End detected');
            this.emit('utterance_end', data);
        });

        this.deepgramLive.on(LiveTranscriptionEvents.Error, (err: any) => {
            console.error('[Deepgram] Error:', err?.message || String(err));
            this.emit('error', err);
        });

        this.deepgramLive.on(LiveTranscriptionEvents.Close, (code: number) => {
            console.warn(`[Deepgram] 🔴 Connection closed (code: ${code})`);
            this.stopKeepAlive();
            this.emit('close');
        });

        // Critical: handle the raw 'error' event to prevent Node.js crash
        // (Node throws if 'error' is emitted with no listener)
        if (this.deepgramLive?.conn) {
            this.deepgramLive.conn.removeAllListeners?.('error');
        }
    }

    private handleTranscript(data: any): void {
        const transcript = data?.channel?.alternatives?.[0]?.transcript;

        if (!transcript) return;

        console.log(`[Deepgram] Transcript: "${transcript}" (is_final: ${data.is_final}, speech_final: ${data.speech_final})`);

        // We only pass is_final transcripts to the orchestrator.
        // Interim results are logged but not accumulated.
        if (data.is_final) {
            this.emit('transcript', transcript);
        }
    }

    public send(audioBuffer: Buffer): void {
        const state = this.deepgramLive?.getReadyState();
        if (state === 1) { // ws.OPEN = 1
            this.deepgramLive.send(audioBuffer);
        } else {
            // Log a one-time warning so we can diagnose if audio is always being dropped
            if (!this._dropWarnedOnce) {
                this._dropWarnedOnce = true;
                console.warn(`[Deepgram] ⚠️ Not ready (readyState=${state}) — dropping audio. Will auto-recover when connected.`);
            }
        }
    }

    private _dropWarnedOnce: boolean = false;

    /**
     * Keep-alive: Use Deepgram's native keepAlive() message
     * to prevent the server from triggering utterance_end on silence.
     */
    private startKeepAlive(): void {
        this.stopKeepAlive();
        this.keepAliveTimer = setInterval(() => {
            if (this.deepgramLive?.getReadyState() === ws.OPEN) {
                this.deepgramLive.keepAlive();
            }
        }, 8000);
    }

    private stopKeepAlive(): void {
        if (this.keepAliveTimer) {
            clearInterval(this.keepAliveTimer);
            this.keepAliveTimer = null;
        }
    }

    public disconnect(): void {
        this.stopKeepAlive();
        if (this.deepgramLive) {
            if (this.connectionStartTime) {
                this.totalSecondsUsed += (Date.now() - this.connectionStartTime) / 1000;
                this.connectionStartTime = null;
            }
            this.deepgramLive.finish();
            this.deepgramLive = null;
        }
    }

    public getUsage(): number {
        return Math.round(this.totalSecondsUsed);
    }
}
