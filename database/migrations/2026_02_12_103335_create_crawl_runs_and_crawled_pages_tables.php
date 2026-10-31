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
        // Parent table: One record per crawl session
        Schema::create('crawl_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->uuid('crawl_version')->unique(); // Same as before
            $table->string('start_url'); // The URL user submitted
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->integer('pages_found')->default(0);
            $table->integer('pages_crawled')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('crawl_version');
        });

        // Child table: One record per page crawled
        Schema::create('crawled_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crawl_run_id')->constrained('crawl_runs')->onDelete('cascade');
            $table->string('url'); // Full URL of THIS page
            $table->string('title')->nullable();
            $table->integer('http_status')->default(200);
            $table->string('content_hash', 64)->nullable(); // MD5 or SHA256 of text
            $table->text('raw_text')->nullable(); // Extracted text
            $table->longText('raw_html')->nullable(); // Optional: full HTML
            $table->string('page_type_guess')->nullable(); // 'homepage', 'services', 'contact', etc.
            $table->decimal('confidence_score', 3, 2)->default(0.50);
            $table->json('metadata')->nullable(); // Additional crawler metadata
            $table->timestamps();

            $table->index(['crawl_run_id', 'url']);
            $table->index(['crawl_run_id', 'page_type_guess']);
            $table->index('content_hash'); // For change detection
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crawled_pages');
        Schema::dropIfExists('crawl_runs');
    }
};
