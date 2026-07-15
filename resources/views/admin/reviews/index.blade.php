@extends('layouts.admin')

@section('title', 'Review Moderation Queue — EduBase Admin')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">User Reviews Moderation</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $reviews->total() }} reviews in queue</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200 text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="bg-gray-50 text-left text-sm font-medium text-gray-500">
                <th class="px-6 py-3">Institute</th>
                <th class="px-6 py-3">User</th>
                <th class="px-6 py-3">Rating</th>
                <th class="px-6 py-3">Comment</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($reviews as $rev)
                <tr class="text-sm">
                    <td class="px-6 py-4">
                        <span class="font-medium text-gray-900">{{ $rev->institute?->name }}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $rev->user?->name }}<br><span class="text-xs text-gray-400">{{ $rev->user?->email }}</span></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 font-bold text-xs">{{ $rev->rating }} / 5</span>
                    </td>
                    <td class="px-6 py-4 text-gray-600 max-w-sm whitespace-normal break-words">{{ $rev->comment }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            @if($rev->moderation_status === 'approved') bg-green-100 text-green-700
                            @elseif($rev->moderation_status === 'pending_review') bg-amber-100 text-amber-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ str_replace('_', ' ', $rev->moderation_status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($rev->moderation_status === 'pending_review')
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('admin.reviews.approve', $rev) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-green-600 hover:text-green-800">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.reviews.reject', $rev) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800">Reject</button>
                                </form>
                            </div>
                        @else
                            <span class="text-xs text-gray-400">Moderated by {{ $rev->moderator?->name ?? 'System' }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        No reviews found in queue.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $reviews->links() }}
</div>
@endsection
