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

    public function show(Request $request): View
    {
        $slugs = [];
        for ($i = 1; $i <= 5; $i++) {
            $s = $request->query('i' . $i);
            if (is_string($s) && $s !== '') {
                $slugs[] = $s;
            }
        }

        $institutes = Institute::published()->whereIn('slug', $slugs)->get();
        $mismatch = false;
        $matrix = null;

        if ($institutes->count() >= 2) {
            $typeIds = $institutes->pluck('institute_type_id')->unique();
            if ($typeIds->count() > 1) {
                $mismatch = true;
            } else {
                $uuids = $institutes->pluck('uuid')->toArray();
                if ($request->boolean('refresh')) {
                    $this->comparison->forgetComparison($uuids);
                }
                $matrix = $this->comparison->getComparison($uuids);
            }
        }

        if (!$matrix) {
            // Seed columns with the attempted institutes (even on a type mismatch)
            // so the page renders the selection and a friendly notice, not a 500.
            $matrix = new \App\Modules\Comparison\DTOs\ComparisonMatrix($institutes->all(), []);
        }

        return view('public.compare.index', [
            'matrix' => $matrix,
            'mismatch' => $mismatch,
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
            return response()->json([
                'error' => 'Mismatched institution types. You can only compare institutions of the same type.',
                'mismatch' => true,
            ], 422);
        }

        if ($request->boolean('refresh')) {
            $this->comparison->forgetComparison($uuids);
        }

        $matrix = $this->comparison->getComparison($uuids);

        return response()->json([
            'institutes' => array_map(fn ($i) => [
                'name' => $i->name,
                'slug' => $i->slug,
                'uuid' => $i->uuid,
                'logo_url' => $i->logo_url,
                'type_id' => $i->institute_type_id,
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
