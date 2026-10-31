<?php

namespace App\Services;

use App\Models\BusinessProfile;
use App\Models\KnowledgeVector;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;

class AgentContextService
{
    /**
     * Get the FULL System Prompt for a given business including Persona and Context.
     */
    public function getSystemPrompt(int $userId, ?string $userQuery = null): string
    {
        // 1. Fetch Structured Profile (Database)
        $profile = BusinessProfile::where('user_id', $userId)
            ->latest()
            ->first();

        if (!$profile) {
            return "CONTEXT: No business profile found. Please complete onboarding first.";
        }

        // --- PART A: FACTS FORMATTING ---
        $businessName = $profile->business_name ?? 'Business';
        $location = $profile->location ? "We are located at {$profile->location}." : "Location provided upon request.";

        // Services
        $servicesText = "";
        $services = $profile->services ?? [];
        if (!empty($services) && is_array($services)) {
            // Limit to top 5 services for the prompt
            foreach (array_slice($services, 0, 5) as $svc) {
                $name = $svc['name'] ?? 'Service';
                $price = $svc['price'] ?? 'Price Varies';
                $servicesText .= "- $name: $price\n";
            }
        } else {
            $servicesText = "Services available upon request.";
        }

        // Hours
        $hoursText = $profile->hours_raw ?? "Please contact us for opening hours.";

        // Contacts
        $phone = $profile->contacts['phone'] ?? null;
        $contactAction = $phone
            ? "offer to have someone call you back or you can reach us at {$phone}."
            : "offer to take a message.";

        // --- PART B: RAG CONTEXT (Knowledge Graph) ---
        $ragContext = "";
        if ($userQuery) {
            $ragContext = "\n### RELEVANT KNOWLEDGE BASE:\n";
            // Check Cache with crawl version
            $queryHash = md5(strtolower(trim($userQuery)));
            $cacheKey = "rag:{$userId}:v{$profile->active_crawl_version}:{$queryHash}";


            try {
                // 1. Compute Query Embedding - Hardened with 5s timeout
                $client = \App\Support\HttpClientFactory::openAiClient(timeout: 5);

                $response = $client->embeddings()->create([
                    'model' => 'text-embedding-3-small',
                    'input' => $userQuery,
                ]);

                $queryVector = $response->embeddings[0]->embedding;
                // Format for Postgres vector syntax if needed, but Laravel PDO often handles array binding for certain drivers.
                // However, with standard PDO and pgvector, valid string format is '[1,2,3...]'
                $vectorString = '[' . implode(',', $queryVector) . ']';

                // 2. Vector Search (Simulate for now if SQLite, attempting raw query)
                // Note: SQLite will fail on vector syntax, so we wrap in try-catch or check driver.
                // But user wants RAG enabled. We assume Postgres is target or we fail gracefully.

                $results = KnowledgeVector::query()
                    ->selectRaw("chunk_text, source_url")
                    ->where('user_id', $userId)
                    ->where('crawl_version', $profile->active_crawl_version) // Only latest crawl
                    ->orderByRaw("embedding <=> ? ASC", [$vectorString])
                    ->limit(3)
                    ->get();

                if ($results->isNotEmpty()) {
                    foreach ($results as $result) {
                        $ragContext .= "Source: {$result->source_url}\nContent: {$result->chunk_text}\n---\n";
                    }
                } else {
                    $ragContext .= "(No relevant knowledge found.)";
                }

            } catch (\Exception $e) {
                // Fallback for when OpenAI fails or Driver doesn't support vector ops (e.g. SQLite local dev)
                // In local dev without Postgres, this catches the error so app doesn't crash.
                $ragContext .= "(Knowledge retrieval unavailable: " . $e->getMessage() . ")";
            }
        }

        // --- PART C: PERSONA TEMPLATE ---
        return <<<EOT
You are the AI Receptionist for "{$businessName}".
Your Voice: Professional, Warm, Kenyan English accent.

### BUSINESS CONTEXT
Name: {$businessName}
Location: {$location}
Operating Hours: {$hoursText}

### CORE SERVICES & PRICING
{$servicesText}

### DYNAMIC KNOWLEDGE
{$ragContext}

### KEY INSTRUCTIONS
1. **VOICE OPTIMIZED**: You are on a PHONE CALL.
   - Keep answers SHORT (1-2 sentences).
   - NEVER use markdown (*, #, -) in speech (except internally).
   - Speak naturally.

2. **HANDLING INQUIRIES**:
   - Answer questions using the data above.
   - If questioned about something not listed, say: "I don't have that info right now, but I can check."

3. **CALL FLOW & ACTIONS**:
   - If the user wants to book or speak to a human, {$contactAction}
   - If they simply say "Hello", greet them warmly as {$businessName}.

### GOAL
Assist the caller efficiently. Do not hallucinate services we do not offer.
EOT;
    }
}
