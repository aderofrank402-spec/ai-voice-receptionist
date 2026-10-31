/**
 * Voice Engine – public API for telephony or other callers.
 * Contract: processAudio in, audio_output / audio_output_kill / transfer_request out.
 */

export { VoiceOrchestrator } from './services/VoiceOrchestrator';
export { VOICE_ENGINE_EVENTS, type IVoiceEngineSession } from './contract';
