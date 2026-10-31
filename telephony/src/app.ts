import fastify from 'fastify';
// @ts-ignore
import dotenv from 'dotenv-safe';
import Srf from 'drachtio-srf';
import { VoiceOrchestrator } from 'voice-engine';
import { ZadarmaService } from './services/ZadarmaService';
import { RtpBridge } from './services/RtpBridge';
import axios from 'axios';


dotenv.config();

const server = fastify({
    logger: {
        transport: {
            targets: [
                {
                    target: 'pino-pretty',
                    level: 'info',
                    options: { colorize: true }
                },
                {
                    target: 'pino/file',
                    level: 'info',
                    options: { destination: '../storage/logs/telephony.log' }
                }
            ]
        }
    }
});

const srf = new Srf();
/* Drachtio Connection */
srf.connect({
    host: process.env.DRACHTIO_HOST || '127.0.0.1',
    port: parseInt(process.env.DRACHTIO_PORT || '9022', 10),
    secret: process.env.DRACHTIO_SECRET || 'cymru',
});

/* SIP Registration to Zadarma */
srf.on('connect', (err: Error | null, hostport: string) => {
    if (err) return server.log.error(`Drachtio Connect Error: ${err}`);
    server.log.info(`Connected to Drachtio at ${hostport}`);

    // Register to Zadarma to receive incoming calls locally
    const sipServer = process.env.ZADARMA_SIP_SERVER || 'pbx.zadarma.com';
    const sipLogin = process.env.ZADARMA_SIP_LOGIN;
    const sipPassword = process.env.ZADARMA_SIP_PASSWORD;

    if (sipLogin && sipPassword) {
        const registerToZadarma = () => {
            server.log.info(`Registering SIP to ${sipServer} as ${sipLogin}...`);
            (srf as any).request(`sip:${sipServer}`, {
                method: 'REGISTER',
                headers: {
                    'From': `sip:${sipLogin}@${sipServer}`,
                    'To': `sip:${sipLogin}@${sipServer}`,
                    'Contact': `<sip:${sipLogin}@127.0.0.1:5060;transport=udp>`
                },
                auth: {
                    username: sipLogin,
                    password: sipPassword
                }
            }, (err: any, req: any) => {
                if (err) return server.log.error(`Failed to send REGISTER: ${err}`);

                req.on('response', (res: any) => {
                    if (res.status === 200) {
                        server.log.info('SIP Registration successful! Ready to receive calls.');
                    } else if (res.status !== 401) { // 401 is expected for auth challenge
                        server.log.warn(`SIP Registration got non-200 status: ${res.status}`);
                    }
                });
            });
        };

        // Register immediately on start
        registerToZadarma();

        // Keep connection alive: Re-register every 30 minutes (1,800,000 ms)
        setInterval(registerToZadarma, 1800000);
    }
});

srf.on('error', (err: Error) => {
    server.log.error(`Drachtio Error: ${err}`);
});

