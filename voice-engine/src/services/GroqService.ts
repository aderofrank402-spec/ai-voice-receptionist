import Groq from 'groq-sdk';
import { EventEmitter } from 'events';
import axios from 'axios';
import { setDefaultResultOrder } from 'dns';

// Fix for Node.js ETIMEDOUT/ENETUNREACH on some networks (favors IPv4)
setDefaultResultOrder('ipv4first');

export class GroqService extends EventEmitter {
    private groq: Groq;
    private systemPrompt: string;
    private model: string = 'llama-3.3-70b-versatile';
    private toolsEnabled: boolean = true;
    private abortController: AbortController | null = null;
    private totalTokensUsed: number = 0;

    constructor() {
        super();
        const apiKey = process.env.GROQ_API_KEY;
        this.groq = new Groq({ apiKey: apiKey || 'placeholder' });
        this.systemPrompt = `You are a professional, warm, and highly capable AI receptionist. 
You speak naturally like a human — not like a bot.

CRITICAL RULES (never break these):
- NEVER read out JSON, curly braces {}, XML tags, function names, or any technical syntax.
- NEVER announce what you are doing internally (e.g. do not say "I am calling the booking function").
- NEVER verbalize internal metadata like # or technical IDs.
- If a tool is running, use a brief natural filler like "Let me check that for you...".
- Speak in clear, concise sentences. Avoid long rambling responses.
- If the user says they have completed onboarding or registration, BELIEVE THEM.
- When confirming important information like dates or times, pause naturally.`;
    }

    private isAbortError(error: any): boolean {
        return error.name === 'AbortError' || 
               error.name === 'APIUserAbortError' || 
               error.message?.includes('aborted');
    }

    private async reportTrace(action: string, latencyMs: number, statusCode: number = 200, isSuccess: boolean = true) {
        try {
            const url = `${process.env.LARAVEL_BASE_URL}/api/v1/system/trace`;
            if (!url) return;
            
            await axios.post(url, {
                provider: 'groq',
                action,
                latency_ms: latencyMs,
                status_code: statusCode,
                is_success: isSuccess,
                metadata: { model: this.model }
            }, {
                headers: { 'X-Crawler-Secret': process.env.CRAWLER_SERVICE_SECRET },
                timeout: 1000
            });
        } catch (err) {
            // Silently fail to ensure processing flow is never blocked
        }
    }

    /** Re-initialize with a new API key from the Vault */
    public reinit(apiKey: string) {
        this.groq = new Groq({ apiKey });
        console.log('Groq SDK re-initialized with Vault key');
    }

    /** When false, LLM returns plain text only (no tool calls). Use for tests to get TTS audio. */
    public setToolsEnabled(enabled: boolean) {
        this.toolsEnabled = enabled;
    }

    public setSystemPrompt(prompt: string) {
        this.systemPrompt = prompt;
        console.log('System Prompt Updated from Server');
    }

