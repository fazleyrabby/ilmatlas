@extends('layouts.admin')

@section('title', 'Fee History — ILMATLAS Admin')

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.fees.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to fees</a>
    <h1 class="text-2xl font-bold text-gray-900 mt-2">Fee History</h1>
    <p class="text-sm text-gray-500 mt-1">
        {{ $fee->institute?->name }} &middot; {{ $fee->feeType?->name }} &middot; {{ number_format($fee->amount, 0) }} {{ $fee->currency }}
    </p>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    @if($fee->histories->isNotEmpty())
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left text-sm font-medium text-gray-500">
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3">Previous</th>
                    <th class="px-6 py-3">New</th>
                    <th class="px-6 py-3">Change</th>
                    <th class="px-6 py-3">Session</th>
                    <th class="px-6 py-3">Reason</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm">
                @foreach($fee->histories as $history)
                    <tr>
                        <td class="px-6 py-4 text-gray-500">{{ $history->effective_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4">{{ $history->previous_amount ? number_format($history->previous_amount, 0) : '—' }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ number_format($history->new_amount, 0) }}</td>
                        <td class="px-6 py-4">
                            @if($history->percentage_change)
                                <span class="@if($history->percentage_change > 0) text-red-600 @elseif($history->percentage_change < 0) text-green-600 @endif">
                                    {{ $history->percentage_change > 0 ? '+' : '' }}{{ $history->percentage_change }}%
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $history->academic_session ?? '—' }}</td>
                        <td class="px-6 py-4 text-gray-500 max-w-xs truncate">{{ $history->change_reason ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="px-6 py-12 text-center text-gray-500">
            No history records for this fee.
        </div>
    @endif
</div>
@endsection
