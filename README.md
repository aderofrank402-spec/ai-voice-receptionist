# AI Voice Receptionist

A real-time, multilingual AI receptionist for answering business calls, grounding responses in company knowledge, completing approved actions, and handing conversations to people when needed.

Laravel · TypeScript · SIP/RTP · WebSockets · Deepgram · Groq · ElevenLabs · OpenAI

## Highlights

- Streams caller audio through speech recognition, language-aware orchestration, and low-latency speech synthesis.
- Supports English, Swahili, and conversational Kenyan language patterns.
- Builds a business-specific knowledge base from guided onboarding and website crawling.
- Handles appointments, messages, transfers, WhatsApp follow-ups, and M-Pesa payment flows through validated tools.
- Tracks call lifecycle events, summaries, recordings, usage, and operational health.
- Protects tool execution with schema validation, stale-turn cancellation, throttling, signed callbacks, and idempotency.

## Architecture

```text
Phone/SIP
   │
Telephony gateway ── RTP/WebSocket ── Voice engine
                                         │
                        STT ── Orchestrator ── LLM ── TTS
                                         │
                                  validated tools
                                         │
                         Laravel API + queue + database
                                         │
                     dashboard, knowledge, billing, analytics
```

The repository separates the Laravel control plane, telephony service, real-time server, voice engine, and website crawler so each latency and reliability boundary can be tested independently.

## Local development

1. Copy each provided `.env.example` to its matching `.env` location.
2. Supply your own development API keys and SIP test account.
3. Install PHP and JavaScript dependencies.
4. Start the Laravel app, queue worker, Reverb, and the required Node services.

```bash
composer install
npm install
php artisan key:generate
php artisan migrate
npm run build
```

See [`docs/CRAWL_SERVICE_SETUP.md`](docs/CRAWL_SERVICE_SETUP.md) for the crawler boundary. Do not test with real customer calls or payment data.

## Publication safety

Credentials, call recordings, transcripts, customer data, local databases, generated dependencies, logs, and runtime storage are excluded. All example configuration values are placeholders.

## Status

Portfolio release of an actively developed system. Provider accounts and a public demo are not bundled.

## License

MIT. See [`LICENSE`](LICENSE).
