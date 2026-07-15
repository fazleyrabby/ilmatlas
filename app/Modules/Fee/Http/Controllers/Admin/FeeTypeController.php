<?php

namespace App\Modules\Fee\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Fee\Models\FeeType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FeeTypeController extends Controller
{
    public function index(): View
    {
        $types = FeeType::withCount('feeStructures')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.fees.types', compact('types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:fee_types,slug',
            'fee_category' => 'required|string|in:one_time,recurring,student_expense,optional_service',
            'description' => 'nullable|string|max:1000',
        ]);

        FeeType::create(['uuid' => (string) Str::uuid()] + $data + ['sort_order' => FeeType::max('sort_order') + 1]);

        return redirect()->route('admin.fees.types.index')->with('success', 'Fee type created.');
    }

    public function update(Request $request, FeeType $type): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:fee_types,slug,'.$type->id,
            'fee_category' => 'required|string|in:one_time,recurring,student_expense,optional_service',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $type->update($data);

        return redirect()->route('admin.fees.types.index')->with('success', 'Fee type updated.');
    }

    public function destroy(FeeType $type): RedirectResponse
    {
        $type->delete();

        return redirect()->route('admin.fees.types.index')->with('success', 'Fee type deleted.');
    }
}
