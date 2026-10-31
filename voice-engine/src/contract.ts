/**
 * Contract between voice-engine and telephony (or any caller).
 * Telephony feeds audio in and consumes audio/events out; no direct STT/LLM/TTS coupling.
 */

export const VOICE_ENGINE_EVENTS = {
  /** Outbound audio chunk (Buffer) – send to RTP/caller. */
  AUDIO_OUTPUT: 'audio_output',
  /** Barge-in: clear send buffer and stop playback. */
  AUDIO_OUTPUT_KILL: 'audio_output_kill',
  /** User requested transfer to human; payload: reason string. */
  TRANSFER_REQUEST: 'transfer_request',
} as const;

export interface IVoiceEngineSession {
  /** Push raw audio (e.g. mulaw 8kHz) into the engine. */
  processAudio(buffer: Buffer): void;
  /** Start the session (fetch config, connect STT/TTS). */
  start(options?: { agentId?: number | string }): Promise<void>;
  /** End the session and persist transcript to Laravel. */
  endCall(status?: string): Promise<void>;
  /** Subscribe to outbound audio. */
  on(event: typeof VOICE_ENGINE_EVENTS.AUDIO_OUTPUT, listener: (chunk: Buffer) => void): this;
  /** Subscribe to barge-in signal. */
  on(event: typeof VOICE_ENGINE_EVENTS.AUDIO_OUTPUT_KILL, listener: () => void): this;
  /** Subscribe to transfer request. */
  on(event: typeof VOICE_ENGINE_EVENTS.TRANSFER_REQUEST, listener: (reason: string) => void): this;
}
