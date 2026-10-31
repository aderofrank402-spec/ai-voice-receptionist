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
            $table->string('recording_url')->after('transcript')->nullable();
            $table->integer('lead_score')->after('recording_url')->nullable(); // 1-5 scale
            $table->string('primary_intent')->after('lead_score')->nullable(); // booking, inquiry, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn(['recording_url', 'lead_score', 'primary_intent']);
        });
    }
};
