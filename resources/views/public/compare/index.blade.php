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

/* GSMArena Style Adjustments */
.eb-table th, .eb-table td {
    border: 1px solid #e5e7eb;
    padding: 0.5rem 0.75rem;
}
.eb-table thead th {
    background-color: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
}
.group-header-cell {
    color: #dc2626; /* red-600 */
    font-weight: 700;
    text-transform: uppercase;
    background-color: #f9fafb;
    vertical-align: top;
    width: 120px;
}
.row-label-cell {
    font-weight: 600;
    color: #4b5563; /* gray-600 */
    background-color: #f9fafb;
    width: 160px;
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

document.addEventListener('alpine:init', () => {
    Alpine.data('comparisonTable', () => ({
        hideIdentical: true,
        groups: {
            @foreach($matrix->groups as $group)
            '{{ $group->slug }}': [
                @foreach($group->rows as $row)
                { identical: {{ $row->allIdentical ? 'true' : 'false' }} }@if(!$loop->last),@endif
                @endforeach
            ]@if(!$loop->last),@endif
            @endforeach
        },
        getVisibleCount(slug) {
            if (!this.hideIdentical) return this.groups[slug].length;
            return this.groups[slug].filter(r => !r.identical).length;
        },
        isFirstVisible(slug, index) {
            if (!this.hideIdentical) return index === 0;
            const rows = this.groups[slug];
            for (let i = 0; i < rows.length; i++) {
                if (!rows[i].identical) {
                    return i === index;
                }
            }
            return false;
        }
    }));
});
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

    <div class="card overflow-hidden" x-data="comparisonTable()">
        <div class="overflow-x-auto">
            <table class="eb-table w-full text-sm" id="comparisonTable">
                <thead>
                    <tr>
                        <th colspan="2" class="sticky left-0 z-10 bg-surface-muted"></th>
                        @foreach ($matrix->institutes as $institute)
                            <th class="relative min-w-[220px] text-center">
                                <a href="{{ route('institutes.show', $institute) }}" class="font-semibold text-primary-700 hover:text-primary-800 normal-case tracking-normal">
                                    {{ $institute->name }}
                                </a>
                                <button data-remove-slug="{{ $institute->slug }}"
                                        class="compare-remove absolute right-2 top-2"
                                        title="Remove from comparison" aria-label="Remove"><i data-lucide="x" style="width:0.85rem;height:0.85rem;stroke-width:3"></i></button>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                @foreach ($matrix->groups as $group)
                    <tbody x-show="getVisibleCount('{{ $group->slug }}') > 0">
                        @foreach ($group->rows as $index => $row)
                            <tr class="compare-row bg-white hover:bg-gray-50 transition-colors"
                                x-show="!(hideIdentical && {{ $row->allIdentical ? 'true' : 'false' }})"
                                x-cloak>
                                
                                <template x-if="isFirstVisible('{{ $group->slug }}', {{ $index }})">
                                    <th class="group-header-cell sticky left-0 z-10" :rowspan="getVisibleCount('{{ $group->slug }}')">
                                        {{ $group->name }}
                                    </th>
                                </template>

                                <td class="row-label-cell sticky left-[120px] z-10">
                                    {{ $row->label }}
                                </td>
                                
                                @foreach ($row->values as $value)
                                    <td class="text-center {{ $row->allIdentical ? 'text-gray-500' : 'text-gray-900 font-medium' }}">
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
