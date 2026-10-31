<?php

namespace App\Support;

use GuzzleHttp\Client;
use OpenAI\Laravel\Facades\OpenAI as OpenAIFacade;

/**
 * Centralised HTTP client factory.
 *
 * Purpose: Replaces 9+ ad-hoc `new GuzzleHttp\Client(['verify' => false])` calls
 * with a single, config-driven factory.  SSL verification is controlled by the
 * OPENAI_VERIFY_SSL env variable — default TRUE in production, overrideable to
 * FALSE in local dev on Windows where the CA bundle is not installed.
 */
class HttpClientFactory
{
    /**
     * Return a Guzzle client whose SSL verification obeys config('openai.verify_ssl').
     *
     * @param  array  $extra  Additional Guzzle options merged on top of the defaults.
     */
    public static function make(array $extra = []): Client
    {
        return new Client(array_merge([
            'verify'  => config('openai.verify_ssl', true),
            'timeout' => 10,
        ], $extra));
    }

    /**
     * Build a configured OpenAI PHP SDK client using the centralized factory.
     *
     * @param  int  $timeout  Request timeout in seconds.
     */
    public static function openAiClient(int $timeout = 10): \OpenAI\Client
    {
        return \OpenAI::factory()
            ->withApiKey(config('openai.api_key'))
            ->withHttpClient(self::make(['timeout' => $timeout]))
            ->make();
    }
}
