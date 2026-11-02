# Security and privacy model

The system can process phone numbers, call audio, transcripts, business knowledge, appointment details, payment references, OAuth tokens, and provider credentials. A deployment must treat these as sensitive operational data.

## Authentication boundaries

- Human sessions use Laravel authentication, CSRF protection, and role middleware.
- Third-party API consumers use Sanctum tokens and throttled routes.
- Voice, telephony, and crawler services use a dedicated shared secret; crawler payloads support HMAC-SHA256 signatures.
- Administrative routes require authentication and the admin middleware.

The internal service secret must be high entropy, shared only with required services, and rotated independently of provider keys.

## Credential handling

Platform credentials stored in the database use Laravel's encrypted cast. Environment variables bootstrap infrastructure credentials. The public repository contains only blank examples.

Never place secrets in browser-delivered configuration, logs, screenshots, recordings, seed data, migrations, or Git history.

## Caller data

Deployments should play the recording notice before collection, document lawful purpose and retention, restrict access by tenant and role, redact logs, encrypt storage and backups, delete expired material, and preserve a human escalation path.

The application emits a recording notice when a voice session starts, but legal requirements vary by jurisdiction and remain the deployer's responsibility.

## Tool safety

Tool calls validate required fields before execution. If a caller interrupts, results from the previous turn are discarded. Payments and other consequential actions should retain explicit confirmation, provider idempotency keys, amount limits, and tenant-specific policy checks.

## Publication controls

The repository validator rejects production environment files, private keys, local databases, recordings, transcripts, logs, and common live-secret patterns. Automated scanning reduces risk but cannot replace review of every staged diff.

Report vulnerabilities using [SECURITY.md](../SECURITY.md).
