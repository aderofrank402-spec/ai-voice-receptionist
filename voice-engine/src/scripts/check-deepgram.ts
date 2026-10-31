/**
 * Preflight: validate Deepgram API key via REST before using the live WebSocket.
 * Run from voice-engine: npx tsx src/scripts/check-deepgram.ts
 */
import dotenv from 'dotenv';
import path from 'path';
import axios from 'axios';
import { setDefaultResultOrder } from 'dns';

// Fix for Node.js ETIMEDOUT on some networks (favors IPv4)
setDefaultResultOrder('ipv4first');

dotenv.config({ path: path.resolve(process.cwd(), '.env') });

const key = process.env.DEEPGRAM_API_KEY || '';

async function main() {
    if (!key) {
        console.error('DEEPGRAM_API_KEY is not set in .env');
        process.exit(1);
    }
    try {
        const res = await axios.get('https://api.deepgram.com/v1/projects', {
            headers: { Authorization: `Token ${key}` },
            timeout: 10000,
        });
        console.log('Deepgram API key is valid. Projects:', res.data?.projects?.length ?? 0);
    } catch (err: any) {
        const status = err.response?.status;
        const data = err.response?.data;
        const noResponse = err.response == null;
        console.error('Deepgram API key check failed.');
        if (status === 401) console.error('  Cause: Invalid or expired API key (401 Unauthorized).');
        else if (status === 403) console.error('  Cause: Forbidden (403). Key may not have project access.');
        else if (noResponse) {
            console.error('  Cause: No HTTP response (network/timeout).', err.code || '', err.message || '');
            if (!process.env.HTTPS_PROXY && !process.env.HTTP_PROXY) {
                console.error('  If behind a corporate proxy, set HTTPS_PROXY (or HTTP_PROXY) and run again.');
            }
        } else console.error('  HTTP', status, data || err.message);
        process.exit(1);
    }
}

main();
