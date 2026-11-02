# Changelog

## [Unreleased]

### Added

- Architecture, security, operations, engineering-decision, and portfolio-evidence documentation
- Independent CI gates for Laravel and four TypeScript service boundaries
- Publication-safety validation for secrets, private data, recordings, databases, logs, and required documentation
- Regression coverage for authenticated internal agent configuration and payment-status lookup

### Fixed

- Added shared-secret authentication to voice-engine and telephony internal API calls
- Added the missing internal payment-status endpoint used by the voice state machine
- Persisted appointment bookings instead of returning synthetic confirmations
- Added safe payment mass-assignment and metadata casting
- Preserved call references through payment callbacks so polling can resolve completed payments
- Removed duplicate agent-test routing and duplicate prompt schema fields

## [1.0.0] - 2026-10-29

### Portfolio release

- Published a credential-cleaned multi-service application snapshot
- Added placeholder environment examples for the control plane and runtime services
- Excluded production caller data, recordings, transcripts, logs, databases, and generated dependencies
