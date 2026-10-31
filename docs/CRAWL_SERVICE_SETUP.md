# Crawl Service Setup (New Architecture)

From "user submits URL" to "results rendered" is **at most 60 seconds**. The crawler runs in a **standalone Node service** (45s max); Laravel only triggers it and receives progress via webhook.

**Reliability features:** Correlation ID (end-to-end tracing), HMAC-signed callbacks, optional Redis queue trigger (at-least-once), and idempotent webhook (no double-persist).

## 1. Add to `.env`

```env
CRAWLER_SERVICE_URL=http://127.0.0.1:3090
CRAWLER_SERVICE_SECRET=your-secret-string-here
# Optional: trigger via Redis instead of HTTP (requires Redis and crawl service with CRAWLER_USE_QUEUE=1)
CRAWLER_USE_QUEUE=false
CRAWLER_QUEUE_KEY=crawl_requests
```

- **Correlation ID:** Generated per crawl and passed job → crawl service → webhook; use it in logs to trace one flow.
- **HMAC:** Crawl service signs callback body with `X-Crawler-Signature: sha256=<hex>`. Laravel verifies it; you can also use `X-Crawler-Secret` for auth.
- **Idempotency:** Webhook uses `correlation_id` so duplicate callbacks do not persist twice (24h TTL).

## 2. Start the crawl service

From the project root:

```bash
cd crawler
npm install
npm run serve
```

This starts the HTTP server on port 3090. It accepts `POST /crawl` and sends progress/final to Laravel at `APP_URL/api/crawler-progress` with correlation_id and HMAC signature.

**Queue mode:** If Laravel uses `CRAWLER_USE_QUEUE=true`, start the crawl service with Redis and queue consumer enabled:

```bash
CRAWLER_USE_QUEUE=1 REDIS_HOST=127.0.0.1 REDIS_PORT=6379 npm run serve
```

Then Laravel pushes to Redis list `CRAWLER_QUEUE_KEY` and the crawl service consumes with `BRPOP`. Requires `ioredis` (in crawler package.json).

## 3. Start Laravel, queue worker, and (optional) Reverb

- **Laravel:** `php artisan serve` (or your usual APP_URL)
- **Queue worker:** `php artisan queue:work` (processes `ScrapeWebsiteJob`, which triggers the crawl service)
- **Reverb (WebSockets):** `php artisan reverb:start` if you want live progress in the UI

## 4. Verify

From project root:

```bash
php scripts/verify_crawl_stack.php
```

This checks:

- `CRAWLER_SERVICE_URL` and `CRAWLER_SERVICE_SECRET` are set
- Crawl service `/health` returns 200
- Laravel webhook rejects wrong secret/signature (401) and accepts valid auth (200). Callbacks can use either `X-Crawler-Signature` (HMAC of body) or `X-Crawler-Secret`.

Optional trigger (sends a real crawl request to the service):

```bash
php scripts/verify_crawl_stack.php --trigger-crawl
```

## 5. E2E

1. Log in and open **Onboarding**.
2. Paste a URL (e.g. `https://example.com`) and press Enter.
3. The UI switches to the progress view; you should see steps and "Pages found" within 60s.
4. On success or time limit, you get either the result or "Time limit reached (60s). Showing partial results if any."

## Time limits

- **Crawl service:** 45s max per crawl; after that it sends partial result and `done: true`.
- **Laravel job:** 55s timeout when calling the crawl service (so it doesn’t block the worker).
- **Frontend:** 60s total; then it shows "Time limit reached" and any partial data.
