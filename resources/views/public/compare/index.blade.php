@extends('layouts.public')

@section('title', 'Compare: '.implode(' vs ', array_map(fn ($i) => $i->name, $matrix->institutes)).' — ILMATLAS')
@section('meta_description', 'Side-by-side comparison of '.implode(', ', array_map(fn ($i) => $i->name, $matrix->institutes)).'. Compare fees, curriculum, facilities, location, and more.')
@push('styles')
<link rel="canonical" href="{{ url()->current() }}">
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Compare: {{ implode(' vs ', array_map(fn ($i) => $i->name, $matrix->institutes)) }}",
    "description": "Side-by-side comparison of educational institutions in Bangladesh.",
    "about": {
        "@type": "ItemList",
        "itemListElement": [
            @foreach($matrix->institutes as $idx => $i)
            {
                "@type": "ListItem",
                "position": {{ $idx + 1 }},
                "item": {
                    "@type": "EducationalOrganization",
                    "name": "{{ $i->name }}",
                    "url": "{{ route('institutes.show', $i) }}"
                }
            }@if(!$loop->last),@endif
            @endforeach
        ]
    }
}
</script>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Compare Institutes</h1>
        <div class="flex items-center gap-2">
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input type="checkbox" id="hideIdentical" checked onchange="toggleIdentical()" class="rounded border-gray-300">
                Hide identical rows
            </label>
            <button onclick="window.print()" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                Print
            </button>
            <button onclick="copyShareUrl()" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                Share
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse bg-white rounded-lg shadow-sm" id="comparisonTable">
            <thead>
                <tr>
                    <th class="sticky left-0 bg-gray-50 z-10 min-w-[180px] p-3 text-left text-sm font-semibold text-gray-900 border-b border-gray-200"></th>
                    @foreach ($matrix->institutes as $institute)
                        <th class="min-w-[220px] p-3 text-center border-b border-gray-200 bg-gray-50">
                            <a href="{{ route('institutes.show', $institute) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm">
                                {{ $institute->name }}
                            </a>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($matrix->groups as $group)
                    <tr class="bg-gray-100">
                        <td colspan="{{ count($matrix->institutes) + 1 }}" class="p-2 px-3 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            {{ $group->name }}
                        </td>
                    </tr>
                    @foreach ($group->rows as $row)
                        <tr class="compare-row {{ $row->allIdentical ? 'identical' : 'different' }} border-b border-gray-100 hover:bg-gray-50">
                            <td class="sticky left-0 bg-white p-3 text-sm font-medium text-gray-700 border-r border-gray-100">
                                {{ $row->label }}
                            </td>
                            @foreach ($row->values as $value)
                                <td class="p-3 text-sm text-center {{ $row->allIdentical ? 'text-gray-500' : 'text-gray-900 font-medium' }}">
                                    {{ $value }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
function toggleIdentical() {
    const hide = document.getElementById('hideIdentical').checked;
    document.querySelectorAll('.compare-row.identical').forEach(el => {
        el.style.display = hide ? 'none' : '';
    });
}

function copyShareUrl() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Comparison URL copied to clipboard!');
    });
}
</script>
@endpush

@push('styles')
<style>
@media print {
    nav, footer, .flex.items-center { display: none !important; }
    .overflow-x-auto { overflow: visible !important; }
    table { box-shadow: none !important; }
    th.sticky { position: static !important; }
}
</style>
@endpush
@endsection
