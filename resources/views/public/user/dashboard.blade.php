@extends('layouts.public')

@section('title', 'My Dashboard — EduBase')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">My Dashboard</h1>
            <p class="text-gray-500 mt-1">Manage your favorited institutes, watchlists, and saved comparisons.</p>
        </div>
        <div class="text-right">
            <span class="text-sm font-medium text-gray-900 block">{{ auth()->user()->name }}</span>
            <span class="text-xs text-gray-500 block">{{ auth()->user()->email }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Favorites List -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Bookmarked Favorites</h2>
                @if($favorites->isEmpty())
                    <p class="text-sm text-gray-500 py-4">You haven't bookmarked any favorite institutes yet.</p>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($favorites as $favorite)
                            @if($favorite->institute)
                                <div class="py-4 flex items-center justify-between first:pt-0 last:pb-0">
                                    <div>
                                        <a href="{{ route('institutes.show', $favorite->institute) }}" class="font-semibold text-gray-900 hover:text-indigo-600 block">
                                            {{ $favorite->institute->name }}
                                        </a>
                                        <span class="text-xs text-gray-500">
                                            {{ $favorite->institute->type?->name }} &middot; {{ $favorite->institute->district?->name }}
                                        </span>
                                    </div>
                                    <form method="POST" action="{{ route('favorites.destroy', $favorite->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-900 font-medium">Remove</button>
                                    </form>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Saved Comparisons -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Saved Comparisons</h2>
                @if($savedComparisons->isEmpty())
                    <p class="text-sm text-gray-500 py-4">No saved comparisons found. Build one on the compare page to save it here.</p>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($savedComparisons as $comparison)
                            <div class="py-4 flex items-center justify-between first:pt-0 last:pb-0">
                                <div>
                                    <span class="font-semibold text-gray-900 block">{{ $comparison->name }}</span>
                                    <span class="text-xs text-gray-500">
                                        Includes {{ count($comparison->institute_ids) }} institutes
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('api.compare') }}?slugs={{ implode(',', $comparison->institute_ids) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">View</a>
                                    <form method="POST" action="{{ route('comparisons.destroy', $comparison->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-900 font-medium">Delete</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Alert Notification watchlists -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Email Alerts</h2>
                <p class="text-xs text-gray-500 mb-4">Subscribe to receive email alerts when watchlisted institutes modify fees or release admission circulars.</p>

                @if($alerts->isEmpty())
                    <p class="text-sm text-gray-500 py-4">No active alert subscriptions found.</p>
                @else
                    <div class="space-y-4">
                        @foreach($alerts as $alert)
                            @if($alert->institute)
                                <div class="p-3 bg-gray-50 rounded-lg flex items-start justify-between">
                                    <div class="min-w-0">
                                        <span class="font-semibold text-sm text-gray-900 truncate block">{{ $alert->institute->name }}</span>
                                        <span class="text-xs text-gray-500 capitalize">
                                            Alert Type: {{ str_replace('_', ' ', $alert->alert_type) }}
                                        </span>
                                    </div>
                                    <form method="POST" action="{{ route('alerts.destroy', $alert->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:text-red-900 font-medium ml-2">Unwatch</button>
                                    </form>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
