@extends('layouts.public')

@section('title', 'Compare: '.implode(' vs ', array_map(fn ($i) => $i->name, $matrix->institutes)).' — EduBase')
@section('meta_description', 'Side-by-side comparison of '.implode(', ', array_map(fn ($i) => $i->name, $matrix->institutes)).'. Compare fees, curriculum, facilities, location, and more.')

@push('styles')
<link rel="canonical" href="{{ url()->current() }}">
<style>
@media print {
    nav, footer, .flex.items-center { display: none !important; }
    .overflow-x-auto { overflow: visible !important; }
    table { box-shadow: none !important; }
    th.sticky { position: static !important; }
}
</style>
@endpush

@push('scripts')
<script>
function copyShareUrl() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Comparison URL copied to clipboard!');
    });
}
</script>
@endpush

@section('content')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebPage",
    "name": "Compare: {{ implode(' vs ', array_map(fn ($i) => $i->name, $matrix->institutes)) }}",
    "description": "Side-by-side comparison of educational institutions in Bangladesh.",
    "about": {
        "@@type": "ItemList",
        "itemListElement": [
            @foreach($matrix->institutes as $idx => $i)
            {
                "@@type": "ListItem",
                "position": {{ $idx + 1 }},
                "item": {
                    "@@type": "EducationalOrganization",
                    "name": "{{ $i->name }}",
                    "url": "{{ route('institutes.show', $i) }}"
                }
            }@if(!$loop->last),@endif
            @endforeach
        ]
    }
}
</script>
<div class="container-page py-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <span class="text-eyebrow">Side-by-side</span>
            <h1 class="section-title mt-1">Compare Institutes</h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <label class="flex cursor-pointer items-center gap-2 text-sm text-text-secondary">
                <input type="checkbox" id="hideIdentical" checked
                       x-model="hideIdentical"
                       class="rounded border-border text-primary-600 focus:ring-primary-500">
                Hide identical rows
            </label>
            <button data-print class="btn btn-secondary btn-sm"><i data-lucide="printer"></i> Print</button>
            <button data-share class="btn btn-secondary btn-sm"><i data-lucide="share-2"></i> Share</button>
        </div>
    </div>

    <div class="card overflow-hidden" x-data="{ collapsed: {}, hideIdentical: true }">
        <div class="overflow-x-auto">
            <table class="eb-table w-full" id="comparisonTable">
                <thead>
                    <tr>
                        <th class="sticky left-0 z-10 min-w-[180px] bg-surface-muted"></th>
                        @foreach ($matrix->institutes as $institute)
                            <th class="relative min-w-[220px] text-center">
                                <a href="{{ route('institutes.show', $institute) }}" class="font-semibold text-primary-700 hover:text-primary-800 normal-case tracking-normal text-sm">
                                    {{ $institute->name }}
                                </a>
                                <button data-remove-slug="{{ $institute->slug }}"
                                        class="compare-remove absolute right-1 top-1"
                                        title="Remove from comparison" aria-label="Remove"><i data-lucide="x" style="width:0.85rem;height:0.85rem;stroke-width:3"></i></button>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                @foreach ($matrix->groups as $group)
                    <tbody>
                        <tr class="compare-group-header"
                            :class="collapsed['{{ $group->slug }}'] ? 'collapsed' : ''"
                            @click="collapsed['{{ $group->slug }}'] = !collapsed['{{ $group->slug }}']">
                            <td colspan="{{ count($matrix->institutes) + 1 }}"
                                class="flex items-center gap-2 bg-surface-muted text-xs font-bold uppercase tracking-wider text-text-muted">
                                <i data-lucide="chevron-down" class="chevron h-4 w-4"></i>
                                {{ $group->name }}
                                <span class="ml-1 rounded-full bg-neutral-200 px-2 py-0.5 text-[10px] font-semibold text-neutral-600"
                                      x-show="collapsed['{{ $group->slug }}']" style="display:none">{{ count($group->rows) }}</span>
                            </td>
                        </tr>
                        @foreach ($group->rows as $row)
                            <tr class="compare-row"
                                x-show="!collapsed['{{ $group->slug }}'] && !(hideIdentical && {{ $row->allIdentical ? 'true' : 'false' }})"
                                x-cloak
                                :class="!{{ $row->allIdentical ? 'true' : 'false' }} ? 'compare-row-diff' : ''">
                                <td class="sticky left-0 z-10 bg-surface font-medium text-text-secondary">
                                    {{ $row->label }}
                                </td>
                                @foreach ($row->values as $vi => $value)
                                    <td class="text-center {{ $row->allIdentical ? 'text-text-muted' : 'compare-cell-diff' }}">
                                        {!! $value !!}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                @endforeach
            </table>
        </div>
    </div>
</div>
@endsection
