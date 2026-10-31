<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FetchExchangeRateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $response = Http::timeout(10)->get('https://api.exchangerate-api.com/v4/latest/USD');
            
            if ($response->successful()) {
                $rates = $response->json('rates');
                $rate = (float) ($rates['KES'] ?? 130.0);
                
                Cache::put('usd_to_kes_rate', $rate, 86400);
                Log::info("Exchange rate updated successfully via background job: {$rate}");
            } else {
                Log::warning("Exchange rate fetch failed with status: " . $response->status());
            }
        } catch (\Exception $e) {
            Log::error("Exchange rate background fetch failed: " . $e->getMessage());
        }
    }
}
