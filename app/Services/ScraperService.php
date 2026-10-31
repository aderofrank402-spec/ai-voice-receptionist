<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class ScraperService
{
    public function __construct()
    {
    }

    /**
     * Parses raw text content using an LLM to extract structured business data.
     * This is used by ScrapeWebsiteJob after the Docker crawler finishes.
     *
     * @param string $text
     * @return array
     */
    public function parseWithLLM(string $text): array
    {
        try {
            $client = \App\Support\HttpClientFactory::openAiClient(timeout: 90);

            $prompt = <<<EOT
You are a Deep Scraper. Analyze the text from multiple pages of a website.
CONTEXT: This text has been "cleaned" – navigation bars, footers, and ads have been removed. 
You are receiving ONLY the body content.

Text:
"""
$text
"""

INSTRUCTION: 
1. Identify the business.
2. If details (like services or hours) are MISSING in the text, DO NOT HALLUCINATE them. Return null or empty array for that field.
3. If the text seems to describe a "Restaurant" but only contains generic words like "Home", "Menu", "Contact" (without food items), it is a FALSE POSITIVE. Ignore it.

Extract JSON:
1. "operating_hours": Consolidated.
2. "services": List of name + price.
3. "landmark_directions": Location clues.
4. "payment_methods": M-Pesa, etc.
5. "social_links": URLs.
6. "crawled_summary": Brief business summary.

Return JSON ONLY.
EOT;

            $response = $client->chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a JSON extractor.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0,
                'response_format' => ['type' => 'json_object']
            ]);

            return json_decode($response->choices[0]->message->content, true) ?? [];
        } catch (\Exception $e) {
            Log::warning("Scraper LLM Error: " . $e->getMessage());
            return [];
        }
    }
}
