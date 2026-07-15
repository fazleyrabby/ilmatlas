<?php

namespace App\Modules\Scraper\Services;

use App\Modules\Scraper\Contracts\ScraperAdapterInterface;
use App\Modules\Scraper\Models\ScraperSource;
use Illuminate\Support\Str;

class OsmAdapter implements ScraperAdapterInterface
{
    public function fetch(ScraperSource $source): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $source->source_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) EduBaseScraper/1.0');
        $json = curl_exec($ch);
        curl_close($ch);

        return $json ?: '';
    }

    public function parse(string $rawContent, ScraperSource $source): array
    {
        if (empty($rawContent)) {
            return [];
        }

        $decoded = json_decode($rawContent, true);
        if (!is_array($decoded)) {
            return [];
        }

        $elements = $decoded['elements'] ?? [];
        if (empty($elements)) {
            return [];
        }

        // Return first matching element (usually node or way)
        return $elements[0] ?? [];
    }

    public function normalize(array $parsedData, ScraperSource $source): array
    {
        $tags = $parsedData['tags'] ?? [];
        
        return [
            'institute_id' => $source->institute_id,
            'latitude' => $parsedData['lat'] ?? $parsedData['center']['lat'] ?? null,
            'longitude' => $parsedData['lon'] ?? $parsedData['center']['lon'] ?? null,
            'full_address' => $tags['addr:full'] ?? $tags['addr:street'] ?? null,
            'website' => $tags['website'] ?? $tags['contact:website'] ?? null,
        ];
    }

    public function getConfidence(array $normalizedData): float
    {
        return 90.0; // OpenStreetMap confidence score
    }
}
