<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        // Eager load favorites, savedComparisons, and alerts with their institutes
        $favorites = $user->favorites()->with('institute.type', 'institute.district')->latest()->get();
        $savedComparisons = $user->savedComparisons()->latest()->get();
        $alerts = $user->alerts()->with('institute')->latest()->get();

        return view('public.user.dashboard', compact('favorites', 'savedComparisons', 'alerts'));
    }
}
