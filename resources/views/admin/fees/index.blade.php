@extends('layouts.admin')

@section('title', 'Fees — EduBase Admin')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Fee Records</h1>
        <p class="text-sm text-gray-500 mt-1">
            {{ $fees->total() }} records
            @if($pendingCount > 0)
                &middot; <span class="text-amber-600 font-medium">{{ $pendingCount }} pending review</span>
            @endif
        </p>
    </div>
    <a href="{{ route('admin.fees.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">New Fee</a>
</div>

<form method="GET" class="mb-6 flex gap-3 flex-wrap">
    <select name="institute_id" class="rounded-lg border-gray-300 text-sm">
        <option value="">All Institutes</option>
        @foreach($institutes as $inst)
            <option value="{{ $inst->id }}" @selected(request('institute_id') == $inst->id)>{{ $inst->name }}</option>
        @endforeach
    </select>
    <select name="moderation_status" class="rounded-lg border-gray-300 text-sm">
        <option value="">All Status</option>
        <option value="pending_review" @selected(request('moderation_status') === 'pending_review')>Pending Review</option>
        <option value="approved" @selected(request('moderation_status') === 'approved')>Approved</option>
        <option value="rejected" @selected(request('moderation_status') === 'rejected')>Rejected</option>
        <option value="needs_revision" @selected(request('moderation_status') === 'needs_revision')>Needs Revision</option>
    </select>
    <select name="fee_type_id" class="rounded-lg border-gray-300 text-sm">
        <option value="">All Types</option>
        @foreach($types as $type)
            <option value="{{ $type->id }}" @selected(request('fee_type_id') == $type->id)>{{ $type->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Filter</button>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="bg-gray-50 text-left text-sm font-medium text-gray-500">
                <th class="px-6 py-3">Institute</th>
                <th class="px-6 py-3">Fee Type</th>
                <th class="px-6 py-3">Amount</th>
                <th class="px-6 py-3">Frequency</th>
                <th class="px-6 py-3">Session</th>
                <th class="px-6 py-3">Moderation</th>
                <th class="px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($fees as $fee)
                <tr class="text-sm @if($fee->moderation_status === 'pending_review') bg-amber-50 @endif">
                    <td class="px-6 py-4">
                        @if($fee->institute)
                            <a href="{{ route('admin.institutes.edit', $fee->institute) }}" class="text-indigo-600 hover:underline font-medium">{{ $fee->institute->name }}</a>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $fee->feeType?->name }}</td>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ number_format($fee->amount, 0) }} {{ $fee->currency }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ str_replace('_', ' ', $fee->frequency) }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $fee->academic_session }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            @if($fee->moderation_status === 'approved') bg-green-100 text-green-700
                            @elseif($fee->moderation_status === 'pending_review') bg-amber-100 text-amber-700
                            @elseif($fee->moderation_status === 'rejected') bg-red-100 text-red-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ str_replace('_', ' ', $fee->moderation_status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.fees.edit', $fee) }}" class="text-indigo-600 hover:text-indigo-900 text-xs">Edit</a>
                            <a href="{{ route('admin.fees.history', $fee) }}" class="text-gray-500 hover:text-gray-700 text-xs">History</a>
                            @if($fee->moderation_status === 'pending_review')
                                <form method="POST" action="{{ route('admin.fees.moderate', $fee) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <button class="text-green-600 hover:text-green-800 text-xs font-medium">Approve</button>
                                </form>
                                <button onclick="showReject({{ $fee->id }})" class="text-red-500 hover:text-red-700 text-xs">Reject</button>
                            @endif
                            <form method="POST" action="{{ route('admin.fees.destroy', $fee) }}" onsubmit="return confirm('Delete?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:text-red-700 text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $fees->links() }}</div>

<div id="rejectModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Fee</h3>
        <form method="POST" id="rejectForm" class="space-y-4">
            @csrf
            <input type="hidden" name="action" value="reject">
            <div>
                <label class="block text-sm font-medium text-gray-700">Reason (optional)</label>
                <textarea name="reason" rows="3" class="w-full rounded-lg border-gray-300"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeReject()" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">Reject</button>
            </div>
        </form>
    </div>
</div>

<script>
function showReject(id) {
    document.getElementById('rejectForm').action = '/admin/fees/' + id + '/moderate';
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectModal').classList.add('flex');
}

function closeReject() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectModal').classList.remove('flex');
}
</script>
@endsection
