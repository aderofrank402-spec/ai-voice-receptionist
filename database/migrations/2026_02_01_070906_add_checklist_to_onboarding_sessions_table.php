<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('onboarding_sessions', function (Blueprint $table) {
            $table->json('checklist')->nullable()->after('transcript');
        });
    }

    public function down(): void
    {
        Schema::table('onboarding_sessions', function (Blueprint $table) {
            $table->dropColumn('checklist');
        });
    }
};
