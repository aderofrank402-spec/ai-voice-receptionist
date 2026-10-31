<?php

namespace App\Services;

class PromptGeneratorAgent
{
    /**
     * Generate the final System Prompt from the normalized data.
     *
     * @param array $data The strict JSON from ProcessAgent
     * @return string The text prompt
     */
    public function generate(array $data): string
    {
        $businessName = $data['business_name'] ?? 'The Business';
        $businessType = $data['business_type'] ?? 'Business';
        $rawSummary = $data['raw_summary'] ?? 'A professional service provider.';

        // ── Google Maps: Practical Navigation Injection ────────────────────
        $googleMapsAddress = null;
        $mapsIntegration = \App\Models\Integration::where('user_id', $data['user_id'] ?? 0)
            ->where('provider', 'google_maps')
            ->first();

        if ($mapsIntegration && !empty($mapsIntegration->settings['place_id'])) {
            try {
                $locationService = app(\App\Services\LocationService::class);
                $details = $locationService->getPlaceDetails($mapsIntegration->settings['place_id']);
                if (isset($details['formatted_address'])) {
                    $googleMapsAddress = $details['formatted_address'];
                }
            } catch (\Exception $e) {
                \Log::warning("Maps prompt injection failed: " . $e->getMessage());
            }
        }

        // ── Location Logic ──────────────────────────────────────────────
        $landmark = $data['landmark_directions'] ?? null;
        $landmarkText = "Location: " . ($googleMapsAddress ?? ($landmark ? "near {$landmark}" : "provided upon request"));

        // Persona Extraction
        $persona = $data['persona'] ?? [];
        $agentName = $persona['agent_name'] ?? 'the Assistant';
        $tone = $persona['tone'] ?? 'polite_admin';
        $language = $persona['language'] ?? 'en';

        // 1. Compile Services List (Limit to top 3)
        $servicesText = "";
        $servicesList = array_slice($data['services'] ?? [], 0, 3);

        if (!empty($servicesList)) {
            foreach ($servicesList as $svc) {
                $priceVal = $svc['price'] ?? 0;
                if ($priceVal <= 0 || ($svc['price_type'] ?? '') === 'quote') {
                    $priceStr = "Price upon request";
                } else {
                    $prefix = ($svc['price_type'] ?? '') === 'starting_from' ? "starts from " : "";
                    $priceStr = $prefix . "KES " . number_format($priceVal);
                }
                $servicesText .= "- {$svc['name']}: {$priceStr}. " . ($svc['description'] ?? '') . "\n";
            }
        }

        // 2. Compile Hours (Natural Speech)
        $hoursData = $data['hours'] ?? [];
        if (($hoursData['type'] ?? '') === '24/7') {
            $hoursText = "We are open 24 hours a day, 7 days a week.";
        } elseif (($hoursData['type'] ?? '') === 'appointment_only') {
            $hoursText = "We operate strictly by appointment.";
        } else {
            $schedule = $hoursData['schedule'] ?? 'Not specified';
            $hoursText = $this->humanizeHours($schedule);
        }

        // 3. Define Persona Instructions
        $toneInstructions = "";
        $greeting = "";

        if ($tone === 'direct_expert') {
            $toneInstructions = "TONE: Clear, concise, and task-oriented. Be professional but very direct. Avoid unnecessary floral language.";
            $greeting = ($language === 'sw') ? "Jambo, mimi ni {$agentName}. Nikusaidie vipi?" : "Hi, I'm {$agentName}. How can I help you today?";
        } else {
            // Default: Polite Admin
            $toneInstructions = "TONE: Warm, highly polite, and formal. Use helpful phrasing like 'It would be my pleasure' or 'Certainly'.";
            $greeting = ($language === 'sw') ? "Habari yako, karibu {$businessName}. Mimi ni {$agentName}, nitakusaidia vipi leo?" : "Good day, welcome to {$businessName}. My name is {$agentName}, how may I assist you today?";
        }

        if ($language === 'sheng') {
            $greeting = "Sasa! Mimi ni {$agentName} kutoka {$businessName}. Unadai nini leo?";
            $toneInstructions .= " Use casual Kenyan Sheng naturally but keep it professional. Use words like 'Sasa', 'Poa', 'Inakuaje'.";
        }

        // 4. Missed Call Action
        $missedCall = $data['missed_call_action'] ?? [];
        if (($missedCall['type'] ?? '') === 'forward_call') {
            $number = $missedCall['forward_number'] ?? 'the line';
            $actionInstruction = "IMMEDIATE ACTION: If the user asks for a person, say: 'Let me transfer you right away.' and trigger 'transfer_call' (Target: {$number}).";
        } else {
            $actionInstruction = "IMMEDIATE ACTION: If requested, say: 'I can take your message and have the owner call you back.' and trigger 'save_message'.";
        }

        // 5. Supplementary Knowledge (RAG Integration)
        $knowledgeText = "";
        $knowledgeSources = $data['knowledge'] ?? [];
        if (!empty($knowledgeSources)) {
            $knowledgeText .= "### SUPPLEMENTARY KNOWLEDGE (PRIORITIZE THIS):\n";
            foreach ($knowledgeSources as $source) {
                $knowledgeText .= "- [" . ($source['title'] ?? 'Info') . "]: " . ($source['content'] ?? '') . "\n";
            }
            $knowledgeText .= "\nInstruction: Use the knowledge provided above as your primary source of truth. If a user's question contradicts this knowledge, follow this knowledge.\n";
        }

        return <<<EOT
You are {$agentName}, the AI Voice Assistant for "{$businessName}" ({$businessType}).
{$toneInstructions}

### GREETING
Always start the call exactly with: "{$greeting}"

### BUSINESS CONTEXT
{$rawSummary}
Operating Hours: {$hoursText}
Location/Directions: {$landmarkText}

{$knowledgeText}
### CORE SERVICES & PRICING
{$servicesText}

### KEY CONSTRAINTS
1. **VOICE-ONLY**: You are on a phone call. Keep responses under 20 words.
2. **NO MARKDOWN**: Never use symbols like *, #, or lists (-). Speak in full, simple sentences.
3. **ACCURACY**: Only discuss services listed above. 
4. **ACTIONS**: {$actionInstruction}

### GOAL
Provide the best customer experience as a {$tone} while remaining efficient.
EOT;
    }

    /**
     * Convert short schedule string to natural speech.
     */
    protected function humanizeHours(string $schedule): string
    {
        // Simple replacements for days
        $map = [
            'Mon' => 'Monday',
            'Tue' => 'Tuesday',
            'Wed' => 'Wednesday',
            'Thu' => 'Thursday',
            'Fri' => 'Friday',
            'Sat' => 'Saturday',
            'Sun' => 'Sunday',
            '-' => ' to ', // For ranges like Mon-Fri
        ];

        $human = strtr($schedule, $map);

        // Convert 24h to 12h (Simple regex for 00:00 format)
        $human = preg_replace_callback('/(\d{2}):(\d{2})/', function ($m) {
            $time = strtotime("{$m[1]}:{$m[2]}");
            return date('g:i A', $time);
        }, $human);

        // Cleanup ":00" for cleaner speech (8:00 AM -> 8 AM)
        $human = str_replace(':00', '', $human);

        return $human;
    }
}
