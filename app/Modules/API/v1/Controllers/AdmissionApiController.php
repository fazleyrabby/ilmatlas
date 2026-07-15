<?php

namespace App\Modules\API\v1\Controllers;

use App\Modules\Admission\Models\AdmissionCircular;
use App\Modules\API\v1\Resources\AdmissionResource;
use App\Modules\Institute\Models\Institute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdmissionApiController extends Controller
{
    /**
     * GET /api/v1/institutes/{uuid}/admissions
     * List published admission circulars for a specific institute.
     */
    public function forInstitute(Request $request, Institute $institute): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 100);

        $circulars = $institute->admissionCirculars()
            ->where('is_published', true)
            ->orderByDesc('application_start_date')
            ->paginate($perPage);

        return response()->json([
            'data' => AdmissionResource::collection($circulars->items()),
            'meta' => [
                'current_page' => $circulars->currentPage(),
                'per_page' => $circulars->perPage(),
                'total' => $circulars->total(),
                'last_page' => $circulars->lastPage(),
            ],
            'links' => [
                'first' => $circulars->url(1),
                'last' => $circulars->url($circulars->lastPage()),
                'prev' => $circulars->previousPageUrl(),
                'next' => $circulars->nextPageUrl(),
            ],
        ])->withHeaders([
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    /**
     * GET /api/v1/admissions
     * List admission circulars globally (optionally filtered).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 100);

        $query = AdmissionCircular::with('institute')
            ->where('is_published', true);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('admission_status', $request->query('status'));
        }

        // Filter by district via institute relationship
        if ($request->filled('district_id')) {
            $query->whereHas('institute', fn ($q) => $q->where('district_id', $request->query('district_id')));
        }

        $circulars = $query->orderByDesc('application_start_date')->paginate($perPage);

        return response()->json([
            'data' => AdmissionResource::collection($circulars->items()),
            'meta' => [
                'current_page' => $circulars->currentPage(),
                'per_page' => $circulars->perPage(),
                'total' => $circulars->total(),
                'last_page' => $circulars->lastPage(),
            ],
            'links' => [
                'first' => $circulars->url(1),
                'last' => $circulars->url($circulars->lastPage()),
                'prev' => $circulars->previousPageUrl(),
                'next' => $circulars->nextPageUrl(),
            ],
        ])->withHeaders([
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
