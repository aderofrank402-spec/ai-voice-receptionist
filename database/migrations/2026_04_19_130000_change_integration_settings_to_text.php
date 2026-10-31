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
        Schema::table('integrations', function (Blueprint $table) {
            // Postgres requires a manual cast when changing from JSON to TEXT 
            // if there is existing data, but Laravel's change() usually handles it.
            // We use change() but we might need a raw query if it fails.
            $table->text('settings')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->json('settings')->nullable()->change();
        });
    }
};
