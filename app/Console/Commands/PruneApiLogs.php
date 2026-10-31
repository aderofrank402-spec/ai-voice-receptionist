<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PruneApiLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prune-api-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune API request logs older than 7 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = \App\Models\ApiRequestLog::where('created_at', '<', now()->subDays(7))->delete();
        $this->info("Successfully pruned {$count} old API request logs.");
    }
}
