import fs from 'fs';
import path from 'path';
import os from 'os';

/**
 * RecordingService — Phase 1 Implementation
 *
 * Dual-channel disk-incremental recording.
 * - Inbound (caller audio) and outbound (AI audio) are written to disk immediately.
 * - No RAM buffering. A 1-hour call uses constant memory.
 * - S3 upload is stubbed — activated in Phase 6.
 * - Safe against server crashes: partial recordings are preserved on disk.
 */
export class RecordingService {
    private inboundStream: fs.WriteStream | null = null;
    private outboundStream: fs.WriteStream | null = null;
    private callId: string = '';
    private enabled: boolean = false;
    private tmpDir: string = os.tmpdir();

    public startRecording(callId: string): void {
        this.callId = callId;
        this.enabled = true;

        const inboundPath = this.getPath('inbound');
        const outboundPath = this.getPath('outbound');

        this.inboundStream = fs.createWriteStream(inboundPath, { flags: 'a' });
        this.outboundStream = fs.createWriteStream(outboundPath, { flags: 'a' });

        this.inboundStream.on('error', (err) => {
            console.error(`[Recording] Inbound stream error: ${err.message}`);
        });
        this.outboundStream.on('error', (err) => {
            console.error(`[Recording] Outbound stream error: ${err.message}`);
        });

        console.log(`[Recording] Started. Inbound: ${inboundPath}`);
    }

    /**
     * Append a caller audio chunk to disk immediately.
     * Called from VoiceOrchestrator.receiveAudio() — every inbound packet.
     */
    public appendInbound(chunk: Buffer): void {
        if (this.enabled && this.inboundStream?.writable) {
            this.inboundStream.write(chunk);
        }
    }

    /**
     * Append an AI audio chunk to disk immediately.
     * Called from ElevenLabs 'audio' event — every outbound packet.
     */
    public appendOutbound(chunk: Buffer): void {
        if (this.enabled && this.outboundStream?.writable) {
            this.outboundStream.write(chunk);
        }
    }

    /**
     * Phase 1 STUB: Close streams. Log paths.
     * Phase 6 ACTIVATION: Merge stereo .wav → upload to S3 → return URL.
     */
    public async finalizeAndUpload(callId: string): Promise<string> {
        this.closeStreams();

        const inboundPath = this.getPath('inbound');
        const outboundPath = this.getPath('outbound');

        // PHASE 6 STUB — S3 upload will be wired here
        console.log(`[Recording] Call ${callId} complete.`);
        console.log(`[Recording] Inbound raw: ${inboundPath}`);
        console.log(`[Recording] Outbound raw: ${outboundPath}`);
        console.log(`[Recording] S3 upload: STUB (activate in Phase 6)`);

        return `file://${inboundPath}`; // Placeholder URL
    }

    /**
     * Delete temp files after successful S3 upload (Phase 6).
     * Safe to call even if files don't exist.
     */
    public cleanup(callId: string): void {
        this.closeStreams();
        this.deleteSafe(this.getPath('inbound'));
        this.deleteSafe(this.getPath('outbound'));
        console.log(`[Recording] Temp files cleaned for call ${callId}`);
    }

    private getPath(channel: 'inbound' | 'outbound'): string {
        return path.join(this.tmpDir, `${this.callId}_${channel}.raw`);
    }

    private closeStreams(): void {
        if (this.inboundStream) {
            this.inboundStream.end();
            this.inboundStream = null;
        }
        if (this.outboundStream) {
            this.outboundStream.end();
            this.outboundStream = null;
        }
        this.enabled = false;
    }

    private deleteSafe(filePath: string): void {
        try {
            if (fs.existsSync(filePath)) {
                fs.unlinkSync(filePath);
            }
        } catch (e) {
            // Silently fail — disk cleanup is best-effort
        }
    }
}
