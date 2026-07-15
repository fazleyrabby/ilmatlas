@extends('layouts.public')

@section('title', $query ? "Search: $query — EduBase" : 'Search — EduBase')

@section('content')
<div class="container-reading py-10">
    <span class="text-eyebrow">Search</span>
    <h1 class="section-title mt-1 mb-5">Search Institutes</h1>

    <form method="GET" action="{{ route('search') }}" class="mb-8 max-w-2xl">
        <div class="search-shell">
            <i data-lucide="search" class="text-text-muted"></i>
            <input type="text" name="q" value="{{ $query }}" placeholder="Search by name, location, EIIN…" class="search-input" autofocus>
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    @if($query)
        <p class="text-sm text-text-muted mb-6">
            <span class="font-semibold text-text-primary">{{ $results->total() }}</span> result(s) for “{{ $query }}”
        </p>
    @endif

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        @forelse($results as $institute)
            <x-ui.institute-card :institute="$institute" />
        @empty
            @if($query)
                <div class="col-span-full">
                    <div class="card flex flex-col items-center gap-3 py-16 text-center">
                        <i data-lucide="search-x" class="h-10 w-10 text-text-muted"></i>
                        <p class="text-text-secondary">No institutes found matching your search.</p>
                        <a href="{{ route('institutes.index') }}" class="btn btn-secondary btn-sm">Browse all institutes</a>
                    </div>
                </div>
            @endif
        @endforelse
    </div>

    @if(isset($results) && method_exists($results, 'links'))
        <div class="mt-8">{{ $results->links() }}</div>
    @endif
</div>
@endsection
