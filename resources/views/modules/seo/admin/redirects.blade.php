@extends('layouts.admin')

@section('title', 'Redirects — Admin')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Redirects</h1>

    <form method="POST" action="{{ route('admin.seo.redirects.store') }}" class="bg-white rounded-lg shadow p-6 mb-8">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">From Path</label>
                <input type="text" name="from_path" required class="w-full border rounded px-3 py-2" placeholder="/old-path">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">To Path</label>
                <input type="text" name="to_path" required class="w-full border rounded px-3 py-2" placeholder="/new-path">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status Code</label>
                <select name="status_code" class="w-full border rounded px-3 py-2">
                    <option value="301">301 (Permanent)</option>
                    <option value="302">302 (Temporary)</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Redirect</button>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left">
                    <th class="px-4 py-3 font-medium">From</th>
                    <th class="px-4 py-3 font-medium">To</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($redirects as $redirect)
                    <tr class="border-t">
                        <td class="px-4 py-3 font-mono text-sm">{{ $redirect->from_path }}</td>
                        <td class="px-4 py-3 font-mono text-sm">{{ $redirect->to_path }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded {{ $redirect->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $redirect->status_code }} {{ $redirect->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.seo.redirects.destroy', $redirect) }}" onsubmit="return confirm('Delete this redirect?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline text-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">No redirects yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $redirects->links() }}
    </div>
</div>
@endsection
