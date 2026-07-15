<?php

namespace App\Modules\Fee\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fee\Models\FeeStructure;
use App\Modules\Institute\Models\Institute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommunityFeeController extends Controller
{
    public function store(Request $request, Institute $institute): RedirectResponse
    {
        $data = $request->validate([
            'fee_type_id' => ['required', 'exists:fee_types,id'],
            'academic_session' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', 'string', 'in:one_time,monthly,quarterly,half_yearly,yearly,per_unit'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        FeeStructure::create([
            'uuid' => (string) Str::uuid(),
            'institute_id' => $institute->id,
            'fee_type_id' => $data['fee_type_id'],
            'academic_session' => $data['academic_session'],
            'amount' => $data['amount'],
            'frequency' => $data['frequency'],
            'verification_status' => 'community_reported',
            'moderation_status' => 'pending_review',
            'source_type' => 'community',
            'source_notes' => $data['notes'],
            'is_published' => false,
        ]);

        return back()->with('success', 'Thank you! Your fee submission has been queued for moderation review.');
    }
}
