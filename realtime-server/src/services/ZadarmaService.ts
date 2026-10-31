import axios from 'axios';
import crypto from 'crypto';
import { FastifyBaseLogger } from 'fastify';

interface ZadarmaOptions {
    apiKey: string;
    apiSecret: string;
    logger: FastifyBaseLogger;
}

export class ZadarmaService {
    private apiKey: string;
    private apiSecret: string;
    private logger: FastifyBaseLogger;
    private baseUrl = 'https://api.zadarma.com';

    constructor(options: ZadarmaOptions) {
        this.apiKey = options.apiKey;
        this.apiSecret = options.apiSecret;
        this.logger = options.logger;
    }

    /**
     * Generate Auth Header for Zadarma API
     * Algorithm:
     * 1. Sort query params alphabetically
     * 2. Build Query String (request_line)
     * 3. MD5(request_line)
     * 4. data = method_url + request_line + md5
     * 5. Signature = Base64( HMAC_SHA1(secret, data) )
     * 6. Header = "api_key:signature"
     */
    private getAuthHeader(methodUrl: string, params: Record<string, string> = {}): string {
        // CRITICAL: Always add format=json, even for GET requests
        if (!params.format) {
            params.format = 'json';
        }

        // 1. Sort params alphabetically (like ksort in PHP)
        const sortedKeys = Object.keys(params).sort();

        // 2. Build query string with URL encoding (like http_build_query in PHP)
        const paramsStr = sortedKeys.map(key => `${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`).join('&');

        // 3. MD5 of paramsStr (returns hex, like PHP's md5())
        const md5Hash = crypto.createHash('md5').update(paramsStr).digest('hex');

        // 4. Construct data string: method + paramsStr + md5(paramsStr)
        const dataToSign = methodUrl + paramsStr + md5Hash;

        this.logger.info(`[AuthDebug] Method: '${methodUrl}'`);
        this.logger.info(`[AuthDebug] ParamsStr: '${paramsStr}'`);
        this.logger.info(`[AuthDebug] MD5(ParamsStr): '${md5Hash}'`);
        this.logger.info(`[AuthDebug] DataToSign: '${dataToSign}'`);

        // 5. HMAC-SHA1 with secret, then base64 encode (DIRECT - recommended approach)
        const signature = crypto
            .createHmac('sha1', this.apiSecret)
            .update(dataToSign)
            .digest('base64');

        this.logger.info(`[AuthDebug] Signature: '${signature}'`);

        return `${this.apiKey}:${signature}`;
    }

    /**
     * Generic API Call Wrapper
     */
    private async callApi(method: 'GET' | 'POST' | 'PUT', endpoint: string, params: Record<string, string> = {}) {
        const authHeader = this.getAuthHeader(endpoint, params);

        // For GET, params go in URL. For POST, they might be body (but Zadarma usually signs query params).
        // The signature logic implies query params. 
        // Important: Zadarma expects params in Body for POST usually but signature is different.
        // Let's stick to GET for fetching profile for now.

        try {
            const response = await axios({
                method,
                url: `${this.baseUrl}${endpoint}`,
                headers: {
                    'Authorization': authHeader
                },
                params: params
            });
            return response.data;
        } catch (error: any) {
            this.logger.error(`Zadarma API Error: ${error.message}`);
            if (error.response) {
                this.logger.error(error.response.data);
            }
            throw error;
        }
    }

    /**
     * Get User Profile / Balance
     * GET /v1/info/balance/
     */
    public async getBalance() {
        return this.callApi('GET', '/v1/info/balance');
    }

    /**
     * Get Tariff Plan (Less sensitive)
     * GET /v1/tariff/
     */
    public async getTariff() {
        return this.callApi('GET', '/v1/tariff');
    }

    /**
     * Get Timezone Info (Public info)
     * GET /v1/info/timezone/
     */
    public async getTimezone() {
        return this.callApi('GET', '/v1/info/timezone');
    }

    /**
     * Get List of SIP Numbers
     * GET /v1/sip/
     */
    public async getSipList() {
        return this.callApi('GET', '/v1/sip');
    }

    /**
     * Get List of PBX Internal Numbers
     * GET /v1/pbx/internal/
     */
    public async getPbxInternal() {
        return this.callApi('GET', '/v1/pbx/internal');
    }

    /**
     * Get Reseller Users List (TEST if using reseller keys)
     * GET /v1/reseller/users/list
     */
    public async getResellerUsers() {
        return this.callApi('GET', '/v1/reseller/users/list');
    }
}
