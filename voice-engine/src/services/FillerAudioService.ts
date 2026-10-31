import axios from 'axios';
import { EventEmitter } from 'events';

/**
 * FillerAudioService — Pre-warmed Instant Playback Fillers
 *
 * Plan spec (Part 8.1): "pre-recorded audio filler plays (not TTS — instant playback)"
 *
 * How this works:
 * - At server startup (or first call), we call ElevenLabs REST API ONCE per phrase.
 * - Audio buffers are stored as static class-level memory.
 * - During calls, playRandom() emits the cached buffer immediately — zero generation latency.
 * - This is opaque to the caller: it sounds TTS-quality but has zero synthesis delay.
 *
 * Warm cost: ~400ms × 4 phrases = ~1.6 seconds at startup. Amortized across all calls.
 * Runtime cost: 0ms (just a Buffer emit).
 */
export class FillerAudioService extends EventEmitter {
    private static warmed: boolean = false;
    private static audioBuffers: Buffer[] = [];

    private static FILLER_TEXTS = [
        "Let me see...",
        "One moment.",
        "Hmm, let me check that for you.",
        "Give me just a second.",
    ];

    private voiceId: string;
    private apiKey: string;
    private outputFormat: string = 'pcm_16000';

    constructor() {
        super();
        this.apiKey  = process.env.ELEVENLABS_API_KEY  || '';
        this.voiceId = process.env.ELEVENLABS_VOICE_ID || '';
    }

    /**
     * Pre-generate all filler phrases at server startup.
     * Idempotent: calling multiple times is safe — only generates once.
     */
    public async warmUp(outputFormat?: string): Promise<void> {
        if (FillerAudioService.warmed) {
            console.log('[Filler] Already warmed. Skipping.');
            return;
        }

        this.outputFormat = outputFormat || this.outputFormat;
        console.log('[Filler] Pre-generating filler audio...');

        const results: Buffer[] = [];

        for (const phrase of FillerAudioService.FILLER_TEXTS) {
            try {
                const buffer = await this.generateViaRest(phrase);
                results.push(buffer);
                console.log(`[Filler] Cached: "${phrase}" (${buffer.length} bytes)`);
            } catch (err: any) {
                // If one filler fails, push an empty buffer — we'll skip it at runtime
                console.warn(`[Filler] Failed to warm "${phrase}": ${err.message}`);
                results.push(Buffer.alloc(0));
            }
        }

        FillerAudioService.audioBuffers = results;
        FillerAudioService.warmed = results.some(b => b.length > 0);
        console.log('[Filler] Warm-up complete. Instant playback ready.');
    }

    /**
     * Emit a random filler audio buffer immediately.
     * If not yet warmed, silently no-ops (caller handles timing).
     *
     * @emits 'audio' — a Buffer containing raw PCM audio
     */
    public playRandom(): void {
        const valid = FillerAudioService.audioBuffers.filter(b => b.length > 0);
        if (valid.length === 0) {
            console.warn('[Filler] Not warmed yet — skipping filler playback.');
            return;
        }
        const selected = valid[Math.floor(Math.random() * valid.length)];
        this.emit('audio', selected);
    }

    public isWarmed(): boolean {
        return FillerAudioService.warmed;
    }

    /**
     * Call ElevenLabs REST API (not WebSocket) to generate audio synchronously.
     *
     * CRITICAL FIX: output_format is a QUERY PARAMETER, not a body field.
     * The Accept header must match the format: for PCM we do not set Accept
     * (ElevenLabs returns the format specified by the query param regardless).
     * Setting Accept: audio/mpeg while requesting pcm_16000 caused a format conflict,
     * returning MP3 data which when sent to an RTP bridge expecting PCM = garbage audio.
     */
    private async generateViaRest(text: string): Promise<Buffer> {
        const format = this.outputFormat;
        const url = `https://api.elevenlabs.io/v1/text-to-speech/${this.voiceId}?output_format=${encodeURIComponent(format)}`;

        const response = await axios.post(
            url,
            {
                text,
                model_id: 'eleven_flash_v2_5',
                voice_settings: {
                    stability:        0.5,
                    similarity_boost: 0.8,
                },
            },
            {
                headers: {
                    'xi-api-key':   this.apiKey,
                    'Content-Type': 'application/json',
                    // No Accept header — let ElevenLabs return the format we specified
                    // in the query param. Specifying Accept: audio/mpeg would override it.
                },
                responseType: 'arraybuffer',
                timeout:      10000,
            }
        );

        return Buffer.from(response.data);
    }

    /**
     * Reset warm status (useful for testing or key rotation).
     */
    public static reset(): void {
        FillerAudioService.warmed = false;
        FillerAudioService.audioBuffers = [];
    }
}
