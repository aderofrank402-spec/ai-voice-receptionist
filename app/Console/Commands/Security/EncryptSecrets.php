<?php

namespace App\Console\Commands\Security;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class EncryptSecrets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:encrypt-secrets {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans database for plain-text secrets and encrypts them at rest using raw SQL.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Security Hardening: Secret Encryption Migration (Raw Mode)...');

        if (!$this->option('force') && !$this->confirm('Have you backed up your database? This will directly modify database rows.')) {
            $this->warn('Operation cancelled.');
            return;
        }

        $this->encryptIntegrations();
        $this->encryptSystemCredentials();

        $this->info('Security Hardening Complete.');
    }

    /**
     * Detect and encrypt plain-text integration settings using Raw SQL.
     */
    private function encryptIntegrations()
    {
        $this->comment('Scanning Integrations...');
        $count = 0;

        // Raw query to avoid model casting issues
        $integrations = DB::table('integrations')->get();

        foreach ($integrations as $raw) {
            $isEncrypted = $this->isEncrypted($raw->settings);

            if (!$isEncrypted && !empty($raw->settings)) {
                $this->info("Found unencrypted settings for user ID {$raw->user_id} ({$raw->provider}). Encrypting...");
                
                // Manual Encryption: Laravel 'encrypted:array' cast expects a string 
                // that is the encrypted version of the serialized JSON.
                $encryptedValue = Crypt::encrypt($raw->settings);
                
                DB::table('integrations')
                    ->where('id', $raw->id)
                    ->update(['settings' => $encryptedValue]);
                
                $count++;
            }
        }

        $this->info("Successfully encrypted {$count} integration records.");
    }

    /**
     * Detect and encrypt plain-text system credentials using Raw SQL.
     */
    private function encryptSystemCredentials()
    {
        $this->comment('Scanning System Credentials...');
        $count = 0;

        $credentials = DB::table('system_credentials')->get();

        foreach ($credentials as $raw) {
            $isEncrypted = $this->isEncrypted($raw->value);

            if (!$isEncrypted && $raw->value !== 'placeholder' && !empty($raw->value)) {
                $this->info("Found unencrypted credential: {$raw->name}. Encrypting...");
                
                $encryptedValue = Crypt::encrypt($raw->value);
                
                DB::table('system_credentials')
                    ->where('id', $raw->id)
                    ->update(['value' => $encryptedValue]);
                
                $count++;
            }
        }

        $this->info("Successfully encrypted {$count} system credential records.");
    }

    /**
     * Determine if a string is a Laravel encrypted payload.
     */
    private function isEncrypted($value)
    {
        if (empty($value)) return true;
        return str_starts_with((string)$value, 'eyJpdiI6');
    }
}
