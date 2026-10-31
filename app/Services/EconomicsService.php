<?php

namespace App\Services;

use Log;

class EconomicsService
{
    /**
     * Provider Rates (USD)
     */
    const RATE_TELEPHONY_MIN = 0.0200; // $0.02 per min
    const RATE_STT_MIN       = 0.0044; // $0.0044 per min
    const RATE_LLM_1MTOKENS  = 0.6900; // $0.69 per 1M tokens
    const RATE_TTS_1KCHARS   = 0.2000; // $0.20 per 1k characters

    const PROFIT_MARGIN      = 1.50;   // 50% Markup
    const DEFAULT_USD_TO_KES = 130.0;  // Static fallback if API fails

    /**
     * Get the USD to KES exchange rate. 
     * NON-BLOCKING: Returns cached value or fallback, and dispatches a background refresh if needed.
     */
    public static function getExchangeRate(): float
    {
        $rate = \Illuminate\Support\Facades\Cache::get('usd_to_kes_rate');

        if ($rate === null) {
            // Cold Cache hit: Dispatch background refresh and return fallback immediately
            \App\Jobs\FetchExchangeRateJob::dispatch();
            return self::DEFAULT_USD_TO_KES;
        }

        return (float) $rate;
    }

    /**
     * Calculate granular costs and billing for a call.
     * 
     * @param int $durationSeconds
     * @param int $tokensUsed
     * @param int $charactersUsed
     * @param string|null $callerNumber
     * @param int|null $sttSeconds
     * @return array
     */
    public static function calculate(int $durationSeconds, int $tokensUsed, int $charactersUsed, ?string $callerNumber = null, ?int $sttSeconds = null): array
    {
        $durationMinutes = $durationSeconds / 60;
        $exchangeRate = self::getExchangeRate();
        
        // Use provided STT seconds, fallback to duration
        $sttMinutes = ($sttSeconds ?? $durationSeconds) / 60;

        // 1. Calculate Internal Costs (What we pay)
        // Telephony usage is ONLY charged if a phone number is involved
        $isPhoneCall = !empty($callerNumber);
        
        $costs = [
            'telephony' => $isPhoneCall ? round($durationMinutes * self::RATE_TELEPHONY_MIN, 4) : 0.0000,
            'stt'       => round($sttMinutes * self::RATE_STT_MIN, 4),
            'llm'       => round(($tokensUsed / 1000000) * self::RATE_LLM_1MTOKENS, 4),
            'tts'       => round(($charactersUsed / 1000) * self::RATE_TTS_1KCHARS, 4),
        ];

        $totalUsdCost = array_sum($costs);

        // 2. Calculate Customer Billing (Revenue with 50% Margin)
        $revenueUsd = $totalUsdCost * self::PROFIT_MARGIN;

        // Ensure a minimum charge for very short or empty calls (Platform Overhead)
        if ($revenueUsd < 0.05 && $durationSeconds > 5) {
            $revenueUsd = 0.05;
        }

        $revenueKes = round($revenueUsd * $exchangeRate, 2);

        return [
            'costs' => $costs,
            'total_cost_usd' => $totalUsdCost,
            'total_revenue_usd' => round($revenueUsd, 4),
            'total_revenue_kes' => $revenueKes,
            'exchange_rate' => $exchangeRate,
        ];
    }
}
