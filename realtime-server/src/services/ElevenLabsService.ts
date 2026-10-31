import WebSocket from 'ws';
import { EventEmitter } from 'events';

export class ElevenLabsService extends EventEmitter {
    private ws: WebSocket | null = null;
    private voiceId: string;
    private apiKey: string;
    private modelId: string = 'eleven_flash_v2_5'; // Fast Flash Model

    constructor() {
        super();
        this.apiKey = process.env.ELEVENLABS_API_KEY || '';
        this.voiceId = process.env.ELEVENLABS_VOICE_ID || '';
    }

    public connect() {
        const url = `wss://api.elevenlabs.io/v1/text-to-speech/${this.voiceId}/stream-input?model_id=${this.modelId}`;

        this.ws = new WebSocket(url);

        this.ws.on('open', () => {
            console.log('🗣️ ElevenLabs Connected (Flash v2.5)');
            this.emit('connection_open');

            // Send initial config with Stability 0.5 (Grit)
            const bosMessage = {
                text: " ",
                voice_settings: {
                    stability: 0.5,
                    similarity_boost: 0.5
                },
                xi_api_key: this.apiKey, // Authenticate in first frame
            };

            this.ws?.send(JSON.stringify(bosMessage));
        });

        this.ws.on('message', (data: any) => {
            const response = JSON.parse(data.toString());

            if (response.audio) {
                // Decode base64 audio chunk
                const audioChunk = Buffer.from(response.audio, 'base64');
                this.emit('audio', audioChunk);
            }

            if (response.isFinal) {
                // ElevenLabs finished speaking the current queue
            }
        });

        this.ws.on('error', (err) => {
            console.error('ElevenLabs Error:', err);
        });

        this.ws.on('close', () => {
            console.log('ElevenLabs Disconnected');
        });
    }

    /**
     * Sends text chunks to be spoken.
     * "Technical Grit": We send text as we get it (buffered by Orchestrator).
     */
    public send(text: string) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            const message = {
                text: text + " ", // Append space to help TTS context
                try_trigger_generation: true, // Force low latency generation
            };
            this.ws.send(JSON.stringify(message));
        }
    }

    /**
     * "Barge-In" Logic: Stops audio generation immediately.
     * Send a specific EOS (End of Stream) message or close/reopen.
     * For streaming, easiest is to just ignore incoming data and send empty text.
     */
    public stop() {
        // 1. Clear local buffers (if any) sets in Orchestrator
        // 2. Send "End of Stream" signal to ElevenLabs if supported, 
        //    or just stop processing incoming audio in the Orchestrator.
        //    Here we emit an event to tell Orchestrator "I am stopping".
        this.emit('interrupt');
    }
}
