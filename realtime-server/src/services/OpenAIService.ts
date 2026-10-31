import WebSocket from 'ws';
import { FastifyBaseLogger } from 'fastify';

interface OpenAIOptions {
    apiKey: string;
    logger: FastifyBaseLogger;
    config?: {
        prompt?: string;
        voice?: string;
        tools?: any[];
    };
}

export class OpenAIService {
    private ws: WebSocket | null = null;
    private apiKey: string;
    private logger: FastifyBaseLogger;
    private config: any;
    private url = 'wss://api.openai.com/v1/realtime?model=gpt-4o-realtime-preview-2024-10-01';

    constructor(options: OpenAIOptions) {
        this.apiKey = options.apiKey;
        this.logger = options.logger;
        this.config = options.config || {};
    }

    public connect(): Promise<void> {
        return new Promise((resolve, reject) => {
            this.ws = new WebSocket(this.url, {
                headers: {
                    'Authorization': `Bearer ${this.apiKey}`,
                    'OpenAI-Beta': 'realtime=v1'
                }
            });

            this.ws.on('open', () => {
                this.logger.info('Connected to OpenAI Realtime API');
                this.initializeSession();
                resolve();
            });

            this.ws.on('message', (data: WebSocket.Data) => {
                const event = JSON.parse(data.toString());
                this.handleEvent(event);
            });

            this.ws.on('error', (err) => {
                this.logger.error(`OpenAI WebSocket Error: ${err}`);
                reject(err);
            });

            this.ws.on('close', () => {
                this.logger.info('Disconnected from OpenAI Realtime API');
            });
        });
    }

    private initializeSession() {
        if (!this.ws) return;

        const sessionUpdate = {
            type: 'session.update',
            session: {
                modalities: ['text', 'audio'],
                voice: this.config.voice || 'shimmer',
                instructions: this.config.prompt || 'You are a helpful AI assistant.',
                tools: this.config.tools || [],
                tool_choice: 'auto'
            }
        };
        this.ws.send(JSON.stringify(sessionUpdate));

        // Inject User Message to Test Tool
        this.ws.send(JSON.stringify({
            type: 'conversation.item.create',
            item: {
                type: 'message',
                role: 'user',
                content: [{ type: 'input_text', text: 'Do you have any appointments available for today?' }]
            }
        }));
        this.ws.send(JSON.stringify({ type: 'response.create' }));
    }

    private handleEvent(event: any) {
        // Log interesting events
        if (event.type === 'response.audio.delta') {
            // Audio streaming...
        } else if (event.type === 'response.function_call_arguments.done') {
            const call_id = event.call_id;
            const name = event.name;
            const args = JSON.parse(event.arguments);

            this.logger.info(`Tool Call: ${name} with args ${JSON.stringify(args)}`);
            this.executeTool(call_id, name, args);
        } else {
            // this.logger.debug(`OpenAI Event: ${event.type}`);
        }
    }

    private async executeTool(call_id: string, name: string, args: any) {
        if (name === 'checkAvailability') {
            try {
                this.logger.info('Fetching availability from Laravel API...');
                const response = await fetch(`http://127.0.0.1:8000/api/v1/appointments/availability?date=${args.date || ''}`);
                const data = await response.json();

                this.logger.info(`Tool Output: ${JSON.stringify(data)}`);

                this.ws?.send(JSON.stringify({
                    type: 'conversation.item.create',
                    item: {
                        type: 'function_call_output',
                        call_id: call_id,
                        output: JSON.stringify(data)
                    }
                }));

                this.ws?.send(JSON.stringify({
                    type: 'response.create',
                    response: {
                        instructions: 'Tell the user the available slots based on the tool output.'
                    }
                }));
            } catch (err) {
                this.logger.error(`Tool Execution Failed: ${err}`);
            }
        }
    }

    public sendAudio(base64Audio: string) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify({
                type: 'input_audio_buffer.append',
                audio: base64Audio
            }));
        }
    }

    public close() {
        if (this.ws) {
            this.ws.close();
            this.ws = null;
        }
    }
}
