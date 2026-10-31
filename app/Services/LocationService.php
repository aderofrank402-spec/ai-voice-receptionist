<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationService
{
    protected $apiKey;
    protected $baseUrl = "https://maps.googleapis.com/maps/api/place";

    public function __construct($config = [])
    {
        // Prioritize: 1. Passed config, 2. DB Global, 3. Default env
        $this->apiKey = $config['api_key'] ?? \App\Models\SystemCredential::getValue('google_maps_api_key', env('GOOGLE_MAPS_API_KEY'));
    }

    /**
     * Generate a clickable Google Maps Navigation Link for a business
     * @param string $placeId The unique Google Place ID for the clinic/salon
     * @return string
     */
    public function generateDirectionLink($placeId)
    {
        return "https://www.google.com/maps/search/?api=1&query=Google&query_place_id={$placeId}";
    }

    /**
     * Get full details for a specific Place ID (Cached for 24h)
     */
    public function getPlaceDetails($placeId)
    {
        if (!$this->apiKey) {
            return ['error' => 'Google Maps API Key not configured.'];
        }

        return \Illuminate\Support\Facades\Cache::remember("google_maps_place_{$placeId}", 86400, function () use ($placeId) {
            $response = Http::timeout(3)->get("https://maps.googleapis.com/maps/api/place/details/json", [
                'place_id' => $placeId,
                'fields'   => 'name,formatted_address,geometry,url',
                'key'      => $this->apiKey,
            ]);

            if ($response->failed() || !$response->json('result')) {
                Log::error("Google Maps Get Details Failed: " . $response->body());
                return ['error' => 'Failed to fetch place details'];
            }

            return $response->json('result');
        });
    }

    /**
     * Search for a business Place ID by name and city
     */
    public function findBusinessPlaceId($name, $city = 'Nairobi')
    {
        if (!$this->apiKey) {
            return ['error' => 'Google Maps API Key not configured.'];
        }

        $input = "{$name} in {$city}";
        
        $response = Http::timeout(3)->get("{$this->baseUrl}/findplacefromtext/json", [
            'input'     => $input,
            'inputtype' => 'textquery',
            'fields'    => 'place_id,name,formatted_address',
            'key'       => $this->apiKey,
        ]);

        if ($response->failed() || empty($response->json('candidates'))) {
            Log::error("Google Maps Find Place Failed: " . $response->body());
            return ['error' => 'Business not found on Google Maps', 'details' => $response->json()];
        }

        return $response->json('candidates')[0];
    }
}
