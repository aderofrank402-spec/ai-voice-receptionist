<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateCrawlerSecret
{
    /**
     * Validate crawl service webhook: HMAC signature (preferred) or X-Crawler-Secret header.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('crawler.service_secret');
        if (empty($secret)) {
            return response()->json(['error' => 'Crawl webhook not configured'], 503);
        }

        $signatureHeader = $request->header('X-Crawler-Signature');
        if (is_string($signatureHeader) && $signatureHeader !== '') {
            $rawBody = $request->getContent();
            $expected = 'sha256=' . hash_hmac('sha256', $rawBody, $secret);
            if (! hash_equals($expected, $signatureHeader)) {
                return response()->json(['error' => 'Invalid signature'], 401);
            }
            return $next($request);
        }

        $header = $request->header('X-Crawler-Secret') ?? $request->header('Authorization');
        $token = is_string($header) && str_starts_with($header, 'Bearer ')
            ? substr($header, 7)
            : $header;

        if (! is_string($token) || ! hash_equals($secret, $token)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
