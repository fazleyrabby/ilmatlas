<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Models\UserFavorite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'institute_id' => ['required', 'exists:institutes,id'],
        ]);

        UserFavorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'institute_id' => $request->institute_id,
        ]);

        return back()->with('success', 'Institute added to your favorites.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $favorite = $request->user()->favorites()->findOrFail($id);
        $favorite->delete();

        return back()->with('success', 'Institute removed from your favorites.');
    }
}
