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
        Schema::table('calls', function (Blueprint $table) {
            // Index for dashboard metrics by agent and date
            $table->index(['agent_id', 'created_at'], 'calls_agent_created_idx');
            // Index for status breakdowns by date
            $table->index(['created_at', 'status'], 'calls_created_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropIndex('calls_agent_created_idx');
            $table->dropIndex('calls_created_status_idx');
        });
    }
};
