import Groq from 'groq-sdk';
import { EventEmitter } from 'events';

export class GroqService extends EventEmitter {
    private groq: Groq;
    private systemPrompt: string;
    private model: string = 'llama-3.3-70b-versatile'; // The "Smart & Cheap" Model

    constructor() {
        super();
        this.groq = new Groq({ apiKey: process.env.GROQ_API_KEY });
        // Default Mock Prompt (Fallback)
        this.systemPrompt = `
          You are a polite and professional receptionist.
          Your name is "Neema".
          
          Capabilities:
          - Book appointments.
          - Check pricing.
        `;
    }

    public setSystemPrompt(prompt: string) {
        this.systemPrompt = prompt;
        console.log("📝 System Prompt Updated from Server");
    }

    /**
   * Sends history to Llama 3 and streams the response back token-by-token.
   */
    public async generateResponse(history: any[]) {
        try {
            const lastUserMessage = history[history.length - 1].content;
            console.log(`🧠 Groq Thinking... [${lastUserMessage}]`);

            const messages = [
                { role: 'system', content: this.systemPrompt },
                ...history
            ];

            const stream = await this.groq.chat.completions.create({
                messages: messages as any,
                model: this.model,
                stream: true,
                temperature: 0.7,
                max_tokens: 150,
                tools: [
                    {
                        type: "function",
                        function: {
                            name: "trigger_mpesa_payment",
                            description: "Trigger an M-Pesa Payment STK Push to the user's phone.",
                            parameters: {
                                type: "object",
                                properties: {
                                    amount: { type: "number", description: "The amount to charge in KES." },
                                    phone_number: { type: "string", description: "The user's phone number." }
                                },
                                required: ["amount"]
                            }
                        }
                    },
                    {
                        type: "function",
                        function: {
                            name: "transfer_to_human",
                            description: "Escalate the call to a human doctor or manager.",
                            parameters: {
                                type: "object",
                                properties: {
                                    reason: { type: "string", description: "Why the user needs a human." }
                                },
                                required: ["reason"]
                            }
                        }
                    }
                ],
                tool_choice: "auto"
            });

            let fullResponse = "";
            let toolCallBuffer: any = null;

            for await (const chunk of stream) {
                const delta = chunk.choices[0]?.delta || {};

                // Handle Content
                if (delta.content) {
                    fullResponse += delta.content;
                    this.emit('token', delta.content);
                }

                // Handle Tool Calls (Groq streams them too)
                if (delta.tool_calls) {
                    const toolCall = delta.tool_calls[0];
                    if (!toolCallBuffer) {
                        toolCallBuffer = { name: toolCall.function?.name, arguments: "" };
                    }
                    if (toolCall.function?.arguments) {
                        toolCallBuffer.arguments += toolCall.function.arguments;
                    }
                }
            }

            // If we had a tool call, emit it
            if (toolCallBuffer) {
                console.log(`🛠️ Tool Triggered: ${toolCallBuffer.name}`);
                this.emit('tool_trigger', {
                    name: toolCallBuffer.name,
                    arguments: JSON.parse(toolCallBuffer.arguments)
                });

                // Important: Add tool call to history so Llama knows it happened
                // (In a real loop, we'd feed the result back, but for now we just log it)
            } else {
                // Output the full thought for History tracking
                this.emit('complete', fullResponse);
            }

        } catch (error) {
            console.error('Groq Error:', error);
            this.emit('error', error);
        }
    }

    /**
     * Tool Usage Example (Future Phase)
     */
    public async checkBookingAvailability(date: string) {
        // Mock Logic
        return true;
    }
}
