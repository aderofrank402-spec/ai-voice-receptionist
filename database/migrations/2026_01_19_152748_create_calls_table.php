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
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('call_id')->unique();
            $table->string('caller_number')->nullable();
            $table->string('status')->default('in_progress'); // in_progress, completed, failed
            $table->integer('duration')->default(0); // seconds
            $table->text('transcript')->nullable();
            $table->string('outcome')->nullable(); // appointment_booked, information_provided, etc.
            $table->json('metadata')->nullable(); // Additional call data
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
