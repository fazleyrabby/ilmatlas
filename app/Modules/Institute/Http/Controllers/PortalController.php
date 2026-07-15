<?php

namespace App\Modules\Institute\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Institute\Models\Institute;
use App\Modules\User\Models\UserAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    /**
     * List owned institutes.
     */
    public function index(Request $request): View
    {
        $institutes = $request->user()->claimedInstitutes()->with(['type', 'district'])->get();

        return view('public.portal.index', compact('institutes'));
    }

    /**
     * Edit portal institute.
     */
    public function edit(Request $request, Institute $institute): View
    {
        abort_unless($institute->owner_id === $request->user()->id, 403);

        return view('public.portal.edit', compact('institute'));
    }

    /**
     * Update portal institute.
     */
    public function update(Request $request, Institute $institute): RedirectResponse
    {
        abort_unless($institute->owner_id === $request->user()->id, 403);

        $data = $request->validate([
            'description' => ['nullable', 'string', 'max:2000'],
            'motto' => ['nullable', 'string', 'max:255'],
            'full_address' => ['nullable', 'string', 'max:500'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'google_maps_url' => ['nullable', 'url', 'max:1000'],
            'nearby_landmark' => ['nullable', 'string', 'max:255'],
        ]);

        $institute->update($data);

        return redirect()->route('portal.index')->with('success', 'Institute profile updated successfully.');
    }

    /**
     * Analytics.
     */
    public function analytics(Request $request, Institute $institute): View
    {
        abort_unless($institute->owner_id === $request->user()->id, 403);

        $viewCount = $institute->view_count;
        $comparisonCount = $institute->comparison_count;
        $alertWatchersCount = UserAlert::where('institute_id', $institute->id)->where('is_active', true)->count();

        return view('public.portal.analytics', compact('institute', 'viewCount', 'comparisonCount', 'alertWatchersCount'));
    }
}
