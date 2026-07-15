<?php

namespace App\Modules\Institute\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Institute\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewModerationController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = Review::with(['user', 'institute'])
            ->latest()
            ->paginate(30);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function approve(Request $request, Review $review): RedirectResponse
    {
        $review->update([
            'moderation_status' => 'approved',
            'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
        ]);

        return back()->with('success', 'Review approved successfully.');
    }

    public function reject(Request $request, Review $review): RedirectResponse
    {
        $review->update([
            'moderation_status' => 'rejected',
            'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
        ]);

        return back()->with('success', 'Review rejected successfully.');
    }
}
