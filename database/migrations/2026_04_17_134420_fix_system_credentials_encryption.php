<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $credentials = DB::table('system_credentials')->get();

        foreach ($credentials as $cred) {
            $value = $cred->value;
            
            // Laravel's encryption engine uses a JSON structure containing 'iv' and 'mac'.
            // If the string doesn't look like an encrypted payload, it's plaintext.
            $isPlaintext = !str_contains($value, '"iv":') || !str_contains($value, '"mac":');

            if ($isPlaintext) {
                \Illuminate\Support\Facades\Log::info("Repairing credential encryption for: {$cred->name}");
                
                DB::table('system_credentials')
                    ->where('id', $cred->id)
                    ->update([
                        'value' => Crypt::encryptString($value),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data-only migrations usually don't need a down method unless we want to roll back to plaintext,
        // which is not desirable here.
    }
};
