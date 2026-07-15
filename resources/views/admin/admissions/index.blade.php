@extends('layouts.admin')

@section('title', 'Admissions — EduBase Admin')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Admission Circulars</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $circulars->total() }} circulars</p>
    </div>
    <a href="{{ route('admin.admissions.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">New Circular</a>
</div>

<form method="GET" class="mb-6 flex gap-3 flex-wrap">
    <select name="institute_id" class="rounded-lg border-gray-300 text-sm">
        <option value="">All Institutes</option>
        @foreach($institutes as $inst)
            <option value="{{ $inst->id }}" @selected(request('institute_id') == $inst->id)>{{ $inst->name }}</option>
        @endforeach
    </select>
    <select name="admission_status" class="rounded-lg border-gray-300 text-sm">
        <option value="">All Status</option>
        <option value="upcoming" @selected(request('admission_status') === 'upcoming')>Upcoming</option>
        <option value="open" @selected(request('admission_status') === 'open')>Open</option>
        <option value="closing_soon" @selected(request('admission_status') === 'closing_soon')>Closing Soon</option>
        <option value="closed" @selected(request('admission_status') === 'closed')>Closed</option>
        <option value="waitlist" @selected(request('admission_status') === 'waitlist')>Waitlist</option>
    </select>
    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Filter</button>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="bg-gray-50 text-left text-sm font-medium text-gray-500">
                <th class="px-6 py-3">Institute</th>
                <th class="px-6 py-3">Title</th>
                <th class="px-6 py-3">Session</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Dates</th>
                <th class="px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($circulars as $circular)
                <tr class="text-sm">
                    <td class="px-6 py-4">
                        @if($circular->institute)
                            <a href="{{ route('admin.institutes.edit', $circular->institute) }}" class="text-indigo-600 hover:underline font-medium">{{ $circular->institute->name }}</a>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-600 max-w-xs truncate">{{ $circular->title ?? '—' }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $circular->session?->name ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            @if($circular->admission_status === 'open') bg-green-100 text-green-700
                            @elseif($circular->admission_status === 'upcoming') bg-blue-100 text-blue-700
                            @elseif($circular->admission_status === 'closing_soon') bg-amber-100 text-amber-700
                            @elseif($circular->admission_status === 'closed') bg-gray-100 text-gray-700
                            @else bg-purple-100 text-purple-700 @endif">
                            {{ str_replace('_', ' ', $circular->admission_status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                        @if($circular->application_start_date)
                            {{ $circular->application_start_date->format('M d') }}
                            @if($circular->application_end_date)
                                - {{ $circular->application_end_date->format('M d, Y') }}
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4 flex gap-2">
                        <a href="{{ route('admin.admissions.edit', $circular) }}" class="text-indigo-600 hover:text-indigo-900 text-xs">Edit</a>
                        <form method="POST" action="{{ route('admin.admissions.destroy', $circular) }}" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 text-xs">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">No admission circulars found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $circulars->links() }}</div>
@endsection
