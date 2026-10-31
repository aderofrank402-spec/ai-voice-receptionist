<?php

namespace App\Jobs;

use App\Models\BusinessProfile;
use App\Models\CrawlRun;
use App\Models\CrawledPage;
use App\Models\KnowledgeVector;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class ProcessCrawlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public CrawlRun $crawlRun;

    public function __construct(CrawlRun $crawlRun)
    {
        $this->crawlRun = $crawlRun;
    }

    public function handle(): void
    {
        Log::info("Refinery: Processing crawl run {$this->crawlRun->crawl_version} ({$this->crawlRun->pages_crawled} pages)");

        // Load all pages from this crawl
        $pages = $this->crawlRun->pages;

        if ($pages->isEmpty()) {
            Log::warning("Refinery: No pages found for crawl run {$this->crawlRun->id}");
            $this->crawlRun->update([
                'status' => 'failed',
                'error_message' => 'No pages found to process',
                'finished_at' => now(),
            ]);
            return;
        }

        try {
            // ========================================
            // PHASE 1: HYBRID FACT EXTRACTION
            // ========================================
            $this->crawlRun->update(['status' => 'extracting']);
            $profile = $this->extractBusinessFacts($pages);
            $this->crawlRun->update(['facts_extracted_at' => now()]);

            // ========================================
            // PHASE 2: BATCH EMBEDDING
            // ========================================
            $this->crawlRun->update(['status' => 'embedding']);
            $this->generateEmbeddings($pages);
            $this->crawlRun->update(['embedded_at' => now()]);

            // ========================================
            // PHASE 3: ATOMIC ACTIVATION (P0 FIX)
            // ========================================
            $this->crawlRun->update(['status' => 'activating']);

            // ONLY set active version after BOTH phases complete
            $oldVersion = $profile->active_crawl_version;

            $profile->update([
                'active_crawl_version' => $this->crawlRun->crawl_version,
            ]);

            // Refresh profile to get updated active_crawl_version
            $profile->refresh();

            // ========================================
            // CACHE POPULATION (After Activation)
            // ========================================
            $cacheKey = "biz:{$this->crawlRun->user_id}:profile:v{$this->crawlRun->crawl_version}";
            Cache::put($cacheKey, $profile->toArray(), 86400);

            // ========================================
            // PHASE 4: CACHE CLEANUP (P0 FIX)
            // ========================================
            if ($oldVersion && $oldVersion !== $this->crawlRun->crawl_version) {
                // Delete old profile cache
                Cache::forget("biz:{$this->crawlRun->user_id}:profile:v{$oldVersion}");

                // FIX #1: Delete old RAG caches too (prevent memory leak)
                $this->deleteOldRagCaches($this->crawlRun->user_id, $oldVersion);

                Log::info("Refinery: Cleaned up old cache for version {$oldVersion}");
            }

            // Mark as completed with activation timestamp
            $this->crawlRun->update([
                'status' => 'completed',
                'activated_at' => now(),
                'finished_at' => now(),
            ]);

            Log::info("Refinery: Activated version {$this->crawlRun->crawl_version} successfully");

        } catch (\Exception $e) {
            // If ANY phase fails, keep previous version active
            Log::error("Refinery: Processing failed, keeping previous crawl active: " . $e->getMessage());

            // Update status to failed with error message
            $this->crawlRun->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            throw $e; // Re-throw for queue retry
        }
    }

    /**
     * Phase 1: Extract business facts using HYBRID approach
     * 1. Regex first (phone, email, links) - FREE & DETERMINISTIC
     * 2. LLM fallback for ambiguous data - COSTLY BUT ACCURATE
     */
    protected function extractBusinessFacts($pages): BusinessProfile
    {
        // SMART PAGE SELECTION (P1 FIX)
        // Instead of aggregating ALL pages, select only relevant ones
        $contactPages = $pages->filter(function ($page) {
            $url = strtolower($page->url);
            $title = strtolower($page->title ?? '');
            return str_contains($url, 'contact') || str_contains($title, 'contact');
        });

        $servicesPages = $pages->filter(function ($page) {
            $url = strtolower($page->url);
            $title = strtolower($page->title ?? '');
            return str_contains($url, 'service') || str_contains($title, 'service');
        });

        $aboutPages = $pages->filter(function ($page) {
            $url = strtolower($page->url);
            $title = strtolower($page->title ?? '');
            return str_contains($url, 'about') || str_contains($title, 'about');
        });

        // Combine relevant pages (not all pages!)
        $relevantPages = $contactPages->merge($servicesPages)->merge($aboutPages);

        // FIX #4: Improved fallback logic
        if ($relevantPages->isEmpty()) {
            // Fallback 1: Use page_type_guess from crawler metadata
            $relevantPages = $pages->whereIn('page_type_guess', ['contact', 'services', 'about']);
        }

        if ($relevantPages->isEmpty()) {
            // Fallback 2: Use homepage (shortest URL, usually root)
            $homepage = $pages->sortBy(fn($p) => strlen($p->url))->first();
            if ($homepage) {
                $relevantPages = collect([$homepage]);
            }
        }

        if ($relevantPages->isEmpty()) {
            // Final fallback: Use first 3 pages by word count
            $relevantPages = $pages->sortByDesc(fn($p) => str_word_count($p->raw_text ?? ''))->take(3);
        }

        $aggregatedText = $relevantPages->pluck('raw_text')->join("\n\n");

        // Step 1: DETERMINISTIC EXTRACTION (Regex)
        $phone = $this->extractPhone($aggregatedText);
        $email = $this->extractEmail($aggregatedText);

        // Step 2: LLM EXTRACTION (Only for missing/ambiguous data)
        $llmData = $this->extractWithLLM($aggregatedText, [
            'phone_found' => !empty($phone),
            'email_found' => !empty($email),
        ]);

        // Step 3: CONFIDENCE SCORING (Multi-signal)
        $confidence = $this->calculateConfidence([
            'phone' => $phone,
            'email' => $email,
            'llm_data' => $llmData,
            'pages_count' => $pages->count(),
        ]);

        // Step 4: SAVE TO BUSINESS PROFILE (Don't set active_crawl_version yet!)
        $profile = BusinessProfile::updateOrCreate(
            ['user_id' => $this->crawlRun->user_id],
            [
                // NOTE: active_crawl_version is set AFTER embeddings complete (atomic activation)
                'business_name' => $llmData['business_name'] ?? null,
                'location' => $llmData['location'] ?? null,
                'hours_raw' => $llmData['hours_raw'] ?? null,
                'hours_structured' => $llmData['hours_structured'] ?? [],
                'contacts' => [
                    'phone' => $phone ?? $llmData['phone'] ?? null,
                    'email' => $email ?? $llmData['email'] ?? null,
                ],
                'services' => $llmData['services'] ?? [],
                'confidence_score' => $confidence,
                'needs_review' => $confidence < 0.7,
            ]
        );

        // NOTE: Cache is populated AFTER activation (in handle method)
        // This ensures cache and active_crawl_version are always in sync

        Log::info("Refinery: Saved business profile (confidence: {$confidence}, not activated yet)");

        return $profile;
    }

    /**
     * Extract phone number using regex (Kenyan format priority)
     */
    protected function extractPhone(string $text): ?string
    {
        // Pattern 1: +254 format
        if (preg_match('/\+254[0-9]{9}/', $text, $matches)) {
            return $matches[0];
        }

        // Pattern 2: 07xx or 01xx format
        if (preg_match('/\b0[17][0-9]{8}\b/', $text, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * Extract email using regex
     */
    protected function extractEmail(string $text): ?string
    {
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
            return $matches[0];
        }

        return null;
    }

    /**
     * LLM extraction for ambiguous data (services, hours, business name)
     */
    protected function extractWithLLM(string $text, array $context): array
    {
        try {
            $prompt = "Extract business information from the following text.\n\n";

            if (!$context['phone_found']) {
                $prompt .= "- Find the phone number\n";
            }
            if (!$context['email_found']) {
                $prompt .= "- Find the email address\n";
            }

            $prompt .= "- Extract business name\n";
            $prompt .= "- Extract location/address\n";
            $prompt .= "- Extract operating hours\n";
            $prompt .= "- Extract list of services with prices\n\n";
            $prompt .= "Text:\n" . substr($text, 0, 3000);
            $prompt .= "\n\nReturn JSON only. Format: {\"business_name\": \"\", \"phone\": \"\", \"email\": \"\", \"location\": \"\", \"hours_raw\": \"\", \"services\": [{\"name\": \"\", \"price\": \"\"}]}";

            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini', // Using mini instead of GPT-4 for cost savings
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.1,
            ]);

            $content = $response->choices[0]->message->content;
            return json_decode($content, true) ?? [];

        } catch (\Exception $e) {
            Log::error("Refinery: LLM extraction failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Multi-signal confidence scoring
     */
    protected function calculateConfidence(array $signals): float
    {
        $score = 0;
        $maxScore = 0;

        // Phone found via regex
        if (!empty($signals['phone'])) {
            $score += 0.3;
        }
        $maxScore += 0.3;

        // Email found via regex
        if (!empty($signals['email'])) {
            $score += 0.2;
        }
        $maxScore += 0.2;

        // Business name found
        if (!empty($signals['llm_data']['business_name'])) {
            $score += 0.2;
        }
        $maxScore += 0.2;

        // Services found
        if (!empty($signals['llm_data']['services'])) {
            $score += 0.15;
        }
        $maxScore += 0.15;

        // Multiple pages crawled (more data = higher confidence)
        if ($signals['pages_count'] >= 5) {
            $score += 0.15;
        } elseif ($signals['pages_count'] >= 3) {
            $score += 0.1;
        }
        $maxScore += 0.15;

        return round($score / $maxScore, 2);
    }

    /**
     * Phase 2: Generate embeddings using BATCH API (no sleep!)
     */
    protected function generateEmbeddings($pages): void
    {
        $chunks = [];
        $chunkMeta = [];

        // Collect all chunks from all pages
        foreach ($pages as $page) {
            $text = $page->raw_text ?? '';
            if (strlen($text) < 50) {
                continue; // Skip short pages
            }

            $pageChunks = $this->chunkText($text, 500);
            foreach ($pageChunks as $index => $chunk) {
                $chunks[] = $chunk;
                $chunkMeta[] = [
                    'page_id' => $page->id,
                    'page_url' => $page->url,
                    'chunk_index' => $index,
                ];
            }
        }

        if (empty($chunks)) {
            Log::info("Refinery: No chunks to embed");
            return;
        }

        Log::info("Refinery: Embedding " . count($chunks) . " chunks via batch API...");

        try {
            // BATCH EMBEDDING (One API call for all chunks!)
            $response = OpenAI::embeddings()->create([
                'model' => 'text-embedding-3-small',
                'input' => $chunks, // Array of strings
            ]);

            // Save vectors to database
            foreach ($response->embeddings as $i => $embedding) {
                KnowledgeVector::create([
                    'user_id' => $this->crawlRun->user_id,
                    'crawl_version' => $this->crawlRun->crawl_version,
                    'chunk_text' => $chunks[$i],
                    'embedding' => $embedding->embedding, // Array of floats
                    'source_url' => $chunkMeta[$i]['page_url'], // ✅ ENABLES CITATIONS
                    'metadata' => [
                        'crawled_page_id' => $chunkMeta[$i]['page_id'],
                        'chunk_index' => $chunkMeta[$i]['chunk_index'],
                    ],
                ]);
            }

            Log::info("Refinery: Successfully embedded and saved " . count($chunks) . " chunks");

        } catch (\Exception $e) {
            Log::error("Refinery: Batch embedding failed: " . $e->getMessage());
        }
    }

    /**
     * Chunk text into smaller pieces
     */
    protected function chunkText(string $text, int $chunkSize = 500): array
    {
        $text = trim($text);
        if (strlen($text) < $chunkSize) {
            return [$text];
        }

        $chunks = [];
        $words = explode(' ', $text);
        $currentChunk = '';

        foreach ($words as $word) {
            if (strlen($currentChunk . ' ' . $word) > $chunkSize && !empty($currentChunk)) {
                $chunks[] = trim($currentChunk);
                $currentChunk = $word;
            } else {
                $currentChunk .= ' ' . $word;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }

        return $chunks;
    }

    /**
     * Delete old RAG caches for a specific user and crawl version
     * FIX #1: Prevent Redis memory leak from accumulated RAG caches
     */
    protected function deleteOldRagCaches(int $userId, string $oldVersion): void
    {
        // Use Redis SCAN (non-blocking) to find and delete old cache keys
        $pattern = "rag:{$userId}:v{$oldVersion}:*";
        $cursor = 0;
        $deletedCount = 0;

        $startTime = microtime(true);

        try {
            do {
                // SCAN is safe for production - doesn't block Redis like KEYS
                [$cursor, $keys] = \Illuminate\Support\Facades\Redis::scan($cursor, [
                    'match' => $pattern,
                    'count' => 500
                ]);

                if (!empty($keys)) {
                    // Ensure flat array for UNLINK command
                    $keys = array_values($keys);

                    // Use UNLINK (async) instead of DEL (sync) to prevent latency spikes
                    // UNLINK deletes asynchronously in background, safe for production
                    try {
                        \Illuminate\Support\Facades\Redis::command('UNLINK', $keys);
                        $deletedCount += count($keys);
                    } catch (\Throwable $unlinkError) {
                        // Fallback to DEL if UNLINK not available (very old Redis)
                        \Illuminate\Support\Facades\Redis::del($keys);
                        $deletedCount += count($keys);
                    }
                }
            } while ($cursor != 0);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            if ($deletedCount > 0) {
                Log::info("Refinery: Deleted {$deletedCount} old RAG cache keys for version {$oldVersion} in {$duration}ms");
            }

        } catch (\Exception $e) {
            // Don't fail the job if cache cleanup fails
            Log::warning("Refinery: Failed to cleanup old RAG caches: " . $e->getMessage());
        }
    }
}
