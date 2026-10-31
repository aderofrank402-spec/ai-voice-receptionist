# Deepgram connectivity – root cause and fixes

## What was checked (facts)

### 1. Codebase

- **Voice-engine** uses `@deepgram/sdk` v4.11.3 with:
  - `createClient(process.env.DEEPGRAM_API_KEY || '')` (now: explicit options, see below)
  - `deepgram.listen.live({ model: 'nova-2', encoding: 'mulaw', sample_rate: 8000, channels: 1, language: 'en-US', smart_format: true, interim_results: true, endpointing: 300 })`
- **SDK behaviour (from SDK source):**
  - Auth: `Authorization: Token <key>` (correct per [Deepgram docs](https://developers.deepgram.com/docs/authenticating): API key uses `Token` scheme).
  - URL: `wss://api.deepgram.com/v1/listen?model=nova-2&encoding=mulaw&sample_rate=8000&...` (correct; query params match [live streaming docs](https://developers.deepgram.com/docs/getting-started-with-live-streaming-audio) and SDK `LiveSchema`).
  - Node: SDK uses `ws` with `{ headers: this.headers }`; key is passed via headers.
- **REST preflight** (`GET https://api.deepgram.com/v1/projects` with `Authorization: Token <key>`) is correct and matches [List Projects](https://developers.deepgram.com/reference/management-api/projects/list).

### 2. Observed failures

- **REST:** `ETIMEDOUT` – no response before timeout (e.g. 10s).
- **WebSocket:** “Received network error or non-101 status code” with **Ready State: CONNECTING** – handshake never completes.

So the machine never gets a normal HTTP/WebSocket response from `api.deepgram.com` (no 200, 401, 403, etc.).

### 3. Root cause (fact-based)

- **Not a bug in our code or wrong API usage:** Parameters, URL, and auth match the docs and SDK.
- **Not invalid key:** Failure is “no response” / “network error”, not 401/403.
- **Actual cause:** Outbound connectivity from the machine to `api.deepgram.com` is failing (timeout or connection error). Typical reasons:
  1. **Corporate proxy** – Node/`ws` do not use `HTTP_PROXY`/`HTTPS_PROXY` by default for every connection; without proxy support, both REST and WebSocket can hang or fail.
  2. **Firewall** – Blocking or throttling to `api.deepgram.com`.
  3. **VPN/DNS** – Routing or resolution issues.

## Changes made in code

1. **Explicit client options**  
   Deepgram client is created with explicit options (e.g. `{ key: process.env.DEEPGRAM_API_KEY ?? '' }`) so the key is always taken from env at creation time and empty string is not passed by mistake.

2. **Proxy support for WebSocket**  
   If `HTTPS_PROXY` or `HTTP_PROXY` is set, the Deepgram WebSocket uses `https-proxy-agent` so the live connection goes through the proxy. REST preflight (axios) already respects these env vars in Node.

3. **Preflight hint**  
   When the preflight fails with no HTTP response, the script suggests setting `HTTPS_PROXY` (or `HTTP_PROXY`) if they are not already set.

## What you should do

- **If you are behind a corporate proxy:**  
  Set `HTTPS_PROXY` (and optionally `HTTP_PROXY`) to your proxy URL (e.g. `http://proxy.company:3128`), then run `npm run check-deepgram` and `npm run test:engine` again.

- **If you are not behind a proxy:**  
  Confirm from the same machine that `api.deepgram.com` is reachable (e.g. `curl -v --max-time 15 https://api.deepgram.com/v1/projects -H "Authorization: Token YOUR_KEY"`). If that also times out, the block is at network/firewall level; fix connectivity to Deepgram (or use a network where it works).

- **Dependency:**  
  `https-proxy-agent` was added; run `npm install` in `voice-engine` if you have not already.
