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
        Schema::table('crawl_runs', function (Blueprint $table) {
            // Update status to be more granular for pipeline tracking
            // Note: We'll update existing 'status' column in a separate statement

            // Error tracking
            $table->text('error_message')->nullable()->after('status');

            // Pipeline phase timestamps (for debugging stuck crawls)
            $table->timestamp('facts_extracted_at')->nullable()->after('started_at');
            $table->timestamp('embedded_at')->nullable()->after('facts_extracted_at');
            $table->timestamp('activated_at')->nullable()->after('embedded_at');
        });

        // Note: Existing status column already exists as string
        // Valid values will be: 'queued', 'crawling', 'extracting', 'embedding', 'activating', 'completed', 'failed'
        // No need to change column type, string is flexible enough
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crawl_runs', function (Blueprint $table) {
            $table->dropColumn(['error_message', 'facts_extracted_at', 'embedded_at', 'activated_at']);
        });
    }
};
