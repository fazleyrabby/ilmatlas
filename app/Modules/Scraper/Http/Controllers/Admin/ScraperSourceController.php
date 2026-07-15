<?php

namespace App\Modules\Scraper\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Institute\Models\Institute;
use App\Modules\Scraper\Models\ScraperSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ScraperSourceController extends Controller
{
    public function index(): View
    {
        $sources = ScraperSource::with(['institute:id,name', 'latestRun'])
            ->latest()
            ->paginate(30);

        return view('admin.scrapers.sources', compact('sources'));
    }

    public function create(): View
    {
        $institutes = Institute::orderBy('name')->get(['id', 'name']);

        return view('admin.scrapers.source-form', [
            'source' => new ScraperSource,
            'institutes' => $institutes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:500',
            'institute_id' => 'nullable|exists:institutes,id',
            'source_type' => 'required|string|in:website,pdf,rss,api,manual',
            'base_url' => 'required|string|max:1000',
            'trust_level' => 'required|string|in:trusted,review_required,untrusted',
            'schedule_frequency' => 'required|string|in:hourly,daily,weekly,monthly,manual',
            'is_active' => 'boolean',
            'adapter_class' => 'required|string',
        ]);

        ScraperSource::create([
            'uuid' => (string) Str::uuid(),
            ...$data,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return redirect()->route('admin.scrapers.sources.index')
            ->with('success', 'Scraper source created.');
    }

    public function edit(ScraperSource $source): View
    {
        $institutes = Institute::orderBy('name')->get(['id', 'name']);

        return view('admin.scrapers.source-form', compact('source', 'institutes'));
    }

    public function update(Request $request, ScraperSource $source): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:500',
            'institute_id' => 'nullable|exists:institutes,id',
            'source_type' => 'required|string|in:website,pdf,rss,api,manual',
            'base_url' => 'required|string|max:1000',
            'trust_level' => 'required|string|in:trusted,review_required,untrusted',
            'schedule_frequency' => 'required|string|in:hourly,daily,weekly,monthly,manual',
            'is_active' => 'boolean',
            'adapter_class' => 'required|string',
        ]);

        $source->update($data);

        return redirect()->route('admin.scrapers.sources.index')
            ->with('success', 'Scraper source updated.');
    }

    public function toggle(ScraperSource $source): RedirectResponse
    {
        $source->update(['is_active' => ! $source->is_active]);

        return redirect()->route('admin.scrapers.sources.index')
            ->with('success', $source->is_active ? 'Source enabled.' : 'Source disabled.');
    }
}
