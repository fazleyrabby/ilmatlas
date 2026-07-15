<?php

namespace App\Modules\SEO\Commands;

use App\Modules\Institute\Models\Institute;
use App\Modules\Location\Models\District;
use App\Modules\Taxonomy\Models\InstituteType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate XML sitemaps for search engines';

    public function handle(): int
    {
        $sitemaps = [];

        $sitemaps[] = $this->writeSitemap('sitemap-institutes.xml', $this->getInstituteUrls());
        $sitemaps[] = $this->writeSitemap('sitemap-districts.xml', $this->getDistrictUrls());
        $sitemaps[] = $this->writeSitemap('sitemap-types.xml', $this->getTypeUrls());
        $sitemaps[] = $this->writeSitemap('sitemap-pseo.xml', $this->getPSEOUrls());
        $sitemaps[] = $this->writeSitemap('sitemap-static.xml', $this->getStaticUrls());

        $this->writeSitemapIndex($sitemaps);

        $this->info('Sitemap generated successfully.');

        return Command::SUCCESS;
    }

    private function writeSitemap(string $filename, array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.e($url['loc'])."</loc>\n";
            $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        Storage::disk('public')->put($filename, $xml);

        return asset("storage/{$filename}");
    }

    private function writeSitemapIndex(array $sitemaps): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($sitemaps as $url) {
            $xml .= "  <sitemap>\n";
            $xml .= '    <loc>'.e($url)."</loc>\n";
            $xml .= '    <lastmod>'.now()->toDateString()."</lastmod>\n";
            $xml .= "  </sitemap>\n";
        }

        $xml .= '</sitemapindex>';

        Storage::disk('public')->put('sitemap.xml', $xml);
    }

    private function getInstituteUrls(): array
    {
        $urls = [];
        $now = now()->toDateString();

        Institute::select('id', 'slug', 'updated_at')->chunk(200, function ($institutes) use (&$urls) {
            foreach ($institutes as $institute) {
                $urls[] = [
                    'loc' => route('institutes.show', $institute->slug),
                    'lastmod' => $institute->updated_at->toDateString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            }
        });

        return $urls;
    }

    private function getDistrictUrls(): array
    {
        $urls = [];
        $now = now()->toDateString();

        District::select('id', 'slug', 'name')->each(function ($district) use (&$urls, $now) {
            $urls[] = [
                'loc' => route('institutes.by.district', $district->slug),
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        });

        return $urls;
    }

    private function getTypeUrls(): array
    {
        $urls = [];
        $now = now()->toDateString();

        InstituteType::select('id', 'slug', 'name')->each(function ($type) use (&$urls, $now) {
            $urls[] = [
                'loc' => route('institutes.by.type', $type->slug),
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        });

        return $urls;
    }

    private function getPSEOUrls(): array
    {
        $urls = [];
        $now = now()->toDateString();

        $types = InstituteType::pluck('slug', 'name');
        $districts = District::pluck('slug', 'name');

        foreach ($types as $typeName => $typeSlug) {
            foreach ($districts as $districtName => $districtSlug) {
                $urls[] = [
                    'loc' => route('institutes.pseo', ['type' => $typeSlug, 'district' => $districtSlug]),
                    'lastmod' => $now,
                    'changefreq' => 'weekly',
                    'priority' => '0.5',
                ];
            }
        }

        return $urls;
    }

    private function getStaticUrls(): array
    {
        $now = now()->toDateString();

        return [
            [
                'loc' => url('/'),
                'lastmod' => $now,
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('about'),
                'lastmod' => $now,
                'changefreq' => 'monthly',
                'priority' => '0.3',
            ],
            [
                'loc' => route('contact'),
                'lastmod' => $now,
                'changefreq' => 'monthly',
                'priority' => '0.3',
            ],
            [
                'loc' => route('privacy'),
                'lastmod' => $now,
                'changefreq' => 'monthly',
                'priority' => '0.2',
            ],
            [
                'loc' => route('terms'),
                'lastmod' => $now,
                'changefreq' => 'monthly',
                'priority' => '0.2',
            ],
            [
                'loc' => url('/institutes'),
                'lastmod' => $now,
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
        ];
    }
}
