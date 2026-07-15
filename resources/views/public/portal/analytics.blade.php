@extends('layouts.public')

@section('title', 'School Traffic Analytics — EduBase')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('portal.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Dashboard</a>
    </div>

    <div class="mb-8 pb-6 border-b">
        <h1 class="text-3xl font-bold text-gray-900">School Profile Traffic Analytics</h1>
        <p class="text-gray-500 mt-1">Performance metrics for <strong>{{ $institute->name }}</strong>.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Profile views -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Total Profile Views</span>
            <div class="mt-4">
                <span class="text-4xl font-extrabold text-indigo-600 block">{{ number_format($viewCount) }}</span>
                <span class="text-xs text-gray-400 mt-1 block">Lifetime organic page views</span>
            </div>
        </div>

        <!-- Comparison additions -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Comparison Inclusions</span>
            <div class="mt-4">
                <span class="text-4xl font-extrabold text-green-600 block">{{ number_format($comparisonCount) }}</span>
                <span class="text-xs text-gray-400 mt-1 block">Added to comparison matrices</span>
            </div>
        </div>

        <!-- Alert subscribers -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Active Watch Subscribers</span>
            <div class="mt-4">
                <span class="text-4xl font-extrabold text-amber-600 block">{{ number_format($alertWatchersCount) }}</span>
                <span class="text-xs text-gray-400 mt-1 block">Users receiving fee & admission updates</span>
            </div>
        </div>
    </div>
</div>
@endsection
