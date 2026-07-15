@extends('layouts.public')

@php
    use App\Modules\Location\Models\Division;
    use App\Modules\Taxonomy\Models\Category;
    use App\Modules\Taxonomy\Models\InstituteType;
    use App\Modules\Institute\Models\Institute;

    $divisions = Division::withCount('publishedInstitutes')->orderBy('name')->get();
    $categories = Category::where('is_active', true)->withCount('publishedInstitutes')->orderBy('sort_order')->take(8)->get();
    $featured = Institute::published()->with(['type', 'district', 'upazila'])->latest()->take(6)->get();

    $stats = [
        ['value' => number_format(Institute::published()->count()), 'label' => 'Institutions'],
        ['value' => number_format(Division::count()), 'label' => 'Divisions'],
        ['value' => number_format(\App\Modules\Location\Models\District::count()), 'label' => 'Districts'],
        ['value' => number_format(InstituteType::count()), 'label' => 'Categories'],
    ];

    $categoryIcons = [
        'school' => 'school', 'college' => 'graduation-cap', 'madrasa' => 'book-open',
        'qawmi' => 'book-marked', 'alia' => 'book', 'english-medium' => 'languages',
        'english-version' => 'globe', 'technical' => 'wrench', 'ahlul-hadith' => 'book-open-check',
    ];
@endphp

@section('content')
<x-ui.hero :searchAction="route('search')" :stats="$stats"
           video="{{ asset('assets/media/hero-bg.mp4') }}"
           image="{{ asset('assets/media/hero-fallback.png') }}" />

{{-- Browse by Division --}}
<section class="section">
    <div class="container-page">
        <div class="section-head">
            <div>
                <span class="text-eyebrow">Explore</span>
                <h2 class="section-title mt-1">Browse by Division</h2>
                <p class="section-subtitle">Discover institutions across all eight divisions of Bangladesh.</p>
            </div>
            <a href="{{ route('institutes.index') }}" class="hidden items-center gap-1 text-sm font-medium text-primary-700 hover:text-primary-800 sm:inline-flex">
                All institutes <i data-lucide="arrow-right" class="h-4 w-4"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            @foreach($divisions as $division)
                <a href="{{ route('locations.division', $division) }}"
                   class="card card-hover group flex items-center justify-between p-5">
                    <div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="map-pin" class="h-4 w-4 text-primary-600"></i>
                            <h3 class="font-semibold text-text-primary group-hover:text-primary-700">{{ $division->name }}</h3>
                        </div>
                        <p class="mt-1 text-sm text-text-muted">{{ number_format($division->published_institutes_count) }} institutes</p>
                    </div>
                    <i data-lucide="arrow-right" class="h-4 w-4 text-text-muted transition-transform group-hover:translate-x-0.5 group-hover:text-primary-600"></i>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- Browse by Category --}}
<section class="section-tight border-y border-border bg-surface-muted/50">
    <div class="container-page">
        <div class="section-head">
            <div>
                <span class="text-eyebrow">Categories</span>
                <h2 class="section-title mt-1">Browse by Category</h2>
                <p class="section-subtitle">Find the right type of institution for your needs.</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach($categories as $category)
                <a href="{{ route('categories.show', $category) }}"
                   class="card card-hover group flex items-center justify-between p-5">
                    <div class="flex items-center gap-2">
                        <i data-lucide="{{ $categoryIcons[$category->slug] ?? 'shapes' }}" class="h-4 w-4 text-primary-600"></i>
                        <h3 class="font-semibold text-text-primary group-hover:text-primary-700">{{ $category->name }}</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-text-muted">{{ number_format($category->published_institutes_count) }}</span>
                        <i data-lucide="arrow-right" class="h-4 w-4 text-text-muted transition-transform group-hover:translate-x-0.5 group-hover:text-primary-600"></i>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- Featured Institutes --}}
@if($featured->isNotEmpty())
<section class="section">
    <div class="container-page">
        <div class="section-head">
            <div>
                <span class="text-eyebrow">Featured</span>
                <h2 class="section-title mt-1">Recently Added Institutions</h2>
                <p class="section-subtitle">Freshly curated profiles with fees, facilities and admissions.</p>
            </div>
            <a href="{{ route('institutes.index') }}" class="hidden items-center gap-1 text-sm font-medium text-primary-700 hover:text-primary-800 sm:inline-flex">
                View all <i data-lucide="arrow-right" class="h-4 w-4"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($featured as $institute)
                <x-ui.institute-card :institute="$institute" />
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Compare Banner --}}
<section class="section-tight">
    <div class="container-page">
        <div class="relative overflow-hidden rounded-lg border border-primary-200 bg-primary-50 p-8 sm:p-12">
            <div class="pointer-events-none absolute -right-10 -top-10 h-48 w-48 rounded-full bg-primary-100 opacity-60"></div>
            <div class="relative max-w-2xl">
                <span class="badge badge-primary mb-3"><i data-lucide="git-compare"></i> Side-by-side</span>
                <h2 class="section-title">Compare institutions like never before</h2>
                <p class="section-subtitle mt-2">Line up fees, curriculum, facilities and location across up to five schools side-by-side.</p>
                <a href="{{ route('institutes.index') }}" class="btn btn-primary mt-6">
                    Start comparing <i data-lucide="arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
