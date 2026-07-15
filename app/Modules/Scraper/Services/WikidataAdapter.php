<?php

namespace App\Modules\Scraper\Services;

use App\Modules\Scraper\Contracts\ScraperAdapterInterface;
use App\Modules\Scraper\Models\ScraperSource;

class WikidataAdapter implements ScraperAdapterInterface
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

        return $decoded['entities'] ?? [];
    }

    public function normalize(array $parsedData, ScraperSource $source): array
    {
        // Extract claims from first entity in response
        $entityId = key($parsedData);
        $claims = $parsedData[$entityId]['claims'] ?? [];

        // Parse inception year (established_year) -> P571
        $establishedYear = null;
        if (isset($claims['P571'][0]['mainsnak']['datavalue']['value']['time'])) {
            $timeStr = $claims['P571'][0]['mainsnak']['datavalue']['value']['time'];
            $establishedYear = (int) substr(ltrim($timeStr, '+'), 0, 4);
        }

        // Parse official website -> P856
        $website = null;
        if (isset($claims['P856'][0]['mainsnak']['datavalue']['value'])) {
            $website = $claims['P856'][0]['mainsnak']['datavalue']['value'];
        }

        return [
            'institute_id' => $source->institute_id,
            'established_year' => $establishedYear,
            'website' => $website,
        ];
    }

    public function getConfidence(array $normalizedData): float
    {
        return 80.0; // Wikidata confidence score
    }
}
