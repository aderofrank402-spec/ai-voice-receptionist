<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ProcessAgent
{
  protected $system_prompt = <<<EOT
You are the "Process Agent" for VoiceAgents.

Your job is to transform a validated Builder Agent interview transcript into a **STRICT, NORMALIZED JSON object**.

Your responsibilities:
1. Extract all relevant business profile information.
2. Normalize formats (prices, phone numbers, hours, capitalization).
3. Produce **strict JSON**—no explanations, commentary, or extra text.
4. Use `null` for any missing or unknown values.

### INPUT:
- A full interview transcript from the Builder Agent.
- The transcript is validated and contains all captured responses (or partial if owner consented).

### OUTPUT SCHEMA (Strict JSON):
{
  "business_name": "string (Title Case, e.g. 'Jamii Auto Garage')",
  "business_type": "string (e.g. 'Car repair garage')",
  "services": [
    {
      "name": "string (Service Name)",
      "price": number (integer, KES, null if unknown),
      "price_type": "fixed|starting_from|quote",
      "description": "string (short summary from transcript context)"
    }
  ],
  "hours": {
    "type": "24/7|regular|appointment_only",
    "schedule": "string (e.g. 'Mon-Fri 08:00-17:00', null if unknown)"
  },
  "missed_call_action": {
    "type": "take_message|forward_call",
    "forward_number": "string (numeric only, e.g. 0712345678) or null",
    "forward_number": "string (numeric only, e.g. 0712345678) or null",
    "emergency_keywords": ["string"]
  },
  "landmark_directions": "string (e.g. 'Opposite Naivas, near Total Station', null if unknown)",
  "raw_summary": "string (2-sentence summary of the business based on transcript)",
  "completion_type": "full|partial"
}

### RULES:
1. **Business Name & Type**: Capitalize properly. Remove obvious typos or gibberish.
2. **Services**: Capture at least the top 3 services discussed. Include short description if available.
3. **Prices**:
   - Convert all to integers (KES).
   - Examples: "KES 1,500" → 1500, "5k" → 5000
   - If unknown → null
4. **Price Type**:
   - "fixed" = exact
   - "starting_from" = minimum price
   - "quote" = deferred/unknown
5. **Hours**: Normalize to 24-hour format (e.g., "8am-6pm" → "08:00-18:00").
6. **Phone Numbers**: Only digits; remove spaces, dashes, symbols.
7. **Missed Call Action**: Extract exactly as captured. Include emergency keywords if mentioned.
8. **Raw Summary**: 2 sentences max; concise overview of business type and core services.
9. **Do NOT invent any information**. Use `null` when data is missing or unknown.
EOT;

  /**
   * Extract normalized blueprint from transcript.
   *
   * @param array $transcript
   * @return array
   */
  public function extractBlueprint(array $transcript): array
  {
    // Flatten transcript for prompt context
    $messages = array_merge(
      [['role' => 'system', 'content' => $this->system_prompt]],
      $transcript
    );

    try {
      $client = \App\Support\HttpClientFactory::openAiClient(timeout: 30);

      $response = $client->chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => $messages,
        'temperature' => 0.1, // Near-zero temp for strict extraction
        'response_format' => ['type' => 'json_object'],
      ]);

      $content = $response->choices[0]->message->content;
      $data = json_decode($content, true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception("Invalid JSON from Process Agent");
      }

      return $data;

    } catch (\Exception $e) {
      Log::error("Process Agent Error: " . $e->getMessage());
      // Return safe fallback
      return [
        'business_name' => 'Error Processing Data',
        'services' => [],
        'hours' => ['type' => 'regular', 'schedule' => '9-5'],
        'missed_call_action' => ['type' => 'take_message'],
        'raw_summary' => 'System error during processing.'
      ];
    }
  }
}
