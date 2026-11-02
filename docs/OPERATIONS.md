# Operations and deployment

## Runtime processes

A complete deployment separates Laravel HTTP, queue workers, the scheduler, Reverb, the voice engine, telephony gateway, Drachtio, crawler, Redis, and the relational database. Run each under a supervisor with bounded restarts, health checks, resource limits, and separate logs.

## Configuration

Start with the root and service-level `.env.example` files. Generate unique values for `APP_KEY`, `CRAWLER_SERVICE_SECRET`, Reverb credentials, database passwords, and SIP secrets. Provider keys must be scoped to the minimum required capability.

## Deployment order

1. Provision database, Redis, storage, and encrypted secrets.
2. Deploy Laravel and run `php artisan migrate --force` once.
3. Start queues, scheduler, and Reverb.
4. Deploy crawler and verify signed callbacks.
5. Deploy voice engine and verify protected configuration plus provider connectivity.
6. Deploy Drachtio and telephony; verify DID resolution before accepting traffic.
7. Place HTTP behind TLS and restrict internal endpoints at the network layer.
8. Place one synthetic call using non-production data.

## Readiness

Verify database/cache access, queue age, Drachtio connectivity, test-DID resolution, STT/TTS socket establishment, fail-closed internal authentication, crawler signature rejection, and recording-storage capacity.

## Observability

The application records call lifecycle data, provider traces, latency, usage, costs, crawl progress, audit events, and system health. Alert on error rate, provider timeouts, queue delay, setup failures, abandoned calls, payment callback failures, and disk usage. Never log keys, authorization headers, unrestricted payment callbacks, or full transcripts.

## Verification

```bash
composer install --no-interaction --prefer-dist
php artisan test
npm ci && npm run build
(cd voice-engine && npm ci && npm run build)
(cd telephony && npm ci && npm run build)
(cd crawler && npm ci && npm run build)
(cd realtime-server && npm ci && npx tsc --noEmit)
php scripts/validate_public_repo.php
```

Provider-backed calls, payments, and messages require isolated staging accounts and are not part of CI.
