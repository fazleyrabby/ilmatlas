<?php

namespace App\Modules\Taxonomy\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Taxonomy\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('institutes')
            ->orderBy('name')
            ->paginate(50);

        return view('admin.taxonomies.categories', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.taxonomies.category-form', [
            'category' => new Category,
            'parentCategories' => Category::whereNull('parent_id')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        Category::create(['uuid' => (string) Str::uuid(), ...$data]);

        return redirect()->route('admin.taxonomies.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.taxonomies.category-form', [
            'category' => $category,
            'parentCategories' => Category::whereNull('parent_id')->where('id', '!=', $category->id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,'.$category->id,
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        $category->update($data);

        return redirect()->route('admin.taxonomies.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('admin.taxonomies.categories.index')->with('success', 'Category deleted.');
    }
}
