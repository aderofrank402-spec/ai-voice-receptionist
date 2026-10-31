<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // 1. HNSW Index for Vector Similarity Search
            // This enables fast approximate nearest neighbor search at scale
            DB::statement('
                CREATE INDEX IF NOT EXISTS knowledge_vectors_embedding_hnsw_idx
                ON knowledge_vectors
                USING hnsw (embedding vector_cosine_ops)
                WITH (m = 16, ef_construction = 64)
            ');

            // 2. Composite Index for Filtering by business/crawl
            DB::statement('
                CREATE INDEX IF NOT EXISTS knowledge_vectors_user_crawl_idx
                ON knowledge_vectors (user_id, crawl_version)
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS knowledge_vectors_embedding_hnsw_idx');
            DB::statement('DROP INDEX IF EXISTS knowledge_vectors_user_crawl_idx');
        }
    }
};
