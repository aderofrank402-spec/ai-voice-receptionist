<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $credentials = [
            // Twilio (WhatsApp)
            ['name' => 'twilio_sid', 'value' => 'placeholder', 'provider' => 'twilio', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'twilio_token', 'value' => 'placeholder', 'provider' => 'twilio', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'twilio_from', 'value' => 'placeholder', 'provider' => 'twilio', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // Africa's Talking (SMS)
            ['name' => 'at_username', 'value' => 'placeholder', 'provider' => 'africas_talking', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'at_api_key', 'value' => 'placeholder', 'provider' => 'africas_talking', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // Google Maps
            ['name' => 'google_maps_api_key', 'value' => 'placeholder', 'provider' => 'google_maps', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($credentials as $cred) {
            $cred['value'] = \Illuminate\Support\Facades\Crypt::encryptString($cred['value']);
            \Illuminate\Support\Facades\DB::table('system_credentials')->updateOrInsert(
                ['name' => $cred['name']],
                $cred
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('system_credentials')
            ->whereIn('provider', ['twilio', 'africas_talking', 'google_maps'])
            ->delete();
    }
};
