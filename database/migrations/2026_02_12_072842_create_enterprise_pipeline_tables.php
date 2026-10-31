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
        $isPgsql = $driver === 'pgsql';

        // 1. Enable pgvector extension for embeddings (Postgres only)
        if ($isPgsql) {
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector;');
        }

        // 2. The Lake (Raw Ingestion)
        Schema::create('crawled_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // owner
            $table->string('session_id')->nullable()->index(); // links to onboarding
            $table->string('crawl_version')->index(); // UUID per runs
            $table->string('url')->index(); // The source URL
            $table->string('title')->nullable();

            // Raw Data
            $table->longText('raw_text_dump')->nullable();
            $table->longText('raw_html')->nullable();

            // Metadata & Classification
            $table->string('page_type_guess')->nullable(); // about, contact, etc.
            $table->json('metadata')->nullable(); // og:image, meta tags

            $table->timestamps();
        });

        // 3. The Refinery Output A: Structured Business Profile (Facts)
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('active_crawl_version')->nullable(); // Pointer to source

            // Core Facts
            $table->string('business_name')->nullable();
            $table->text('location')->nullable();
            $table->text('hours_raw')->nullable();
            $table->json('hours_structured')->nullable(); // Normalized
            $table->json('contacts')->nullable(); // Phones, Emails
            $table->json('services')->nullable(); // [{name, price}]
            $table->string('booking_link')->nullable();

            // Validation
            $table->decimal('confidence_score', 3, 2)->default(0.0); // 0.00 to 1.00
            $table->boolean('needs_review')->default(false);

            $table->timestamps();
        });

        // 4. The Refinery Output B: Context (RAG Vault)
        Schema::create('knowledge_vectors', function (Blueprint $table) use ($isPgsql) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('crawl_version')->index();

            // Content
            $table->text('chunk_text');

            // Vector needs special handling
            if ($isPgsql) {
                $table->vector('embedding', 1536); // OpenAI text-embedding-3-small dimension
            } else {
                $table->json('embedding')->nullable(); // Fallback for SQLite/MySQL
            }

            // Metadata for Retrieval
            $table->string('source_url');
            $table->string('page_type')->nullable(); // about, faq, policy
            $table->string('section_heading')->nullable();
            $table->json('metadata')->nullable(); // extra citations

            $table->timestamps();
        });

        // HNSW Index for fast vector search (Postgres Only)
        if ($isPgsql) {
            DB::statement('CREATE INDEX knowledge_vectors_embedding_idx ON knowledge_vectors USING hnsw (embedding vector_cosine_ops);');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_vectors');
        Schema::dropIfExists('business_profiles');
        Schema::dropIfExists('crawled_contents');
    }
};
