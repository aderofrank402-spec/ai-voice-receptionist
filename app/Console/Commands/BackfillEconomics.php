<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Call;
use App\Services\EconomicsService;

class BackfillEconomics extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'economics:backfill {--all : Re-calculate even those with costs}';

    /**
     * The console command description.
     */
    protected $description = 'Retroactively calculate and populate granular costs for all calls.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting Economics Backfill...");
        
        $query = Call::query();
        if (!$this->option('all')) {
            $query->whereNull('stt_cost_usd')->orWhere('stt_cost_usd', 0);
        }

        $calls = $query->get();
        $this->info("Found " . $calls->count() . " calls to process.");

        $bar = $this->output->createProgressBar($calls->count());
        $bar->start();

        foreach ($calls as $call) {
            // For old calls that don't have tokens/chars, we'll estimate them from the duration
            // Average: 120 tokens/min, 600 characters/min
            $tokens = $call->tokens_used ?: round(($call->duration / 60) * 120);
            $chars = $call->characters_used ?: round(($call->duration / 60) * 600);

            $economics = EconomicsService::calculate(
                (int) $call->duration,
                (int) $tokens,
                (int) $chars,
                $call->caller_number,
                (int) ($call->stt_seconds ?: $call->duration)
            );

            $call->update([
                'stt_cost_usd'       => $economics['costs']['stt'],
                'llm_cost_usd'       => $economics['costs']['llm'],
                'tts_cost_usd'       => $economics['costs']['tts'],
                'telephony_cost_usd' => $economics['costs']['telephony'],
                'cost'               => $economics['total_revenue_usd'],
                'total_cost_kes'     => $economics['total_revenue_kes'],
                'tokens_used'        => $tokens,
                'characters_used'    => $chars,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Backfill complete! Your dashboard is now alive.");
    }
}
