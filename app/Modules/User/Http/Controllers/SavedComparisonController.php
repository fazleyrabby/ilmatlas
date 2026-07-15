<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Models\SavedComparison;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SavedComparisonController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'institute_ids' => ['required', 'array', 'min:2', 'max:5'],
            'institute_ids.*' => ['required', 'exists:institutes,uuid'],
        ]);

        SavedComparison::create([
            'user_id' => $request->user()->id,
            'name' => $request->name ?? 'Comparison '.now()->format('Y-m-d H:i'),
            'institute_ids' => $request->institute_ids,
        ]);

        return back()->with('success', 'Comparison saved successfully.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $comparison = $request->user()->savedComparisons()->findOrFail($id);
        $comparison->delete();

        return back()->with('success', 'Saved comparison deleted.');
    }
}
