@extends('layouts.public')

@section('title', 'Edit School Profile — EduBase')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('portal.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Dashboard</a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-8 shadow-sm">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Edit School Profile</h1>
        <p class="text-sm text-gray-500 mb-6">Modify public information for <strong>{{ $institute->name }}</strong>.</p>

        <form method="POST" action="{{ route('portal.update', $institute) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="motto" class="block text-sm font-medium text-gray-700 mb-1">Motto</label>
                <input type="text" id="motto" name="motto" value="{{ old('motto', $institute->motto) }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                @error('motto')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">About / Description</label>
                <textarea id="description" name="description" rows="5"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('description', $institute->description) }}</textarea>
                @error('description')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="full_address" class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                    <input type="text" id="full_address" name="full_address" value="{{ old('full_address', $institute->full_address) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('full_address')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $institute->postal_code) }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('postal_code')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="google_maps_url" class="block text-sm font-medium text-gray-700 mb-1">Google Maps Link</label>
                <input type="url" id="google_maps_url" name="google_maps_url" value="{{ old('google_maps_url', $institute->google_maps_url) }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                @error('google_maps_url')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="nearby_landmark" class="block text-sm font-medium text-gray-700 mb-1">Nearby Landmark</label>
                <input type="text" id="nearby_landmark" name="nearby_landmark" value="{{ old('nearby_landmark', $institute->nearby_landmark) }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                @error('nearby_landmark')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
                Save Profile Changes
            </button>
        </form>
    </div>
</div>
@endsection
