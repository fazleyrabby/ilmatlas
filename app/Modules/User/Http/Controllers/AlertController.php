<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Models\UserAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'institute_id' => ['required', 'exists:institutes,id'],
            'alert_type' => ['required', 'string', 'in:fee_changes,admission_openings'],
        ]);

        UserAlert::updateOrCreate([
            'user_id' => $request->user()->id,
            'institute_id' => $request->institute_id,
            'alert_type' => $request->alert_type,
        ], [
            'is_active' => true,
        ]);

        return back()->with('success', 'Alert subscription created successfully.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $alert = $request->user()->alerts()->findOrFail($id);
        $alert->delete();

        return back()->with('success', 'Alert subscription removed.');
    }
}
