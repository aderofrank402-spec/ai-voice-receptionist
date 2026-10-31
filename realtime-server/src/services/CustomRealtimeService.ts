import { WebSocket } from 'ws';
import { FastifyBaseLogger } from 'fastify';
import { VoiceOrchestrator } from '../../../voice-engine/src/services/VoiceOrchestrator';

export class CustomRealtimeService {
    private orchestrator: VoiceOrchestrator;
    private ws: WebSocket;
    private logger: FastifyBaseLogger;
    private agentId: string | number;

    constructor(ws: WebSocket, logger: FastifyBaseLogger, agentId: string | number) {
        this.ws = ws;
        this.logger = logger;
        this.agentId = agentId;
        this.orchestrator = new VoiceOrchestrator();
        this.setupPipeline();
    }

    private setupPipeline() {
        // AI speaks -> Send audio to browser
        this.orchestrator.on('audio_output', (audio: Buffer) => {
            if (this.ws.readyState === WebSocket.OPEN) {
                this.ws.send(audio);
            }
        });

        // AI is thinking -> Send token/transcript to browser
        this.orchestrator.on('token', (token: string) => {
            this.sendJson({ type: 'token', text: token });
        });

        this.orchestrator.on('transcript', (text: string) => {
            this.sendJson({ type: 'transcript', text: text });
        });

        this.orchestrator.on('tool_trigger', (data: any) => {
            this.sendJson({ type: 'tool', name: data.name, arguments: data.arguments });
        });

        this.orchestrator.on('audio_output_kill', () => {
            this.sendJson({ type: 'audio_kill' });
        });

        // Signal browser to return to listening state when AI finishes speaking
        // Fixes: browser stuck at "AI Speaking..." after compliance notice or any TTS
        this.orchestrator.on('audio_complete', () => {
            this.sendJson({ type: 'listening' });
        });

        this.orchestrator.on('error', (err: any) => {
            this.logger.error(`[Orchestrator Error]: ${err.message}`);
            this.sendJson({ type: 'error', message: err.message });
        });
    }

    private sendJson(data: any) {
        if (this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(data));
        }
    }

    public async start() {
        this.logger.info(`Starting Custom Voice Session for Agent: ${this.agentId}`);
        
        // Use "Studio Quality" settings for the Dashboard
        await this.orchestrator.start({
            agentId: this.agentId,
            media: {
                encoding: 'linear16',
                sample_rate: 16000,
                output_format: 'pcm_16000'
            }
        });
        
        this.sendJson({ type: 'ready', message: 'Voice Engine Connected' });
    }

    public handleIncomingAudio(chunk: Buffer) {
        // Send directly to the Deepgram Ears
        // Note: The orchestrator uses 'deepgram.send()' internally for the connection
        // but it doesn't expose a root 'send' for easy piping. 
        // I will add a proxy send to VoiceOrchestrator for cleaner integration.
        (this.orchestrator as any).receiveAudio(chunk);
    }

    public stop() {
        this.orchestrator.endCall('user_disconnected');
        this.orchestrator.removeAllListeners();
        this.logger.info('Custom Voice Session Closed');
    }
}
