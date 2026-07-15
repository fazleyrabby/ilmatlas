@extends('layouts.admin')

@section('title', 'Fee Types — ILMATLAS Admin')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Fee Types</h1>
    <p class="text-sm text-gray-500 mt-1">Manage fee categories and types</p>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="bg-gray-50 text-left text-sm font-medium text-gray-500">
                <th class="px-6 py-3">Name</th>
                <th class="px-6 py-3">Slug</th>
                <th class="px-6 py-3">Category</th>
                <th class="px-6 py-3">Active</th>
                <th class="px-6 py-3">Fees</th>
                <th class="px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($types as $type)
                <tr class="text-sm">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $type->name }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $type->slug }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            @if($type->fee_category === 'recurring') bg-blue-100 text-blue-700
                            @elseif($type->fee_category === 'one_time') bg-purple-100 text-purple-700
                            @elseif($type->fee_category === 'student_expense') bg-orange-100 text-orange-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ str_replace('_', ' ', $type->fee_category) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">{{ $type->is_active ? 'Yes' : 'No' }}</td>
                    <td class="px-6 py-4">{{ $type->fee_structures_count }}</td>
                    <td class="px-6 py-4 flex gap-2">
                        <button onclick="editType({{ $type->id }}, '{{ $type->name }}', '{{ $type->slug }}', '{{ $type->fee_category }}')" class="text-indigo-600 hover:text-indigo-900 text-xs">Edit</button>
                        <form method="POST" action="{{ route('admin.fees.types.destroy', $type) }}" onsubmit="return confirm('Delete this fee type?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 text-xs">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-8 bg-white rounded-xl border border-gray-200 p-6 max-w-xl">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">New Fee Type</h2>
    <form method="POST" action="{{ route('admin.fees.types.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" class="w-full rounded-lg border-gray-300" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Slug</label>
            <input type="text" name="slug" class="w-full rounded-lg border-gray-300" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Category</label>
            <select name="fee_category" class="w-full rounded-lg border-gray-300" required>
                <option value="one_time">One Time</option>
                <option value="recurring">Recurring</option>
                <option value="student_expense">Student Expense</option>
                <option value="optional_service">Optional Service</option>
            </select>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Create</button>
        </div>
    </form>
</div>

<div id="editModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Fee Type</h3>
        <form method="POST" id="editForm" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" id="editName" class="w-full rounded-lg border-gray-300" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" name="slug" id="editSlug" class="w-full rounded-lg border-gray-300" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <select name="fee_category" id="editCategory" class="w-full rounded-lg border-gray-300" required>
                    <option value="one_time">One Time</option>
                    <option value="recurring">Recurring</option>
                    <option value="student_expense">Student Expense</option>
                    <option value="optional_service">Optional Service</option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeEdit()" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
function editType(id, name, slug, category) {
    document.getElementById('editName').value = name;
    document.getElementById('editSlug').value = slug;
    document.getElementById('editCategory').value = category;
    document.getElementById('editForm').action = '/admin/fees/types/' + id;
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');
}

function closeEdit() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
}
</script>
@endsection