    public async generateResponse(history: any[], skipAbort: boolean = false) {
        const startTime = Date.now();
        try {
            const lastUserMessage = history[history.length - 1].content;
            console.log('Groq Thinking...', lastUserMessage);

            const messages = [{ role: 'system', content: this.systemPrompt }, ...history];
            
            // Increment Input Tokens (Approximation: 4 chars = 1 token)
            const inputChars = JSON.stringify(messages).length;
            this.totalTokensUsed += Math.ceil(inputChars / 4);

            const baseOptions: any = {
                messages: messages as any,
                model: this.model,
                stream: true,
                temperature: 0.0,
                max_tokens: 250,  // Increased to prevent mid-sentence cutoffs
            };

            // Abort any existing stream before starting a new one (UNLESS it's a tool-triggered follow up)
            if (!skipAbort) {
                this.abort();
            }
            this.abortController = new AbortController();

            if (this.toolsEnabled) {
                baseOptions.tools = [
                    {
                        type: 'function',
                        function: {
                            name: 'check_availability',
                            description: 'Check available appointment slots for a specific date.',
                            parameters: {
                                type: 'object',
                                properties: {
                                    date: { type: 'string', description: 'The date to check availability for, in YYYY-MM-DD format.' }
                                },
                                required: ['date']
                            }
                        }
                    },
                    {
                        type: 'function',
                        function: {
                            name: 'book_appointment',
                            description: 'Book an appointment for a specific date and time.',
                            parameters: {
                                type: 'object',
                                properties: {
                                    name: { type: 'string', description: 'The customer name.' },
                                    date: { type: 'string', description: 'The date of the appointment in YYYY-MM-DD format.' },
                                    time: { type: 'string', description: 'The time of the appointment, e.g., 09:00 AM.' },
                                    reason: { type: 'string', description: 'Optional reason for the appointment.' }
                                },
                                required: ['name', 'date', 'time']
                            }
                        }
                    },
                    {
                        type: 'function',
                        function: {
                            name: 'trigger_mpesa_payment',
                            description: "Trigger an M-Pesa Payment STK Push to the user's phone.",
                            parameters: {
                                type: 'object',
                                properties: {
                                    amount: { type: 'number', description: 'The amount to charge in KES.' },
                                    phone_number: { type: 'string', description: "The user's phone number." }
                                },
                                required: ['amount']
                            }
                        }
                    },
                    {
                        type: 'function',
                        function: {
                            name: 'transfer_to_human',
                            description: 'Escalate the call to a human doctor or manager.',
                            parameters: {
                                type: 'object',
                                properties: {
                                    reason: { type: 'string', description: 'Why the user needs a human.' }
                                },
                                required: ['reason']
                            }
                        }
                    }
                ];
                baseOptions.tool_choice = 'auto';
            }
            const stream: any = await this.groq.chat.completions.create(baseOptions, { 
                signal: this.abortController?.signal 
            });

            let fullResponse = '';
            let toolCallBuffer: any = null;

            for await (const chunk of stream) {
                const delta = chunk.choices[0]?.delta || {};
                if (delta.content) {
                    fullResponse += delta.content;
                    this.emit('token', delta.content);
                }
                if (delta.tool_calls) {
                    const tc = delta.tool_calls[0];
                    if (!toolCallBuffer) {
                        toolCallBuffer = {
                            id: tc.id,
                            name: tc.function?.name,
                            arguments: ''
                        };
                    }
                    if (tc.function?.arguments) toolCallBuffer.arguments += tc.function.arguments;
                }
            }

            const latency = Date.now() - startTime;
            this.reportTrace('completion', latency);

            if (toolCallBuffer) {
                console.log('Tool Triggered:', toolCallBuffer.name);
                
                let parsedArgs = {};
                try {
                    // Clean the string: remove anything before first { and after last }
                    const rawArgs = toolCallBuffer.arguments.trim();
                    const firstBrace = rawArgs.indexOf('{');
                    const lastBrace = rawArgs.lastIndexOf('}');
                    
                    if (firstBrace !== -1 && lastBrace !== -1) {
                        const cleanJson = rawArgs.substring(firstBrace, lastBrace + 1);
                        parsedArgs = JSON.parse(cleanJson);
                    } else {
                        parsedArgs = JSON.parse(rawArgs);
                    }
                } catch (e) {
                    console.error('[Groq] Failed to parse tool arguments, falling back to empty.', toolCallBuffer.arguments);
                    parsedArgs = {};
                }

                this.emit('tool_trigger', {
                    name: toolCallBuffer.name,
                    arguments: parsedArgs,
                    id: toolCallBuffer.id
                });
            }

            this.totalTokensUsed += Math.ceil(fullResponse.length / 4);
            this.emit('complete', fullResponse);
        } catch (error: any) {
            if (this.isAbortError(error)) {
                console.log('[Groq] Generation stopped by system');
                return;
            }
            const latency = Date.now() - startTime;
            this.reportTrace('completion', latency, error.status || 500, false);
            console.error('Groq Error:', error);
            this.emit('error', error);
        }
    }

    /**
     * Tier 3 Intent Classifier — used for barge-in detection.
     * Ultra-low-latency micro-call using the fastest available model.
     * Returns true if the speech is a conversational interrupt.
     *
     * Real-world budget from Nairobi: 120–180ms end-to-end.
     * Timeout: 200ms. Default on timeout: false (don't interrupt).
     */
    public async classifyInterrupt(text: string): Promise<boolean> {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 200);

