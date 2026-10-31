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
        Schema::create('system_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., openai_api_key
            $table->text('value'); // Encrypted
            $table->string('provider'); // e.g., openai, elevenlabs, groq, deepgram, zadarma
            $table->boolean('is_active')->default(true);
            $table->boolean('is_valid')->default(false);
            $table->timestamp('last_tested_at')->nullable();
            $table->timestamps();
        });

        // Seed initial placeholder keys
        $providers = [
            'openai' => ['openai_api_key'],
            'groq' => ['groq_api_key'],
            'elevenlabs' => ['elevenlabs_api_key'],
            'deepgram' => ['deepgram_api_key'],
            'zadarma' => ['zadarma_key', 'zadarma_secret'],
        ];

        foreach ($providers as $provider => $keys) {
            foreach ($keys as $key) {
                \DB::table('system_credentials')->insert([
                    'name' => $key,
                    'value' => \Illuminate\Support\Facades\Crypt::encryptString('placeholder'), // Encrypted seed
                    'provider' => $provider,
                    'created_at' => now(),
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
        Schema::dropIfExists('system_credentials');
    }
};
