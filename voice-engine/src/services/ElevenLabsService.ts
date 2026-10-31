import WebSocket from 'ws';
import { EventEmitter } from 'events';
import axios from 'axios';
import { setDefaultResultOrder } from 'dns';

setDefaultResultOrder('ipv4first');

/**
 * ElevenLabsService — Production Engine v1
 *
 * Key changes from previous version:
 * 1. Keep-alive uses ws.ping() (WebSocket control frame) — NOT text messages.
 *    Sending " " text would trigger audio synthesis. ws.ping() does not.
 * 2. ensureOpen(): Pre-warms the connection. Called from THINKING state.
 * 3. closeStream(): Graceful EOS flush + close. Used by INTERRUPTED state.
 *    Does NOT fully disconnect — connection is re-established on next turn.
 * 4. audio_complete: Emitted when ElevenLabs signals isFinal — tells
 *    the orchestrator that all audio has been delivered.
 */
export class ElevenLabsService extends EventEmitter {
    private ws: WebSocket | null = null;
    private voiceId: string;
    private apiKey: string;
    private modelId: string = 'eleven_flash_v2_5';
    private lastOutputFormat: string = 'pcm_16000';
    private pingTimer: NodeJS.Timeout | null = null;
    private connectionPromise: Promise<void> | null = null;
    private totalCharactersUsed: number = 0;

    constructor() {
        super();
        this.apiKey  = process.env.ELEVENLABS_API_KEY  || '';
        this.voiceId = process.env.ELEVENLABS_VOICE_ID || '';
    }

    private async reportTrace(action: string, latencyMs: number, statusCode = 200, isSuccess = true) {
        try {
            await axios.post(`${process.env.LARAVEL_BASE_URL}/api/v1/system/trace`, {
                provider:    'elevenlabs',
                action,
                latency_ms:  latencyMs,
                status_code: statusCode,
                is_success:  isSuccess,
                metadata:    { model: this.modelId, voice_id: this.voiceId },
            }, {
                headers: { 'X-Crawler-Secret': process.env.CRAWLER_SERVICE_SECRET },
                timeout: 1000,
            });
        } catch {
            // Silent — tracing must never block the voice pipeline
        }
    }

    public reinit(apiKey?: string, voiceId?: string): void {
        if (apiKey)  this.apiKey  = apiKey;
        if (voiceId) this.voiceId = voiceId;
        console.log('[ElevenLabs] Re-initialized with new credentials');
    }

    /**
     * Open a new WebSocket streaming session with ElevenLabs.
     * Called once at call-start and re-used throughout the call.
     */
    public connect(outputFormat: string = 'pcm_16000'): Promise<void> {
        if (this.connectionPromise) return this.connectionPromise;

        this.lastOutputFormat = outputFormat;

        this.connectionPromise = new Promise((resolve, reject) => {
            const startTime = Date.now();
            const url = `wss://api.elevenlabs.io/v1/text-to-speech/${this.voiceId}/stream-input`
                + `?model_id=${this.modelId}`
                + `&output_format=${outputFormat}`
                + `&optimize_streaming_latency=3`
                + `&inactivity_timeout=300`;

            const ipv4Lookup = (hostname: string, opts: any, cb: any) => {
                require('dns').lookup(hostname, { ...opts, family: 4 }, cb);
            };

            if (this.ws) {
                this.ws.removeAllListeners();
                this.ws.terminate();
                this.ws = null;
            }

            this.ws = new WebSocket(url, { lookup: ipv4Lookup });

            const connectionTimeout = setTimeout(() => {
                this.connectionPromise = null;
                reject(new Error('[ElevenLabs] Connection timeout (10s)'));
            }, 10000);

            this.ws.on('open', async () => {
                clearTimeout(connectionTimeout);
                const latency = Date.now() - startTime;
                await this.reportTrace('connection', latency);

                console.log('🗣️ ElevenLabs Connected (Flash v2.5)');

                // Send BOS (Beginning of Stream) with voice settings
                this.ws?.send(JSON.stringify({
                    text: ' ',
                    voice_settings: {
                        stability:        0.5,
                        similarity_boost: 0.80,
                        speed:            1.0,
                    },
                    generation_config: {
                        chunk_length_schedule: [80, 120, 160],
                    },
                    xi_api_key: this.apiKey,
                }));

                this.startPing();
                this.emit('connection_open');
                resolve();
            });

            this.ws.on('message', (data: any) => {
                try {
                    const response = JSON.parse(data.toString());
                    if (response.audio) {
                        const audioChunk = Buffer.from(response.audio, 'base64');
                        this.emit('audio', audioChunk);
                    }
                    if (response.isFinal === true) {
                        // All audio chunks for this generation have been delivered
                        this.emit('audio_complete');
                    }
                    if (response.error) {
                        console.error('[ElevenLabs] Server error:', response.error);
                    }
                } catch {
                    // Malformed message — ignore
                }
            });

            this.ws.on('pong', () => {
                // Pong received — connection is confirmed alive
            });

            this.ws.on('close', (code: number) => {
                this.connectionPromise = null;
                this.stopPing();
                // Only log non-standard closures
                if (code !== 1000 && code !== 1001) {
                    console.warn(`[ElevenLabs] Connection closed unexpectedly (code: ${code})`);
                }
            });

            this.ws.on('error', (err: Error) => {
                this.connectionPromise = null;
                this.stopPing();
                console.error('[ElevenLabs] WebSocket error:', err.message);
                reject(err);
            });
        });

        return this.connectionPromise;
    }

