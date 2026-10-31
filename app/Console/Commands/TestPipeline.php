<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ComplianceAgent;
use App\Services\ProcessAgent;
use App\Services\PromptGeneratorAgent;

class TestPipeline extends Command
{
    protected $signature = 'test:pipeline';
    protected $description = 'Simulate the 4-Agent Onboarding Pipeline';

    public function handle(
        ComplianceAgent $compliance,
        ProcessAgent $process,
        PromptGeneratorAgent $generator
    ) {
        $this->info("🚀 STARTING PIPELINE SIMULATION");
        $this->line("------------------------------------------------");

        // --- SCENARIO 1: THE HAPPY PATH ---
        $this->info("\n🧪 TEST CASE 1: 'The Perfect Candidate' (Auto Shop)");
        $validTranscript = [
            ['role' => 'user', 'content' => 'I want to set up an agent.'],
            ['role' => 'assistant', 'content' => 'What is your business name?'],
            ['role' => 'user', 'content' => 'Nairobi Auto Fixers'],
            ['role' => 'assistant', 'content' => 'What services do you offer?'],
            ['role' => 'user', 'content' => 'Oil change for 3000 bob, engine diagnostics for 1500, and tire rotation for 500.'],
            ['role' => 'assistant', 'content' => 'What are your hours?'],
            ['role' => 'user', 'content' => 'We are open Mon-Fri 8am to 6pm.'],
            ['role' => 'assistant', 'content' => 'Great. [INTERVIEW_COMPLETE]'],
        ];

        // Step 1: Compliance
        $this->line("1️⃣  Compliance Agent checking...");
        $result = $compliance->validate($validTranscript);

        if ($result['approved']) {
            $this->info("✅ APPROVED: " . $result['reason']);

            // Step 2: Process
            $this->line("\n2️⃣  Process Agent transforming...");
            $blueprint = $process->extractBlueprint($validTranscript);
            $this->comment("   -> Extracted: " . json_encode($blueprint, JSON_PRETTY_PRINT));

            // Step 3: Prompt Generation
            $this->line("\n3️⃣  Prompt Generator building...");
            $prompt = $generator->generate($blueprint);
            $this->comment("\n📜 FINAL PROMPT PREVIEW:\n" . substr($prompt, 0, 300) . "...\n[TRUNCATED]");

        } else {
            $this->error("❌ FAILED: " . $result['reason']);
        }

        $this->line("\n------------------------------------------------");

        // --- SCENARIO 2: THE FAILURE PATH ---
        $this->info("\n🧪 TEST CASE 2: 'The Confusion' (Garage doing Dental)");
        $invalidTranscript = [
            ['role' => 'user', 'content' => 'Business name is Fast Cars Ltd.'],
            ['role' => 'assistant', 'content' => 'Services?'],
            ['role' => 'user', 'content' => 'We fix brakes and do root canals.'],
            ['role' => 'assistant', 'content' => '[INTERVIEW_COMPLETE]'],
        ];

        // Step 1: Compliance
        $this->line("1️⃣  Compliance Agent checking...");
        $result = $compliance->validate($invalidTranscript);

        if ($result['approved']) {
            $this->error("❌ UNEXPECTED APPROVAL: " . $result['reason']);
        } else {
            $this->info("🛡️  SUCCESSFULLY BLOCKED: " . $result['reason']);
            $this->line("   -> System would now inject: 'COMPLIANCE ALERT: " . $result['reason'] . "'");
        }

        $this->line("\n------------------------------------------------");
        $this->info("🚀 SIMULATION COMPLETE");
    }
}
