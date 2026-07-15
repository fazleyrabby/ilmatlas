<?php

namespace App\Modules\SEO\Http\Controllers\Admin;

use App\Models\InstituteType;
use App\Modules\Institute\Models\Institute;
use App\Modules\Location\Models\District;
use App\Modules\SEO\Models\Redirect;
use App\Modules\SEO\Models\SeoMetadata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;

class SeoAdminController extends Controller
{
    public function index()
    {
        return view('modules.seo.admin.index');
    }

    public function edit(string $entityType, int $entityId)
    {
        $entity = $this->resolveEntity($entityType, $entityId);

        if (! $entity) {
            abort(404);
        }

        $meta = method_exists($entity, 'seoMetadata') ? $entity->seoMetadata : null;

        return view('modules.seo.admin.edit', [
            'entity' => $entity,
            'entityType' => $entityType,
            'meta' => $meta ?? new SeoMetadata,
        ]);
    }

    public function update(string $entityType, int $entityId)
    {
        $entity = $this->resolveEntity($entityType, $entityId);

        if (! $entity) {
            abort(404);
        }

        $data = request()->validate([
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|string|max:500',
            'og_type' => 'nullable|string|max:50',
            'canonical_url' => 'nullable|string|max:500',
            'noindex' => 'nullable|boolean',
            'schema_type' => 'nullable|string|max:100',
        ]);

        $data['noindex'] = request()->boolean('noindex');

        $meta = $entity->seoMetadata ?? $entity->seoMetadata()->create([]);
        $meta->update($data);

        return redirect()->back()->with('success', 'SEO metadata updated successfully.');
    }

    public function redirects()
    {
        $redirects = Redirect::latest()->paginate(20);

        return view('modules.seo.admin.redirects', compact('redirects'));
    }

    public function storeRedirect()
    {
        $data = request()->validate([
            'from_path' => 'required|string|max:500',
            'to_path' => 'required|string|max:500',
            'status_code' => 'required|integer|in:301,302',
        ]);

        Redirect::create($data);

        return redirect()->back()->with('success', 'Redirect created successfully.');
    }

    public function destroyRedirect(Redirect $redirect)
    {
        $redirect->delete();

        return redirect()->back()->with('success', 'Redirect deleted successfully.');
    }

    private function resolveEntity(string $type, int $id): ?Model
    {
        return match ($type) {
            'institute' => Institute::find($id),
            'district' => District::find($id),
            'type' => InstituteType::find($id),
            default => null,
        };
    }
}
