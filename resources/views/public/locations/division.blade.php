@extends('layouts.public')

@section('title', "$division->name Division — EduBase")

@section('content')
<div class="container-page py-8">
    <div class="mb-6">
        <a href="{{ route('institutes.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-primary-700 hover:text-primary-800">
            <i data-lucide="arrow-left" class="h-4 w-4"></i> All Institutes
        </a>
    </div>

    <span class="text-eyebrow">Division</span>
    <h1 class="section-title mt-1 mb-2">{{ $division->name }} Division</h1>
    <p class="text-text-secondary mb-8">{{ number_format($institutes->total()) }} institutes in this division</p>

    @if($districts->isNotEmpty())
        <div class="mb-10">
            <h2 class="mb-3 text-sm font-semibold text-text-primary">Districts</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($districts as $district)
                    <a href="{{ route('locations.district', $district->slug) }}" class="chip">{{ $district->name }}</a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($institutes as $institute)
            <x-ui.institute-card :institute="$institute" />
        @endforeach
    </div>

    <div class="mt-8">{{ $institutes->links() }}</div>
</div>
@endsection
