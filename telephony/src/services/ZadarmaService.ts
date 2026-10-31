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

    private getAuthHeader(methodUrl: string, params: Record<string, string> = {}): string {
        if (!params.format) params.format = 'json';
        const sortedKeys = Object.keys(params).sort();
        const paramsStr = sortedKeys.map(k => `${encodeURIComponent(k)}=${encodeURIComponent(params[k]).replace(/%20/g, '+')}`).join('&');
        const md5Hash = crypto.createHash('md5').update(paramsStr).digest('hex');
        const dataToSign = methodUrl + paramsStr + md5Hash;
        const signature = crypto.createHmac('sha1', this.apiSecret).update(dataToSign).digest('base64');
        return `${this.apiKey}:${signature}`;
    }

    private async callApi(method: 'GET' | 'POST' | 'PUT', endpoint: string, params: Record<string, string> = {}) {
        const authHeader = this.getAuthHeader(endpoint, params);
        try {
            const response = await axios({ method, url: `${this.baseUrl}${endpoint}`, headers: { Authorization: authHeader }, params });
            return response.data;
        } catch (error: any) {
            this.logger.error(`Zadarma API Error: ${error.message}`);
            if (error.response) this.logger.error(error.response.data);
            throw error;
        }
    }

    public async getBalance() { return this.callApi('GET', '/v1/info/balance'); }
    public async getTariff() { return this.callApi('GET', '/v1/tariff'); }
    public async getTimezone() { return this.callApi('GET', '/v1/info/timezone'); }
    public async getSipList() { return this.callApi('GET', '/v1/sip'); }
    public async getPbxInternal() { return this.callApi('GET', '/v1/pbx/internal'); }
    public async getResellerUsers() { return this.callApi('GET', '/v1/reseller/users/list'); }
}
