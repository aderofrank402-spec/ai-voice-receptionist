<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add cost column to calls table.
     *
     * The voice-engine sends `cost` (decimal, KES equivalent of API usage)
     * in its endCall() payload. Without this column the value was silently dropped.
     *
     * Cost is calculated in VoiceOrchestrator.endCall():
     *   cost = (durationSeconds / 60) * 0.16   (~$0.16/min blended rate)
     */
    public function up(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            // Stored as a decimal with 6 decimal places (e.g. 0.053333 KES)
            // nullable so old records without cost are unaffected
            $table->decimal('cost', 10, 6)->nullable()->default(null)->after('duration');
        });
    }

    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn('cost');
        });
    }
};
