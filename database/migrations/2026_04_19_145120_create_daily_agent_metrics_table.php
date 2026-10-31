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
        Schema::create('daily_agent_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->date('date');
            
            // Metrics (O(1) aggregators)
            $table->integer('total_calls')->default(0);
            $table->integer('total_seconds')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('after_hours_count')->default(0);
            
            $table->timestamps();

            // Ensure we only have one row per agent per day
            $table->unique(['agent_id', 'date']);
            
            // Indexes for range queries (7 days, 30 days)
            $table->index(['agent_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_agent_metrics');
    }
};
