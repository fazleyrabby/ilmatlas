<?php

namespace App\Modules\Taxonomy\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Taxonomy\Models\Category;
use App\Modules\Taxonomy\Models\Curriculum;
use App\Modules\Taxonomy\Models\InstituteType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TaxonomyController extends Controller
{
    public function index(): View
    {
        return view('admin.taxonomies.index', [
            'types' => InstituteType::orderBy('name')->get(),
            'categories' => Category::withCount('institutes')->orderBy('name')->get(),
            'curriculums' => Curriculum::withCount('institutes')->orderBy('name')->get(),
        ]);
    }

    public function createType(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:institute_types,slug',
            'description' => 'nullable|string|max:1000',
        ]);

        InstituteType::create(['uuid' => (string) Str::uuid(), ...$data]);

        return redirect()->route('admin.taxonomies.index')->with('success', 'Institute type created.');
    }

    public function destroyType(InstituteType $type): RedirectResponse
    {
        $type->delete();

        return redirect()->route('admin.taxonomies.index')->with('success', 'Institute type deleted.');
    }
}
