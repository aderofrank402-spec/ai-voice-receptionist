import axios from 'axios';
import { DeepgramService } from './DeepgramService';
import { GroqService } from './GroqService';
import { ElevenLabsService } from './ElevenLabsService';
import { EventEmitter } from 'events';

export class VoiceOrchestrator extends EventEmitter {
    private deepgram: DeepgramService;
    private groq: GroqService;
    private elevenLabs: ElevenLabsService;
    private buffer: string = ""; // Accumulates Groq tokens for sentence chunking
    private history: { role: 'system' | 'user' | 'assistant'; content: string }[] = [];
    private fillerTimer: NodeJS.Timeout | null = null;

    // Call Tracking
    private startTime: number = Date.now();
    private callId: string = "test-call-" + Date.now();

    constructor() {
        super(); // Call the EventEmitter constructor
        this.deepgram = new DeepgramService();
        this.groq = new GroqService();
        this.elevenLabs = new ElevenLabsService();

        this.setupPipeline();
    }

    public async start() {
        console.log('🚀 Starting Voice Engine...');

        // 0. Fetch Configuration from Body (Simulated for now, assumes Agent ID 1)
        try {
            console.log("🔄 Fetching Agent Config...");
            const response = await axios.get('http://127.0.0.1:8000/api/v1/agents/config?agent_id=1');
            const config = response.data.config;

            if (config && config.prompt) {
                this.groq.setSystemPrompt(config.prompt);
                console.log(`✅ Agent Loaded: ${config.business_name}`);
            }
        } catch (error: any) {
            console.error("⚠️ Failed to load agent config (using default):", error.message);
        }

        this.startTime = Date.now(); // Reset start time
        this.deepgram.connect();
        this.elevenLabs.connect();
        // Groq is API-based, so no persistent connection needed

        // Initialize Memory
        this.history = [];
    }

    /**
     * Ends the call and persists transcript to Laravel Backend.
     */
    public async endCall(status: string = 'completed') {
        console.log(`\n🛑 Ending Call. Status: ${status}`);

        // 1. Cleanup
        this.stopFillerTimer();
        this.deepgram.disconnect();
        // this.elevenLabs.disconnect(); // Optional, keep open for last word

        // 2. Calculate Duration
        const durationSeconds = Math.round((Date.now() - this.startTime) / 1000);

        // 2.5 Estimate Cost (Pro-Tip)
        // Rate: $0.16/min (approx)
        const cost = (durationSeconds / 60) * 0.16;

        // 3. Prepare Payload
        const payload = {
            call_id: this.callId,
            status: status,
            duration: durationSeconds,
            transcript: JSON.stringify(this.history),
            outcome: "AI Handled",
            cost: cost.toFixed(4), // Add Cost Field
            started_at: new Date(this.startTime).toISOString(),
            ended_at: new Date().toISOString()
        };

        // 4. Send to Laravel
        try {
            console.log("💾 Persisting Transcript to Laravel...");
            // Use 127.0.0.1 for local Laravel
            const response = await axios.post('http://127.0.0.1:8000/api/v1/calls/log', payload);
            console.log(`✅ Transcript Saved! Call ID: ${response.data.call_id}`);
        } catch (error: any) {
            console.error(`❌ Failed to Save Transcript: ${error.message}`);
            if (error.response) {
                console.error("   Server Response:", error.response.data);
            }
        }
    }

