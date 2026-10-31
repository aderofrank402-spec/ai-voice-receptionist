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
        // Intentionally empty. Development users belong in an explicit seeder,
        // never in a schema migration that may run in production.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No data was created.
    }
};
