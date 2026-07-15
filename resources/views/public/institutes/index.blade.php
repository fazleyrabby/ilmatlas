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
        $genders = ['male' => 'Male', 'female' => 'Female', 'mixed' => 'Mixed'];
        $selType = request('type');
        $selCat = request('category') ? (is_array(request('category')) ? request('category') : explode(',', request('category'))) : [];
        $selCur = request('curriculum') ? (is_array(request('curriculum')) ? request('curriculum') : explode(',', request('curriculum'))) : [];
        $selGen = request('gender');
        $selDist = request('district') ? (is_array(request('district')) ? request('district') : explode(',', request('district'))) : [];
    @endphp

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-[280px_1fr]">
        {{-- Filter sidebar --}}
        <aside class="lg:sticky lg:top-24 lg:self-start" x-data="{
                type: {{ $selType ? "'".e($selType)."'" : 'null' }},
                category: {{ json_encode($selCat) }},
                curriculum: {{ json_encode($selCur) }},
                gender: {{ $selGen ? "'".e($selGen)."'" : 'null' }},
                district: {{ json_encode($selDist) }},
                get count() { return (this.type?1:0) + this.category.length + this.curriculum.length + (this.gender?1:0) + this.district.length; },
                toggle(list, val) { const i = this[list].indexOf(val); if (i>-1) this[list].splice(i,1); else this[list].push(val); }
            }">
            <form method="GET" class="card p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="flex items-center gap-2 text-sm font-semibold text-text-primary">
                        <i data-lucide="sliders-horizontal" class="h-4 w-4"></i> Filters
                        <span x-show="count > 0" x-text="count" class="ml-1 rounded-full bg-primary-600 px-1.5 py-0.5 text-xs font-semibold text-white" style="display:none"></span>
                    </h2>
                    <a href="{{ route('institutes.index') }}" class="text-xs font-medium text-text-muted hover:text-primary-700">Reset</a>
                </div>

                <div class="space-y-5">
                    {{-- Type (single) --}}
                    <div>
                        <p class="eb-label">Type</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button type="button" @click="type = (type === null ? null : null)"
                                    :class="type === null ? 'chip chip-active' : 'chip'">All</button>
                            @foreach($types as $type)
                                <button type="button" @click="type = (type === '{{ $type->slug }}' ? null : '{{ $type->slug }}')"
                                        :class="type === '{{ $type->slug }}' ? 'chip chip-active' : 'chip'">{{ $type->name }}</button>
                            @endforeach
                        </div>
                        <input type="hidden" name="type" :value="type ?? ''">
                    </div>

                    {{-- Category (multi) --}}
                    <div>
                        <p class="eb-label">Category</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($categories as $cat)
                                <button type="button" @click="toggle('category', '{{ $cat->slug }}')"
                                        :class="category.includes('{{ $cat->slug }}') ? 'chip chip-active' : 'chip'">{{ $cat->name }}</button>
                            @endforeach
                        </div>
                        <template x-for="v in category" :key="'cat-'+v"><input type="hidden" name="category[]" :value="v"></template>
                    </div>

                    {{-- Curriculum (multi) --}}
                    <div>
                        <p class="eb-label">Curriculum</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($curriculums as $c)
                                <button type="button" @click="toggle('curriculum', '{{ $c->slug }}')"
                                        :class="curriculum.includes('{{ $c->slug }}') ? 'chip chip-active' : 'chip'">{{ $c->name }}</button>
                            @endforeach
                        </div>
                        <template x-for="v in curriculum" :key="'cur-'+v"><input type="hidden" name="curriculum[]" :value="v"></template>
                    </div>

                    {{-- Gender (single) --}}
                    <div>
                        <p class="eb-label">Gender</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($genders as $gval => $glabel)
                                <button type="button" @click="gender = (gender === '{{ $gval }}' ? null : '{{ $gval }}')"
                                        :class="gender === '{{ $gval }}' ? 'chip chip-active' : 'chip'">{{ $glabel }}</button>
                            @endforeach
                        </div>
                        <input type="hidden" name="gender" :value="gender ?? ''">
                    </div>

                    {{-- District (searchable multi-select for large dataset) --}}
                    <div>
                        <p class="eb-label">District</p>
                        <select name="district_ts" id="district-filter" multiple class="tom-select" x-ref="districtSelect"
                                x-init="$nextTick(() => {
                                    if (window.TomSelect && $refs.districtSelect) {
                                        const ts = new window.TomSelect($refs.districtSelect, {
                                            plugins: ['remove_button'],
                                            persist: false, create: false, hideSelected: true,
                                            placeholder: 'Any district…',
                                            onChange: (vals) => { district = vals; },
                                        });
                                        district.forEach(v => ts.addItem(v, true));
                                    }
                                })">
                            @foreach($districts as $d)
                                <option value="{{ $d->id }}" :selected="district.includes('{{ $d->id }}')">{{ $d->name }}</option>
                            @endforeach
                        </select>
                        <template x-for="v in district" :key="'dist-'+v"><input type="hidden" name="district[]" :value="v"></template>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-full">Apply Filters</button>
                </div>
            </form>
        </aside>

        {{-- Results --}}
        <div>
            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm text-text-muted">{{ number_format($institutes->total()) }} institutes found</p>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
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
