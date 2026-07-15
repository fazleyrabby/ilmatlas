<?php

namespace App\Modules\Comparison\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comparison\Services\ComparisonService;
use App\Modules\Institute\Models\Institute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComparisonController extends Controller
{
    public function __construct(
        private ComparisonService $comparison,
    ) {}

    public function show(Request $request, string $slugs): View
    {
        $slugList = $this->comparison->parseSlug($slugs);
        $institutes = Institute::published()->whereIn('slug', $slugList)->get();

        if ($institutes->count() < 2) {
            abort(404, 'At least 2 institutes are required for comparison.');
        }

        $typeIds = $institutes->pluck('institute_type_id')->unique();
        if ($typeIds->count() > 1) {
            abort(400, 'Mismatched institution types. You can only compare institutions of the same type (e.g., comparing school with school, college with college).');
        }

        $matrix = $this->comparison->getComparison($institutes->pluck('uuid')->toArray());

        return view('public.compare.index', [
            'matrix' => $matrix,
            'slug' => $slugs,
        ]);
    }

    public function api(Request $request): JsonResponse
    {
        $uuids = $request->input('ids', []);

        if (is_string($uuids)) {
            $uuids = explode(',', $uuids);
        }

        if (count($uuids) < 2 || count($uuids) > 5) {
            return response()->json(['error' => 'Provide 2-5 institute UUIDs.'], 422);
        }

        $institutes = Institute::published()->whereIn('uuid', $uuids)->get();
        if ($institutes->count() < 2) {
            return response()->json(['error' => 'At least 2 valid published institutions are required for comparison.'], 422);
        }

        $typeIds = $institutes->pluck('institute_type_id')->unique();
        if ($typeIds->count() > 1) {
            return response()->json(['error' => 'Mismatched institution types. You can only compare institutions of the same type.'], 422);
        }

        $matrix = $this->comparison->getComparison($uuids);

        return response()->json([
            'institutes' => array_map(fn ($i) => [
                'name' => $i->name,
                'slug' => $i->slug,
                'uuid' => $i->uuid,
                'logo_url' => $i->logo_url,
            ], $matrix->institutes),
            'groups' => array_map(fn ($g) => [
                'name' => $g->name,
                'slug' => $g->slug,
                'rows' => array_map(fn ($r) => [
                    'label' => $r->label,
                    'slug' => $r->slug,
                    'values' => $r->values,
                    'all_identical' => $r->allIdentical,
                ], $g->rows ?? []),
            ], $matrix->groups),
            'generated_at' => $matrix->generatedAt->toIso8601String(),
        ]);
    }
}
