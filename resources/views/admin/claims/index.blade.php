@extends('layouts.admin')

@section('title', 'Institute Claims Moderation — EduBase Admin')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Institute Claims Queue</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $claims->total() }} claims in queue</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="bg-gray-50 text-left text-sm font-medium text-gray-500">
                <th class="px-6 py-3">Institute</th>
                <th class="px-6 py-3">Claiming User</th>
                <th class="px-6 py-3">Proof Document</th>
                <th class="px-6 py-3">Notes</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($claims as $claim)
                <tr class="text-sm">
                    <td class="px-6 py-4">
                        <span class="font-medium text-gray-900">{{ $claim->institute?->name }}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        {{ $claim->user?->name }}<br>
                        <span class="text-xs text-gray-400">{{ $claim->user?->email }}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        @if($claim->proof_url)
                            <a href="{{ asset('storage/' . $claim->proof_url) }}" target="_blank" class="text-indigo-600 hover:underline font-medium text-xs">View Document</a>
                        @else
                            <span class="text-xs text-gray-400">No doc attached</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-600 max-w-sm whitespace-normal break-words">{{ $claim->notes }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            @if($claim->status === 'approved') bg-green-100 text-green-700
                            @elseif($claim->status === 'pending') bg-amber-100 text-amber-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ ucfirst($claim->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($claim->status === 'pending')
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('admin.claims.approve', $claim) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-green-600 hover:text-green-800">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.claims.reject', $claim) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800">Reject</button>
                                </form>
                            </div>
                        @else
                            <span class="text-xs text-gray-400">Moderated by {{ $claim->moderator?->name ?? 'System' }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        No claims found in queue.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $claims->links() }}
</div>
@endsection
