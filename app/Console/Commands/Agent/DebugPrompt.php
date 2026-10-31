<?php

namespace App\Console\Commands\Agent;

use App\Models\Agent;
use Illuminate\Console\Command;

class DebugPrompt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agent:debug-prompt {agent_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Displays the fully generated system prompt for an agent including Knowledge Base injection.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $agentId = $this->argument('agent_id');
        
        if ($agentId) {
            $agent = Agent::findOrFail($agentId);
        } else {
            $agent = Agent::first();
        }

        if (!$agent) {
            $this->error('No agents found in database.');
            return;
        }

        $this->info("=== Debugging System Prompt for Agent: {$agent->name} ===");
        $this->line($agent->system_prompt);
        $this->info("=========================================================");
        
        $count = $agent->knowledgeSources()->where('status', 'ready')->count();
        $this->comment("Total Knowledge Sources Injected: {$count}");
    }
}
