<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        $isPgsql = $driver === 'pgsql';

        if ($isPgsql) {
            // Fix #5: Add critical performance indexes

            // CRITICAL: business_profiles (user_id) - Used on EVERY agent request
            DB::statement('CREATE INDEX IF NOT EXISTS business_profiles_user_idx ON business_profiles (user_id);');

            // For fetching latest crawl run (used frequently)
            DB::statement('CREATE INDEX IF NOT EXISTS crawl_runs_user_latest_idx ON crawl_runs (user_id, created_at DESC);');

            // Fix #3: Drop duplicate HNSW index (from original migration)
            // Keep only knowledge_vectors_embedding_hnsw_idx (the newer one with explicit params)
            DB::statement('DROP INDEX IF EXISTS knowledge_vectors_embedding_idx;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        $isPgsql = $driver === 'pgsql';

        if ($isPgsql) {
            DB::statement('DROP INDEX IF EXISTS business_profiles_user_idx;');
            DB::statement('DROP INDEX IF EXISTS crawl_runs_user_latest_idx;');

            // Recreate the old index if rolling back
            DB::statement('CREATE INDEX IF NOT EXISTS knowledge_vectors_embedding_idx ON knowledge_vectors USING hnsw (embedding vector_cosine_ops);');
        }
    }
};