srf.invite(async (req: any, res: any) => {
    server.log.info(`Incoming SIP Call from ${req.source_address}:${req.source_port}`);

    try {
        // Extract the Destination Number from the SIP URI
        // Example SIP URI: sip:560657@pbx.zadarma.com
        const toHeader = req.getParsedHeader('To');
        const calledNumber = toHeader.uri.match(/sip:(.*?)@/)?.[1] || 'default';
        server.log.info(`Incoming call directed to DID: ${calledNumber}`);

        // Fetch Agent Configuration dynamically from Laravel API
        const LARAVEL_BASE = process.env.LARAVEL_BASE_URL || 'http://127.0.0.1:8000';
        let agentId = 1;

        server.log.info(`Querying Laravel for agent configuration via DID: ${calledNumber}`);
        try {
            const agentRes = await axios.get(`${LARAVEL_BASE}/api/v1/agents/by-number?number=${calledNumber}`);
            if (agentRes.data && agentRes.data.agent_id) {
                agentId = agentRes.data.agent_id;
                server.log.info(`Mapped DID ${calledNumber} to Agent ID ${agentId}`);
            }
        } catch (httpErr: any) {
            server.log.warn(`Failed to execute DID lookup for ${calledNumber}. Defaulting to Agent 1. Error: ${httpErr.message}`);
        }

        // Initialize Native RTP Bridge for Audio
        const rtp = new RtpBridge();
        const localPort = await (rtp as any).bind();
        server.log.info(`RTP Bridge bound on local UDP port: ${localPort}`);

        // Initialize AI Voice Engine
        const engine = new VoiceOrchestrator();

        // 1. Pipe Incoming RTP Payload -> Deepgram Speech-To-Text
        rtp.on('audio', (payload: Buffer) => {
            (engine as any).receiveAudio(payload);
        });

        // 2. Pipe Outgoing Voice Engine audio -> RTP Sender (ElevenLabs -> Phone)
        engine.on('audio_output', (audioChunk: Buffer) => {
            rtp.send(audioChunk);
        });

        // Answer the call with an SDP targeting our local RTP Bridge port
        const sdp = [
            'v=0',
            `o=drachtio 0 0 IN IP4 ${req.local_address}`,
            's=VoiceAgent',
            `c=IN IP4 ${req.local_address}`,
            't=0 0',
            `m=audio ${localPort} RTP/AVP 0`, // PCMU (ulaw) 8000Hz payload type 0
            'a=rtpmap:0 PCMU/8000',
            'a=sendrecv'
        ].join('\r\n') + '\r\n';

        const uas = await srf.createUAS(req, res, {
            localSdp: sdp,
            headers: {
                'Content-Type': 'application/sdp'
            }
        });

        server.log.info(`SIP Call Connected! Starting AI Engine (Agent ${agentId}).`);
        await (engine as any).start({ 
            agentId,
            media: {
                encoding: 'mulaw',
                sample_rate: 8000,
                output_format: 'ulaw_8000'
            }
        });

        // ── DTMF Handling (Plan Part 6.3) ────────────────────────────────────
        // 0 → Transfers to a human agent.
        // # → Repeats the last message (for accessibility/hard-of-hearing callers).
        let lastAgentMessage = '';

        engine.on('token', (token: string) => {
            lastAgentMessage += token;
        });

        // Fix 7: Reset accumulator after each agent turn — prevents unbounded growth
        // Without this, pressing # after 10 turns would accumulate the entire conversation
        engine.on('audio_complete', () => {
            lastAgentMessage = '';
        });

        (uas as any).on('dtmf', (obj: { digit: string }) => {
            const digit = obj?.digit;
            server.log.info(`[DTMF] Key pressed: ${digit}`);

            if (digit === '0') {
                server.log.info('[DTMF] Transferring to human agent...');
                engine.injectTranscript('Please transfer me to a human agent.');
            } else if (digit === '#') {
                server.log.info('[DTMF] Repeating last agent message...');
                engine.injectTranscript('Can you repeat that?');
                lastAgentMessage = '';
            }
        });

        uas.on('destroy', () => {
            server.log.info('SIP Call Ended by Remote.');
            rtp.close();
            engine.endCall('completed');
        });

    } catch (err: any) {
        server.log.error(`Failed to handle SIP INVITE: ${err.message}`);
        res.send(500, { headers: { 'X-Reason': 'Server Error' } });
    }
});

server.get('/health', async () => {
    return { status: 'ok', sip: 'connected' };
});

server.post('/simulate-call', async (request, reply) => {
    server.log.info('Simulating call with voice-engine...');

    try {
        const agentId = (request.body as any)?.agent_id ?? (request.query as any)?.agent_id ?? 1;
        const engine = new VoiceOrchestrator();

        await engine.start({ agentId });
        server.log.info('Voice engine started with agent_id=%s', agentId);

        return {
            status: 'success',
            message: 'Voice engine (Deepgram + Groq + ElevenLabs) started.',
            agent_id: agentId,
        };
    } catch (err: any) {
        server.log.error(err);
        return reply.code(500).send({ status: 'error', message: err?.message || 'Failed to start voice engine' });
    }
});

server.get('/test-zadarma', async (request, reply) => {
    const apiKey = process.env.ZADARMA_API_KEY || process.env.ZADARMA_KEY || '';
    const apiSecret = process.env.ZADARMA_API_SECRET || process.env.ZADARMA_SECRET || '';

    const zadarma = new ZadarmaService({
        apiKey,
        apiSecret,
        logger: server.log,
    });

    const results: any = { timestamp: new Date().toISOString(), tests: [] };

    const endpoints = [
        { name: '/v1/tariff/', fn: () => zadarma.getTariff() },
        { name: '/v1/info/timezone/', fn: () => zadarma.getTimezone() },
        { name: '/v1/sip/', fn: () => zadarma.getSipList() },
        { name: '/v1/pbx/internal/', fn: () => zadarma.getPbxInternal() },
        { name: '/v1/info/balance', fn: () => zadarma.getBalance() },
        { name: '/v1/reseller/users/list', fn: () => zadarma.getResellerUsers() },
    ];

    for (const { name, fn } of endpoints) {
        try {
            const data = await fn();
            results.tests.push({ endpoint: name, status: 'success', data });
        } catch (err: any) {
            results.tests.push({ endpoint: name, status: 'error', message: err.message });
        }
    }

    const successCount = results.tests.filter((t: any) => t.status === 'success').length;
    results.summary = `${successCount}/${results.tests.length} endpoints successful`;

    return results;
});

const start = async () => {
    try {
        const port = parseInt(process.env.PORT || '8080', 10);
        await server.listen({ port, host: '0.0.0.0' });
        console.log(`Telephony server listening on port ${port}`);
    } catch (err) {
        server.log.error(err);
        process.exit(1);
    }
};

start();