    private setupPipeline() {
        // 1. Ear -> Brain
        this.deepgram.on('transcript', (text) => {
            // 1. Add User to History
            this.history.push({ role: 'user', content: text });

            // 1.5 Start Thinking Filler Timer
            this.startFillerTimer();

            // 2. Generate Response (passing History)
            this.groq.generateResponse(this.history);
        });

        // 2. Brain -> Mouth (With Chunking "Grit")
        this.groq.on('token', (token) => {
            // Stop filler as soon as we have a thought
            this.stopFillerTimer();
            this.handleTokenStream(token);
        });

        // 2.5 Brain Memory Update
        this.groq.on('complete', (fullText) => {
            this.stopFillerTimer(); // Ensure it's stopped if it wasn't already
            console.log(`📝 Memory Updated: Assistant said "${fullText}"`);
            this.history.push({ role: 'assistant', content: fullText });
        });

        // 2.6 Tool Trigger (M-Pesa)
        this.groq.on('tool_trigger', async (toolData) => { // Added 'async' here
            this.stopFillerTimer(); // Tools count as a response
            if (toolData.name === 'trigger_mpesa_payment') {
                const { amount, phone_number } = toolData.arguments;

                // Call Laravel Backend
                try {
                    console.log(`\n💳 M-PESA STK PUSH TRIGGERED!`);
                    console.log(`   Amount: ${amount} KES`);
                    console.log(`   Phone:  ${phone_number || 'Use Caller ID'}`);

                    const response = await axios.post('http://127.0.0.1:8000/api/v1/payments/stk', {
                        amount: Number(amount), // Force Number
                        phone_number: phone_number || '254700000000',
                        account_reference: this.callId
                    });

                    console.log(`✅ Payment API Response: ${response.status} ${response.statusText}`);

                    this.history.push({
                        role: 'system',
                        content: `Success: STK Push sent for ${amount} KES to ${phone_number}. Waiting for user to enter PIN.`
                    });
                } catch (error: any) {
                    console.error("❌ Failed to Trigger STK Push:", error.message);
                    if (error.response) {
                        console.error("   Server Status:", error.response.status);
                        console.error("   Server Data:", JSON.stringify(error.response.data));
                    } else if (error.request) {
                        console.error("   No Response Received (Connection Retry?)");
                    }
                    this.history.push({ role: 'system', content: `Error: Failed to send payment request.` });
                }

            } else if (toolData.name === 'transfer_to_human') {
                const { reason } = toolData.arguments;
                console.log(`\n👨‍⚕️ HUMAN TRANSFER REQUESTED! Reason: ${reason}`);
                // In Phase 2: Signal Drachtio to REFER
                this.emit('transfer_request', reason);

                this.history.push({ role: 'system', content: `Success: Transferring to human...` });
            }
        });

        // 3. Mouth -> Output (For now, console/file, Phase 2 -> RTP)
        this.elevenLabs.on('audio', (audioChunk) => {
            // process.stdout.write('.'); // Visual feedback
            this.emit('audio_output', audioChunk);
        });

        // 4. "Barge-In" Kill Switch
        this.deepgram.on('speech_started', () => {
            console.log('🛑 User Interrupt Detected! Killing Audio.');
            this.stopFillerTimer(); // Don't hum if interrupted
            this.elevenLabs.stop();
            this.buffer = ""; // Clear Brain buffer
            this.emit('audio_output_kill'); // Signal RTP to clear buffer
        });
    }

    private startFillerTimer() {
        this.stopFillerTimer();
        this.fillerTimer = setTimeout(() => this.playFiller(), 400);
    }

    private stopFillerTimer() {
        if (this.fillerTimer) {
            clearTimeout(this.fillerTimer);
            this.fillerTimer = null;
        }
    }

    private playFiller() {
        console.log('🔊 [Thinking Filler] "Mm-hmm"');
        // In reality, emit a specific buffer here.
        // this.emit('audio_output', fillerBuffer);
    }

    /**
     * Feed raw audio into the engine (from RTP or File)
     */
    public processAudio(buffer: Buffer) {
        this.deepgram.send(buffer);
    }

    /**
     * "Sentence Chunking" Logic
       * Accumulates tokens until a natural pause is found.
       */
    private handleTokenStream(token: string) {
        this.buffer += token;

        // Check for punctuation that signals a "speakable chunk"
        if (/[.,!?;]/.test(token) || this.buffer.length > 50) {
            if (this.buffer.trim().length > 0) {
                console.log(`🗣️ Speaking Chunk: "${this.buffer}"`);
                this.elevenLabs.send(this.buffer);
                this.buffer = "";
            }
        }
    }
}
