import dgram from 'dgram';
import { EventEmitter } from 'events';
import crypto from 'crypto';

export class RtpBridge extends EventEmitter {
    private socket: dgram.Socket;
    private localPort: number = 0;
    private remotePort: number | null = null;
    private remoteAddress: string | null = null;
    
    private audioQueue: Buffer = Buffer.alloc(0);
    private isThrottling: boolean = false;
    private throttleInterval: NodeJS.Timeout | null = null;
    
    // RTP Header construction state
    private ssrc: number;
    private seq: number;
    private timestamp: number;

    constructor() {
        super();
        this.socket = dgram.createSocket('udp4');
        this.ssrc = crypto.randomBytes(4).readUInt32BE(0);
        this.seq = Math.floor(Math.random() * 65535);
        this.timestamp = Math.floor(Math.random() * 0xFFFFFFFF);

        this.socket.on('message', (msg, rinfo) => {
            // Lock onto the remote RTP endpoint from the first incoming packet
            if (!this.remoteAddress || !this.remotePort) {
                this.remoteAddress = rinfo.address;
                this.remotePort = rinfo.port;
                this.startThrottler();
            }

            // Minimal RTP Parser (RFC 3550)
            if (msg.length < 12) return; // Too short for RTP

            const version = (msg[0] >> 6) & 0x03;
            if (version !== 2) return; // Not RTP version 2

            const cc = msg[0] & 0x0F; // CSRC count
            const pt = msg[1] & 0x7F; // Payload type
            
            // Calculate header length (12 base + CSRC identifiers)
            let headerLen = 12 + (cc * 4);
            
            // Check for extension bit
            const x = (msg[0] >> 4) & 0x01;
            if (x === 1 && msg.length >= headerLen + 4) {
                const extLen = msg.readUInt16BE(headerLen + 2);
                headerLen += 4 + (extLen * 4);
            }

            if (msg.length > headerLen) {
                const payload = msg.subarray(headerLen);
                // Currently only processing Payload Type 0 (PCMU / u-law)
                if (pt === 0) {
                    this.emit('audio', payload);
                }
            }
        });

        this.socket.on('error', (err) => {
            console.error('[RtpBridge] UDP Socket Error:', err);
        });
    }

    /** Binds the UDP socket to an ephemeral port and returns it. */
    public bind(port: number = 0): Promise<number> {
        return new Promise((resolve, reject) => {
            this.socket.on('listening', () => {
                this.localPort = this.socket.address().port;
                resolve(this.localPort);
            });
            this.socket.on('error', (err) => {
                reject(err);
            });
            this.socket.bind(port);
        });
    }

    private startThrottler() {
        if (this.isThrottling) return;
        this.isThrottling = true;

        // SIP expects 1 packet every 20ms for G711 (160 bytes)
        this.throttleInterval = setInterval(() => {
            if (this.audioQueue.length > 0) {
                const chunkSize = Math.min(this.audioQueue.length, 160);
                const chunk = this.audioQueue.subarray(0, chunkSize);
                this.audioQueue = this.audioQueue.subarray(chunkSize);
                this.sendPacket(chunk);
            }
        }, 20);
    }

    /**
     * Internal low-level sender that wraps a chunk in an RTP header.
     */
    private sendPacket(ulawBuffer: Buffer) {
        if (!this.remoteAddress || !this.remotePort) return;

        const header = Buffer.alloc(12);
        header[0] = 0x80; // Version 2
        header[1] = 0x00; // Payload type 0 (PCMU)
        header.writeUInt16BE(this.seq, 2);
        header.writeUInt32BE(this.timestamp, 4);
        header.writeUInt32BE(this.ssrc, 8);

        const packet = Buffer.concat([header, ulawBuffer]);
        this.socket.send(packet, 0, packet.length, this.remotePort, this.remoteAddress);

        this.seq = (this.seq + 1) & 0xFFFF;
        this.timestamp = (this.timestamp + ulawBuffer.length) >>> 0;
    }

    /**
     * Push raw u-law PCMU bytes into the jitter-safe throttle queue.
     * @param ulawBuffer Raw PCMU audio chunk chunks from ElevenLabs
     */
    public send(ulawBuffer: Buffer) {
        this.audioQueue = Buffer.concat([this.audioQueue, ulawBuffer]);
    }

    /**
     * Set the explicit remote target (usually extracted from SIP SDP negotiation)
     */
    public setRemote(ip: string, port: number) {
        this.remoteAddress = ip;
        this.remotePort = port;
    }

    /**
     * Close the socket
     */
    public close() {
        try {
            this.socket.close();
        } catch (e) {}
    }

    public getLocalPort(): number {
        return this.localPort;
    }
}
