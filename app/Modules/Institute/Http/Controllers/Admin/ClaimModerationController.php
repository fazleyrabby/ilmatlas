<?php

namespace App\Modules\Institute\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Institute\Models\InstituteClaim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClaimModerationController extends Controller
{
    public function index(Request $request): View
    {
        $claims = InstituteClaim::with(['user', 'institute'])
            ->latest()
            ->paginate(30);

        return view('admin.claims.index', compact('claims'));
    }

    public function approve(Request $request, InstituteClaim $claim): RedirectResponse
    {
        $claim->update([
            'status' => 'approved',
            'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
        ]);

        $claim->institute->update([
            'owner_id' => $claim->user_id,
        ]);

        // Assign editor role to the user so they can login to portal/edit things
        if (! $claim->user->hasRole('editor')) {
            $claim->user->assignRole('editor');
        }

        return back()->with('success', 'Claim approved. User has been mapped as the owner of this institute.');
    }

    public function reject(Request $request, InstituteClaim $claim): RedirectResponse
    {
        $claim->update([
            'status' => 'rejected',
            'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
        ]);

        return back()->with('success', 'Claim rejected successfully.');
    }
}
