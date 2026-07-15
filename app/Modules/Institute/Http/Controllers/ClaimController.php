<?php

namespace App\Modules\Institute\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Institute\Models\Institute;
use App\Modules\Institute\Models\InstituteClaim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function store(Request $request, Institute $institute): RedirectResponse
    {
        $request->validate([
            'notes' => ['required', 'string', 'min:10', 'max:2000'],
            'proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // 5MB max
        ]);

        // Check if already claimed/verified
        if ($institute->owner_id !== null) {
            return back()->withErrors(['notes' => 'This institute has already been claimed by a verified owner.']);
        }

        // Check if user has an active pending claim for this school
        $exists = InstituteClaim::where('user_id', $request->user()->id)
            ->where('institute_id', $institute->id)
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return back()->withErrors(['notes' => 'You already have a pending claim for this school.']);
        }

        // Handle proof upload
        $proofUrl = null;
        if ($request->hasFile('proof')) {
            $proofUrl = $request->file('proof')->store('claims', 'public');
        }

        InstituteClaim::create([
            'user_id' => $request->user()->id,
            'institute_id' => $institute->id,
            'proof_url' => $proofUrl,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Your ownership claim has been submitted successfully and is under review.');
    }
}