    /**
     * Ensure the connection is open. If not, open it.
     * Called from THINKING state to pre-warm before first token arrives.
     */
    public async ensureOpen(outputFormat?: string): Promise<void> {
        if (this.isOpen()) return;
        await this.connect(outputFormat || this.lastOutputFormat);
    }

    public isOpen(): boolean {
        return this.ws !== null && this.ws.readyState === WebSocket.OPEN;
    }

    /**
     * Send a text chunk to ElevenLabs for synthesis.
     * Called once per sentence boundary.
     */
    public send(text: string): void {
        if (!this.isOpen()) {
            console.warn('[ElevenLabs] Cannot send — socket not open');
            return;
        }
        this.totalCharactersUsed += text.length;
        this.ws?.send(JSON.stringify({
            text: text + ' ',
            try_trigger_generation: true,
        }));
    }

    /**
     * Flush: Send EOS marker to signal end of the current generation.
     * This tells ElevenLabs to finalize and deliver remaining audio.
     */
    public flush(): void {
        if (this.isOpen()) {
            console.log('[ElevenLabs] Sending EOS flush...');
            this.ws?.send(JSON.stringify({
                text: '',
                try_trigger_generation: true,
            }));
        }
    }

    /**
     * Close the current stream gracefully without fully disconnecting.
     * Used by INTERRUPTED state.
     * Sends EOS + closes the socket so no more audio is generated.
     * The next call to connect() or ensureOpen() will create a fresh session.
     */
    public closeStream(): void {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            // Send EOS first to let ElevenLabs clean up server-side
            try {
                this.ws.send(JSON.stringify({ text: '' }));
            } catch { /* ignore */ }
            this.ws.close(1000, 'Stream interrupted by user');
        }
        this.connectionPromise = null;
        this.stopPing();
        this.ws = null;
    }

    /**
     * Full disconnect — used only in endCall().
     */
    public disconnect(): void {
        this.closeStream();
        console.log('[ElevenLabs] Disconnected (call ended)');
    }

    /**
     * Keep-alive: ws.ping() sends a WebSocket protocol-level control frame.
     * The ElevenLabs server responds with ws.pong().
     * NO text is sent. NO audio is generated. Connection is confirmed alive.
     *
     * This replaces the old approach of sending ' ' (space text) which
     * would cause ElevenLabs to synthesize a tiny audio artifact.
     */
    private startPing(): void {
        this.stopPing();
        this.pingTimer = setInterval(() => {
            if (this.isOpen()) {
                this.ws?.ping();
            }
        }, 10000);
    }

    private stopPing(): void {
        if (this.pingTimer) {
            clearInterval(this.pingTimer);
            this.pingTimer = null;
        }
    }

    public getUsage(): number {
        return this.totalCharactersUsed;
    }

    public stop(): void {
        this.emit('interrupt');
    }
}
