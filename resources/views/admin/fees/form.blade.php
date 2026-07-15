@extends('layouts.admin')

@section('title', $fee->exists ? 'Edit Fee — EduBase Admin' : 'New Fee — EduBase Admin')

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.fees.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to fees</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $fee->exists ? 'Edit Fee Record' : 'New Fee Record' }}</h1>
</div>

<form method="POST" action="{{ $fee->exists ? route('admin.fees.update', $fee) : route('admin.fees.store') }}" class="max-w-2xl bg-white rounded-xl border border-gray-200 p-6 space-y-4">
    @csrf
    @if($fee->exists) @method('PUT') @endif

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
        <label class="block text-sm font-medium text-gray-700 mb-1">Fee Type</label>
        <select name="fee_type_id" class="w-full rounded-lg border-gray-300" required>
            <option value="">Select Type</option>
            @foreach($types as $type)
                <option value="{{ $type->id }}" @selected(old('fee_type_id', $fee->fee_type_id) == $type->id)>{{ $type->name }} ({{ $type->fee_category }})</option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
            <input type="number" step="0.01" name="amount" value="{{ old('amount', $fee->amount) }}" class="w-full rounded-lg border-gray-300" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
            <input type="text" name="currency" value="{{ old('currency', $fee->currency ?? 'BDT') }}" class="w-full rounded-lg border-gray-300" maxlength="3">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
            <select name="frequency" class="w-full rounded-lg border-gray-300" required>
                <option value="monthly" @selected(old('frequency', $fee->frequency) === 'monthly')>Monthly</option>
                <option value="quarterly" @selected(old('frequency', $fee->frequency) === 'quarterly')>Quarterly</option>
                <option value="half_yearly" @selected(old('frequency', $fee->frequency) === 'half_yearly')>Half Yearly</option>
                <option value="yearly" @selected(old('frequency', $fee->frequency) === 'yearly')>Yearly</option>
                <option value="one_time" @selected(old('frequency', $fee->frequency) === 'one_time')>One Time</option>
                <option value="per_unit" @selected(old('frequency', $fee->frequency) === 'per_unit')>Per Unit</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Academic Session</label>
            <input type="text" name="academic_session" value="{{ old('academic_session', $fee->academic_session) }}" class="w-full rounded-lg border-gray-300" required>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Grade Range Start</label>
            <input type="text" name="grade_range_start" value="{{ old('grade_range_start', $fee->grade_range_start === 'all' ? '' : $fee->grade_range_start) }}" class="w-full rounded-lg border-gray-300" placeholder="e.g., Class 1">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Grade Range End</label>
            <input type="text" name="grade_range_end" value="{{ old('grade_range_end', $fee->grade_range_end === 'all' ? '' : $fee->grade_range_end) }}" class="w-full rounded-lg border-gray-300" placeholder="e.g., Class 5">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Label</label>
        <input type="text" name="unit_label" value="{{ old('unit_label', $fee->unit_label) }}" class="w-full rounded-lg border-gray-300" placeholder="e.g., per credit, per subject">
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_negotiable" value="0">
        <input type="checkbox" name="is_negotiable" value="1" id="is_negotiable" class="rounded border-gray-300" @checked(old('is_negotiable', $fee->is_negotiable))>
        <label for="is_negotiable" class="text-sm text-gray-700">Negotiable</label>
    </div>

    @if(!$fee->exists)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Moderation Status</label>
            <select name="moderation_status" class="w-full rounded-lg border-gray-300">
                <option value="approved">Approved (published immediately)</option>
                <option value="pending_review">Pending Review</option>
            </select>
        </div>
    @endif

    <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
        <a href="{{ route('admin.fees.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            {{ $fee->exists ? 'Update' : 'Create' }}
        </button>
    </div>
</form>
@endsection
