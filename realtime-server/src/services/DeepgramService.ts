import { createClient, LiveTranscriptionEvents } from '@deepgram/sdk';
import { EventEmitter } from 'events';
import ws from 'ws';

export class DeepgramService extends EventEmitter {
    private deepgram: any;
    private deepgramLive: any;

    constructor() {
        super();
        this.deepgram = createClient(process.env.DEEPGRAM_API_KEY || '');
    }

    /**
     * Connects to Deepgram Live Transcription API
     * specifically configured for Zadarma SIP (mulaw/8000).
     */
    public connect() {
        this.deepgramLive = this.deepgram.listen.live({
            model: 'nova-2',
            encoding: 'mulaw',      // Critical for Zadarma
            sample_rate: 8000,      // Critical for Zadarma
            channels: 1,
            language: 'en-US',      // Or Swahili if/when supported better
            smart_format: true,
            interim_results: true,  // We receive them but filter them manually
            endpointing: 300,       // 300ms silence = speech over
        });

        this.deepgramLive.on(LiveTranscriptionEvents.Open, () => {
            console.log('👂 Deepgram Configured (Nova-2 | mulaw | 8000Hz)');
            this.emit('connection_open');
        });

        this.deepgramLive.on(LiveTranscriptionEvents.Transcript, (data: any) => {
            this.handleTranscript(data);
        });

        this.deepgramLive.on(LiveTranscriptionEvents.Error, (err: any) => {
            console.error('Deepgram Error:', err);
        });

        this.deepgramLive.on(LiveTranscriptionEvents.Close, () => {
            console.log('Deepgram Connection Closed');
        });
    }

    /**
     * Processes the raw transcript data.
     * "Technical Grit": Ignores interim results to prevent premature interruption.
     */
    private handleTranscript(data: any) {
        const transcript = data.channel.alternatives[0].transcript;

        // Check for "Speech Started" signal to trigger Barge-In (Buffer Flush)
        if (data.speech_started) {
            this.emit('speech_started');
        }

        // Only emit text if it is FINAL or Speech Final (Silence detected)
        if (transcript && data.is_final) {
            console.log(`🎤 User: ${transcript}`);
            this.emit('transcript', transcript);
        }
    }

    /**
     * Feeds raw audio chunks (mulaw) into Deepgram
     */
    public send(audioBuffer: Buffer) {
        if (this.deepgramLive && this.deepgramLive.getReadyState() === ws.OPEN) {
            this.deepgramLive.send(audioBuffer);
        }
    }

    public disconnect() {
        if (this.deepgramLive) {
            this.deepgramLive.finish();
            this.deepgramLive = null;
        }
    }
}
