@extends('layouts.public')

@section('title', 'Institutes — ILMATLAS')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Educational Institutes</h1>
    <p class="text-gray-500 mb-8">Browse and compare schools, madrasas, and colleges across Bangladesh.</p>

    <div class="flex gap-4 mb-8 flex-wrap">
        <form method="GET" class="flex gap-4 flex-wrap items-center">
            <select name="type" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                <option value="">All Types</option>
                @foreach($types as $type)
                    <option value="{{ $type->slug }}" @selected(request('type') === $type->slug)>{{ $type->name }}</option>
                @endforeach
            </select>
            <select name="category" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="curriculum" class="rounded-lg border-gray-300 text-sm" onchange="this.form.submit()">
                <option value="">All Curriculums</option>
                @foreach($curriculums as $c)
                    <option value="{{ $c->slug }}" @selected(request('curriculum') === $c->slug)>{{ $c->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($institutes as $institute)
            <div class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <a href="{{ route('institutes.show', $institute) }}">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-lg flex-shrink-0">
                            {{ substr($institute->name, 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="font-semibold text-gray-900 truncate">{{ $institute->name }}</h2>
                            <p class="text-sm text-gray-500">{{ $institute->type?->name }} &middot; {{ $institute->district?->name }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between text-sm">
                        <span class="text-gray-500">{{ $institute->upazila?->name }}</span>
                        <span class="font-medium text-gray-900">{{ number_format($institute->estimated_monthly_fee, 0) }} BDT</span>
                    </div>
                </a>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <button
                        onclick="event.preventDefault(); compareAdd('{{ $institute->uuid }}', '{{ $institute->slug }}', '{{ addslashes($institute->name) }}')"
                        class="w-full text-center text-sm text-indigo-600 hover:text-indigo-800 font-medium compare-btn"
                        data-uuid="{{ $institute->uuid }}"
                    >
                        + Add to Compare
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                No institutes found matching your criteria.
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $institutes->links() }}
    </div>
</div>
@endsection