        try {
            const response = await this.groq.chat.completions.create({
                model:      'llama-3.1-8b-instant',
                max_tokens: 2,
                temperature: 0,
                messages: [
                    {
                        role: 'system',
                        content: 'You are a classifier. Reply only YES or NO. Is the following speech a conversational interrupt (the speaker wants to stop or redirect the conversation)?'
                    },
                    { role: 'user', content: text }
                ],
            }, { signal: controller.signal });

            const answer = (response.choices[0]?.message?.content || '').trim().toUpperCase();
            return answer.startsWith('YES');
        } catch {
            return false; // Timeout or error: default to NOT interrupting (safety)
        } finally {
            clearTimeout(timeout);
        }
    }

    /**
     * Compresses a set of history messages into a 3-bullet summary.
     * Called by VoiceOrchestrator every 6 user turns to prevent context overflow.
     * Uses llama-3.1-8b-instant for speed (not the main conversational model).
     */
    public async compressHistory(history: any[]): Promise<string> {
        const formatted = history
            .filter(h => h.role === 'user' || h.role === 'assistant')
            .map(h => `${h.role === 'user' ? 'Customer' : 'Agent'}: ${h.content}`)
            .join('\n');

        const response = await this.groq.chat.completions.create({
            model:       'llama-3.1-8b-instant',
            max_tokens:  200,
            temperature: 0,
            messages: [
                {
                    role:    'system',
                    content: 'Summarize this phone conversation in 3 concise bullet points. Focus on: what the customer wants, what has been agreed, and any pending actions.',
                },
                { role: 'user', content: formatted },
            ],
        });

        return response.choices[0]?.message?.content?.trim() || 'Prior conversation context available.';
    }

    /**
     * Generates a comprehensive summary of the entire call for business owners.
     * Produces a structured JSON response containing summary, sentiment, intent, and action items.
     */
    public async summarizeCall(history: any[]): Promise<string> {
        if (!history || history.length === 0) return JSON.stringify({
            professional_summary: "No conversation took place.",
            sentiment: "Neutral",
            primary_intent: "None",
            action_items: []
        });

        const formatted = history
            .filter(h => h.role === 'user' || h.role === 'assistant' || h.role === 'system')
            .map(h => `${h.role === 'user' ? 'Customer' : (h.role === 'system' ? 'System' : 'Agent')}: ${h.content}`)
            .join('\n');

        if (formatted.trim() === '') return JSON.stringify({
            professional_summary: "No conversation took place.",
            sentiment: "Neutral",
            primary_intent: "None",
            action_items: []
        });

        try {
            const response = await this.groq.chat.completions.create({
                model: 'llama-3.3-70b-versatile',
                max_tokens: 500,
                temperature: 0.1, // Lower temperature for more consistent JSON
                response_format: { type: 'json_object' },
                messages: [
                    {
                        role: 'system',
                        content: `Analyze the following customer interaction and return a JSON object with these EXACT keys:
- "professional_summary": A 2-3 sentence overview of the conversation.
- "sentiment": One word describing the customer's tone (Satisfied, Frustrated, Neutral, Inquisitive).
- "primary_intent": One word category (Booking, Inquiry, Support, Payment, Other).
- "action_items": An array of strings representing follow-up tasks for the business owner.

CRITICAL: Do not mention AI or bots. Speak as a professional analyst.`
                    },
                    { role: 'user', content: formatted },
                ],
            });

            return response.choices[0]?.message?.content?.trim() || '{}';
        } catch (e) {
            console.error('[Groq] Failed to generate structured summary:', e);
            return JSON.stringify({ professional_summary: 'Error generating summary.', sentiment: 'Neutral', primary_intent: 'Error', action_items: [] });
        }
    }

    public async checkBookingAvailability(date: string) {
        return true;
    }

    public abort() {
        if (this.abortController) {
            this.abortController.abort();
            this.abortController = null;
            console.log('Groq Generation Aborted');
        }
    }

    public getUsage(): number {
        return this.totalTokensUsed;
    }
}
