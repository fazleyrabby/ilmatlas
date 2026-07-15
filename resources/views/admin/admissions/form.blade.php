@extends('layouts.admin')

@section('title', $circular->exists ? 'Edit Circular — ILMATLAS Admin' : 'New Circular — ILMATLAS Admin')

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.admissions.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to admissions</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $circular->exists ? 'Edit Admission Circular' : 'New Admission Circular' }}</h1>
</div>

<form method="POST" action="{{ $circular->exists ? route('admin.admissions.update', $circular) : route('admin.admissions.store') }}" class="max-w-2xl bg-white rounded-xl border border-gray-200 p-6 space-y-4">
    @csrf
    @if($circular->exists) @method('PUT') @endif

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Institute</label>
        <select name="institute_id" class="w-full rounded-lg border-gray-300" required>
            <option value="">Select Institute</option>
            @foreach($institutes as $inst)
                <option value="{{ $inst->id }}" @selected(old('institute_id', $selectedInstitute?->id) == $inst->id)>{{ $inst->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Admission Session</label>
        <select name="admission_session_id" class="w-full rounded-lg border-gray-300">
            <option value="">— None —</option>
            @foreach($sessions as $session)
                <option value="{{ $session->id }}" @selected(old('admission_session_id', $circular->admission_session_id) == $session->id)>{{ $session->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
        <input type="text" name="title" value="{{ old('title', $circular->title) }}" class="w-full rounded-lg border-gray-300" placeholder="e.g., Class 1 Admission 2026">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select name="admission_status" class="w-full rounded-lg border-gray-300" required>
            <option value="upcoming" @selected(old('admission_status', $circular->admission_status) === 'upcoming')>Upcoming</option>
            <option value="open" @selected(old('admission_status', $circular->admission_status) === 'open')>Open</option>
            <option value="closing_soon" @selected(old('admission_status', $circular->admission_status) === 'closing_soon')>Closing Soon</option>
            <option value="closed" @selected(old('admission_status', $circular->admission_status) === 'closed')>Closed</option>
            <option value="waitlist" @selected(old('admission_status', $circular->admission_status) === 'waitlist')>Waitlist</option>
        </select>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Application Start</label>
            <input type="date" name="application_start_date" value="{{ old('application_start_date', $circular->application_start_date?->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Application End</label>
            <input type="date" name="application_end_date" value="{{ old('application_end_date', $circular->application_end_date?->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300">
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="flex items-center gap-2">
            <input type="checkbox" name="admission_test_required" value="1" id="test_required" class="rounded border-gray-300" @checked(old('admission_test_required', $circular->admission_test_required))>
            <label for="test_required" class="text-sm text-gray-700">Test Required</label>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="interview_required" value="1" id="interview_required" class="rounded border-gray-300" @checked(old('interview_required', $circular->interview_required))>
            <label for="interview_required" class="text-sm text-gray-700">Interview</label>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="online_application_available" value="1" id="online_app" class="rounded border-gray-300" @checked(old('online_application_available', $circular->online_application_available))>
            <label for="online_app" class="text-sm text-gray-700">Online App</label>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Application URL</label>
        <input type="url" name="application_url" value="{{ old('application_url', $circular->application_url) }}" class="w-full rounded-lg border-gray-300" placeholder="https://">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Documents Required</label>
        <textarea name="documents_required" rows="3" class="w-full rounded-lg border-gray-300">{{ old('documents_required', $circular->documents_required) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Eligibility Criteria</label>
        <textarea name="eligibility_criteria" rows="3" class="w-full rounded-lg border-gray-300">{{ old('eligibility_criteria', $circular->eligibility_criteria) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Info</label>
        <input type="text" name="contact_info" value="{{ old('contact_info', $circular->contact_info) }}" class="w-full rounded-lg border-gray-300">
    </div>

    <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
        <a href="{{ route('admin.admissions.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            {{ $circular->exists ? 'Update' : 'Create' }}
        </button>
    </div>
</form>
@endsection
