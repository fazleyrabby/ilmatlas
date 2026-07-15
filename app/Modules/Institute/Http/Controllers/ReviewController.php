<?php

namespace App\Modules\Institute\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Institute\Models\Institute;
use App\Modules\Institute\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Institute $institute): RedirectResponse
    {
        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        // Check unique review
        $exists = Review::where('user_id', $request->user()->id)
            ->where('institute_id', $institute->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['comment' => 'You have already reviewed this institute.']);
        }

        Review::create([
            'user_id' => $request->user()->id,
            'institute_id' => $institute->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'moderation_status' => 'pending_review',
        ]);

        return back()->with('success', 'Your review has been submitted and is awaiting moderation.');
    }
}
