<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Business Name
            $table->json('configuration'); // Structured Data (Hours, Services, prices)
            $table->text('system_prompt'); // The Compiled Golden Prompt
            $table->string('status')->default('active'); // active, inactive, suspended
            $table->string('phone_number')->nullable(); // Assigned Twilio Number
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
