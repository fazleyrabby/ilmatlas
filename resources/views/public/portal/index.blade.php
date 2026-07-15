@extends('layouts.public')

@section('title', 'School Portal Dashboard — EduBase')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-8 border-b pb-6">
        <h1 class="text-3xl font-bold text-gray-900">School Portal Dashboard</h1>
        <p class="text-gray-500 mt-1">Manage claimed school profiles, edit descriptions, and monitor traffic analytics.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($institutes->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <p class="text-base font-semibold mb-2">No school profiles claimed yet.</p>
                <p class="text-sm mb-4">To claim ownership, find your school on the public directory list and click "Claim this School".</p>
                <a href="{{ route('institutes.index') }}" class="inline-flex px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
                    Browse Institutes Directory
                </a>
            </div>
        @else
            <div class="divide-y divide-gray-200">
                @foreach($institutes as $inst)
                    <div class="p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $inst->name }}</h2>
                            <p class="text-sm text-gray-500">{{ $inst->type?->name }} &middot; {{ $inst->district?->name }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('portal.edit', $inst) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium rounded-lg transition">
                                Edit Profile
                            </a>
                            <a href="{{ route('portal.analytics', $inst) }}" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-sm font-medium rounded-lg transition">
                                View Analytics
                            </a>
                            <a href="{{ route('institutes.show', $inst) }}" class="text-sm font-medium text-gray-500 hover:text-gray-800">
                                View Public Profile
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
