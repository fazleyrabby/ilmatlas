<?php

namespace App\Modules\Taxonomy\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Institute\Models\Institute;
use App\Modules\Taxonomy\Models\Category;
use Illuminate\View\View;

class TaxonomyPublicController extends Controller
{
    public function category($category): View
    {
        $category = Category::where('slug', $category)->firstOrFail();

        $institutes = Institute::published()
            ->where(fn ($q) => $q
                ->where('primary_category_id', $category->id)
                ->orWhereHas('categories', fn ($q2) => $q2->where('category_id', $category->id)))
            ->with(['type', 'district'])
            ->latest('published_at')
            ->paginate(20);

        return view('public.taxonomies.category', compact('category', 'institutes'));
    }
}
