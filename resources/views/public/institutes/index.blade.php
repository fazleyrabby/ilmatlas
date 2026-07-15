@extends('layouts.public')

@section('title', $seo['meta_title'] ?? 'Institutes — EduBase')
@section('meta_description', $seo['meta_description'] ?? 'Browse and compare schools, madrasas, and colleges across Bangladesh.')
@section('meta_keywords', $seo['meta_keywords'] ?? '')
@section('og_title', $seo['og_title'] ?? '')
@section('og_description', $seo['og_description'] ?? '')
@section('canonical_url', $seo['canonical_url'] ?? url()->current())
@if(isset($seo['noindex']) && $seo['noindex'])
    @section('robots', 'noindex, nofollow')
@endif

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <x-schema-breadcrumb :items="array_merge(
        [['name' => 'Home', 'url' => url('/')]],
        isset($currentType) ? [['name' => 'Institutes', 'url' => route('institutes.index')], ['name' => $currentType->name]] : [],
        isset($currentDistrict) && !isset($currentType) ? [['name' => 'Institutes', 'url' => route('institutes.index')], ['name' => $currentDistrict->name]] : [],
        isset($currentType) && isset($currentDistrict) ? [['name' => 'Institutes', 'url' => route('institutes.index')], ['name' => $currentType->name], ['name' => $currentDistrict->name]] : [],
        !isset($currentType) && !isset($currentDistrict) ? [] : []
    )" />

    <h1 class="text-3xl font-bold text-text-primary mb-2 mt-4">
        @if(isset($currentType) && isset($currentDistrict))
            {{ $currentType->name }}s in {{ $currentDistrict->name }}
        @elseif(isset($currentType))
            {{ $currentType->name }}s
        @elseif(isset($currentDistrict))
            Institutes in {{ $currentDistrict->name }}
        @else
            Educational Institutes
        @endif
    </h1>
    <p class="text-text-secondary mb-8 max-w-2xl">
        @if(isset($currentType) && isset($currentDistrict))
            Browse {{ $currentType->name }}s in {{ $currentDistrict->name }}. Compare fees, curriculum, facilities, and admission.
        @else
            Browse and compare schools, madrasas, and colleges across Bangladesh.
        @endif
    </p>

    @php
        $genders = ['boys' => 'Boys', 'girls' => 'Girls', 'co_educational' => 'Co-educational'];
        $typeSlugs = $types->pluck('slug')->all();
        $rawType = request('type');
        $selType = in_array($rawType, $typeSlugs, true) ? $rawType : '';
        $selCat = request('category') ? (is_array(request('category')) ? request('category') : explode(',', request('category'))) : [];
        $selCur = request('curriculum') ? (is_array(request('curriculum')) ? request('curriculum') : explode(',', request('curriculum'))) : [];
        $rawGen = request('gender');
        $selGen = array_key_exists($rawGen, $genders) ? $rawGen : '';
        $selDist = request('district') ? (is_array(request('district')) ? request('district') : explode(',', request('district'))) : [];
    @endphp

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-[240px_1fr]">
        {{-- Filter sidebar --}}
        <aside class="lg:sticky lg:top-24 lg:self-start" x-data="{
                type: '{{ $selType }}',
                category: {!! json_encode(array_values($selCat)) !!},
                curriculum: {!! json_encode(array_values($selCur)) !!},
                gender: '{{ $selGen }}',
                district: {!! json_encode(array_values($selDist)) !!},
                get count() { return (this.type?1:0) + this.category.length + this.curriculum.length + (this.gender?1:0) + this.district.length; }
            }"
            @submit="category = $refs.categorySelect ? Array.from($refs.categorySelect.selectedOptions).map(o => o.value) : category; curriculum = $refs.curriculumSelect ? Array.from($refs.curriculumSelect.selectedOptions).map(o => o.value) : curriculum; district = $refs.districtSelect ? Array.from($refs.districtSelect.selectedOptions).map(o => o.value) : district;">
            <form method="GET" class="card p-4">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="flex items-center gap-2 text-sm font-semibold text-text-primary">
                        <i data-lucide="sliders-horizontal" class="h-4 w-4"></i> Filters
                        <span x-show="count > 0" x-text="count" class="ml-1 rounded-full bg-primary-600 px-1.5 py-0.5 text-xs font-semibold text-white" style="display:none"></span>
                    </h2>
                    <a href="{{ route('institutes.index') }}" class="text-xs font-medium text-text-muted hover:text-primary-700">Reset</a>
                </div>

                <div class="space-y-5">
                    {{-- Type (single, native select) --}}
                    <div>
                        <p class="eb-label">Type</p>
                        <select x-model="type" name="type" class="mt-2 w-full rounded-md border-border bg-surface px-3 py-2 text-sm text-text-primary focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            <option value="">All types</option>
                            @foreach($types as $type)
                                <option value="{{ $type->slug }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Category (multi select) --}}
                    <div>
                        <p class="eb-label">Category</p>
                        <select id="category-filter" multiple class="tom-select mt-2" x-ref="categorySelect"
                                x-init="$nextTick(() => {
                                    if (window.TomSelect && $refs.categorySelect) {
                                        new window.TomSelect($refs.categorySelect, {
                                            plugins: ['remove_button'],
                                            persist: false, create: false, hideSelected: true,
                                            placeholder: 'Any category…',
                                        });
                                    }
                                })">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}" {{ in_array($cat->slug, $selCat) ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="category" :value="category.join(',')">
                    </div>

                    {{-- Curriculum (multi select) --}}
                    <div>
                        <p class="eb-label">Curriculum</p>
                        <select id="curriculum-filter" multiple class="tom-select mt-2" x-ref="curriculumSelect"
                                x-init="$nextTick(() => {
                                    if (window.TomSelect && $refs.curriculumSelect) {
                                        new window.TomSelect($refs.curriculumSelect, {
                                            plugins: ['remove_button'],
                                            persist: false, create: false, hideSelected: true,
                                            placeholder: 'Any curriculum…',
                                        });
                                    }
                                })">
                            @foreach($curriculums as $c)
                                <option value="{{ $c->slug }}" {{ in_array($c->slug, $selCur) ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="curriculum" :value="curriculum.join(',')">
                    </div>

                    {{-- Gender (single) --}}
                    <div>
                        <p class="eb-label">Gender</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($genders as $gval => $glabel)
                                <button type="button" @click="gender = (gender === '{{ $gval }}' ? '' : '{{ $gval }}')"
                                        :class="gender === '{{ $gval }}' ? 'chip chip-active' : 'chip'">{{ $glabel }}</button>
                            @endforeach
                        </div>
                        <input type="hidden" name="gender" :value="gender">
                    </div>

                    {{-- District (searchable multi-select for large dataset) --}}
                    <div>
                        <p class="eb-label">District</p>
                        <select id="district-filter" multiple class="tom-select" x-ref="districtSelect"
                                x-init="$nextTick(() => {
                                    if (window.TomSelect && $refs.districtSelect) {
                                        new window.TomSelect($refs.districtSelect, {
                                            plugins: ['remove_button'],
                                            persist: false, create: false, hideSelected: true,
                                            placeholder: 'Any district…',
                                        });
                                    }
                                })">
                            @foreach($districts as $d)
                                <option value="{{ $d->slug }}" {{ in_array($d->slug, $selDist) ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="district" :value="district.join(',')">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-full">Apply Filters</button>
                </div>
            </form>
        </aside>

        {{-- Results --}}
        <div x-data="{ viewMode: 'grid' }">
            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-text-muted">{{ number_format($institutes->total()) }} institutes found</p>
                <div class="flex items-center gap-1 bg-surface-muted p-0.5 rounded-lg border border-border">
                    <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-surface text-primary-700 shadow-xs' : 'text-text-muted'" class="p-1.5 rounded-md hover:text-text-primary transition-all" title="Grid View">
                        <i data-lucide="grid" class="h-4 w-4"></i>
                    </button>
                    <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-surface text-primary-700 shadow-xs' : 'text-text-muted'" class="p-1.5 rounded-md hover:text-text-primary transition-all" title="List View">
                        <i data-lucide="list" class="h-4 w-4"></i>
                    </button>
                </div>
            </div>

            <!-- Grid View -->
            <div x-show="viewMode === 'grid'" class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @forelse($institutes as $institute)
                    <x-ui.institute-card :institute="$institute" />
                @empty
                    <div class="col-span-full">
                        <div class="card flex flex-col items-center gap-3 py-16 text-center">
                            <i data-lucide="search-x" class="h-10 w-10 text-text-muted"></i>
                            <p class="text-text-secondary">No institutes found matching your criteria.</p>
                            <a href="{{ route('institutes.index') }}" class="btn btn-secondary btn-sm">Clear filters</a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- List View -->
            <div x-show="viewMode === 'list'" class="space-y-4" style="display:none">
                @forelse($institutes as $institute)
                    <article class="card card-hover group flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 gap-4">
                        <a href="{{ route('institutes.show', $institute) }}" class="flex items-center gap-4 flex-1 min-w-0">
                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-md bg-primary-50 text-base font-bold text-primary-700 ring-1 ring-primary-100">
                                {{ strtoupper(substr($institute->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="truncate font-semibold text-text-primary group-hover:text-primary-700">
                                        {{ $institute->name }}
                                    </h3>
                                    @if(($institute->verification_status ?? null) === 'verified')
                                        <span class="badge badge-success flex-shrink-0" title="Verified">
                                            <i data-lucide="badge-check"></i>
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-metadata">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="map-pin" class="h-3.5 w-3.5"></i>
                                        {{ data_get($institute, 'district.name') }}
                                    </span>
                                    @if(data_get($institute, 'type.name'))
                                        <span class="text-text-muted">•</span>
                                        <span>{{ data_get($institute, 'type.name') }}</span>
                                    @endif
                                    @if(data_get($institute, 'gender'))
                                        <span class="text-text-muted">•</span>
                                        <span class="capitalize">{{ data_get($institute, 'gender') }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                        <div class="flex flex-row sm:flex-col items-end gap-3 sm:gap-1 text-right flex-shrink-0 w-full sm:w-auto border-t sm:border-t-0 pt-3 sm:pt-0 border-divider">
                            <div class="flex-1 sm:flex-initial text-left sm:text-right">
                                <div class="text-xs text-text-muted">Est. monthly fee</div>
                                <div class="text-base font-bold tabular-nums text-text-primary">
                                    @if((float) ($institute->estimated_monthly_fee ?? 0) > 0)
                                        ৳{{ number_format($institute->estimated_monthly_fee, 0) }}
                                    @else
                                        <span class="text-xs font-medium text-text-muted">N/A</span>
                                    @endif
                                </div>
                            </div>
                            <button
                                class="compare-btn flex items-center justify-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium text-primary-700 transition-colors hover:bg-primary-50 border border-border"
                                data-uuid="{{ $institute->uuid }}"
                                data-slug="{{ $institute->slug }}"
                                data-name="{{ $institute->name }}"
                            >
                                <i data-lucide="git-compare" class="h-3.5 w-3.5"></i>
                                Compare
                            </button>
                        </div>
                    </article>
                @empty
                    <div class="card flex flex-col items-center gap-3 py-16 text-center">
                        <i data-lucide="search-x" class="h-10 w-10 text-text-muted"></i>
                        <p class="text-text-secondary">No institutes found matching your criteria.</p>
                        <a href="{{ route('institutes.index') }}" class="btn btn-secondary btn-sm">Clear filters</a>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $institutes->links() }}
            </div>
        </div>
    </div>

    @if(isset($currentType, $currentDistrict))
        <div class="mt-12 card p-6">
            <h2 class="text-lg font-semibold mb-4">Browse Other Areas</h2>
            <div class="flex flex-wrap gap-2">
                @foreach(\App\Modules\Location\Models\District::inRandomOrder()->limit(10)->get() as $d)
                    <a href="{{ route('institutes.pseo', ['type' => $currentType->slug, 'district' => $d->slug]) }}"
                       class="chip">
                        {{ $currentType->name }}s in {{ $d->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @elseif(isset($currentType))
        <div class="mt-12 card p-6">
            <h2 class="text-lg font-semibold mb-4">Browse by District</h2>
            <div class="flex flex-wrap gap-2">
                @foreach(\App\Modules\Location\Models\District::inRandomOrder()->limit(12)->get() as $d)
                    <a href="{{ route('institutes.by.district', $d->slug) }}"
                       class="chip">
                        {{ $d->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @elseif(isset($currentDistrict))
        <div class="mt-12 card p-6">
            <h2 class="text-lg font-semibold mb-4">Browse by Type</h2>
            <div class="flex flex-wrap gap-2">
                @foreach(\App\Modules\Taxonomy\Models\InstituteType::all() as $t)
                    <a href="{{ route('institutes.by.type', $t->slug) }}"
                       class="chip">
                        {{ $t->name }}s in {{ $currentDistrict->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @else
        <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card p-6">
                <h2 class="text-lg font-semibold mb-4">Browse by Type</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach(\App\Modules\Taxonomy\Models\InstituteType::all() as $t)
                        <a href="{{ route('institutes.by.type', $t->slug) }}"
                           class="chip">
                            {{ $t->name }}s
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="card p-6">
                <h2 class="text-lg font-semibold mb-4">Browse by District</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach(\App\Modules\Location\Models\District::inRandomOrder()->limit(12)->get() as $d)
                        <a href="{{ route('institutes.by.district', $d->slug) }}"
                           class="chip">
                            {{ $d->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
