import fastify from 'fastify';
import dotenv from 'dotenv-safe';
import Srf from 'drachtio-srf';
import websocket from '@fastify/websocket';
import { CustomRealtimeService } from './services/CustomRealtimeService';

// Load environment variables
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
                    options: { destination: '../storage/logs/voice-engine.log' }
                }
            ]
        }
    }
});

// ... (existing Drachtio logic remains)

// 1. Register WebSocket Support
server.register(websocket);

server.register(async (fastify) => {
    fastify.get('/voice-test', { websocket: true }, (connection, req) => {
        const query = req.query as any;
        const agentId = query.agent_id || 1;
        
        server.log.info(`New Dashboard WebSocket Connection for Agent: ${agentId}`);
        
        // The 'connection' object is a SocketStream, we need the underlying 'socket'
        const socket = (connection as any).socket || connection;
        const service = new CustomRealtimeService(socket, server.log, agentId);
        
        service.start().catch(err => {
            server.log.error(`Failed to start voice service: ${err.message}`);
        });

        socket.on('message', (message: any) => {
            if (Buffer.isBuffer(message)) {
                service.handleIncomingAudio(message);
            }
        });

        socket.on('close', () => {
            service.stop();
        });
    });
});

// Fastify Routes (Health Check)
server.get('/health', async (request, reply) => {
    return { status: 'ok', engine: 'custom-active' };
});

const start = async () => {
    try {
        await server.listen({ port: parseInt(process.env.PORT || '3000'), host: '0.0.0.0' });
        console.log(`Custom Realtime Hub listening on port ${process.env.PORT || 3000}`);
    } catch (err) {
        server.log.error(err);
        process.exit(1);
    }
};

start();
