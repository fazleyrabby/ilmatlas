<?php

namespace App\Modules\API\v1\Controllers;

use App\Modules\API\v1\Resources\InstituteDetailResource;
use App\Modules\API\v1\Resources\InstituteListResource;
use App\Modules\Institute\Models\Institute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class InstituteApiController extends Controller
{
    /**
     * GET /api/v1/institutes
     * List all published institutes with filtering, sorting, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 100);
        $sort = $request->query('sort', 'published_at');
        $order = $request->query('order', 'desc') === 'asc' ? 'asc' : 'desc';

        // Check if cached
        $cacheKey = 'api:v1:institutes:list:'.md5(serialize($request->all()));

        $response = Cache::remember($cacheKey, 300, function () use ($request, $perPage, $sort, $order) {
            $query = Institute::published()
                ->with(['type', 'district', 'upazila']);

            // Type filter
            if ($request->filled('type')) {
                $query->whereHas('type', fn ($q) => $q->where('slug', $request->query('type')));
            }

            // District filter
            if ($request->filled('district')) {
                $query->whereHas('district', fn ($q) => $q->where('slug', $request->query('district')));
            }

            // Upazila filter
            if ($request->filled('upazila')) {
                $query->whereHas('upazila', fn ($q) => $q->where('slug', $request->query('upazila')));
            }

            // Curriculum filter
            if ($request->filled('curriculum')) {
                $query->whereHas('curriculums', fn ($q) => $q->where('slug', $request->query('curriculum')));
            }

            // Gender filter
            if ($request->filled('gender')) {
                $query->where('gender', $request->query('gender'));
            }

            // Religious orientation filter
            if ($request->filled('religious_orientation')) {
                $query->where('religious_orientation', $request->query('religious_orientation'));
            }

            // Fee min/max
            if ($request->filled('fee_min')) {
                $query->where('estimated_monthly_fee', '>=', (float) $request->query('fee_min'));
            }
            if ($request->filled('fee_max')) {
                $query->where('estimated_monthly_fee', '<=', (float) $request->query('fee_max'));
            }

            // Admission status
            if ($request->query('admission_status') === 'open') {
                $query->admissionOpen();
            }

            // Keyword search (simple fallback if Scout is not utilized)
            if ($request->filled('q')) {
                $q = $request->query('q');
                $query->where(fn ($sub) => $sub->where('name', 'like', "%{$q}%")->orWhere('short_name', 'like', "%{$q}%"));
            }

            // Sorting validation
            $allowedSorts = ['name', 'estimated_monthly_fee', 'established_year', 'published_at'];
            if (in_array($sort, $allowedSorts)) {
                $query->orderBy($sort, $order);
            } else {
                $query->orderBy('published_at', 'desc');
            }

            $institutes = $query->paginate($perPage);

            return [
                'data' => InstituteListResource::collection($institutes->items())->resolve(),
                'meta' => [
                    'current_page' => $institutes->currentPage(),
                    'per_page' => $institutes->perPage(),
                    'total' => $institutes->total(),
                    'last_page' => $institutes->lastPage(),
                ],
                'links' => [
                    'first' => $institutes->url(1),
                    'last' => $institutes->url($institutes->lastPage()),
                    'prev' => $institutes->previousPageUrl(),
                    'next' => $institutes->nextPageUrl(),
                ],
            ];
        });

        return response()->json($response)->withHeaders([
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    /**
     * GET /api/v1/institutes/{uuid}
     * Retrieve details of a single published institute.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $include = array_filter(explode(',', $request->query('include', '')));

        // Cache key incorporates includes
        $cacheKey = "api:v1:institute:{$uuid}:".md5(serialize($include));

        $response = Cache::remember($cacheKey, 600, function () use ($uuid, $include) {
            $query = Institute::where('uuid', $uuid)->published();

            // Load primary relationships by default
            $query->with(['type', 'district', 'division', 'upazila']);

            // Conditional relations based on include query param
            if (in_array('contacts', $include)) {
                $query->with('contacts');
            }
            if (in_array('curriculums', $include)) {
                $query->with('curriculums');
            }
            if (in_array('boards', $include)) {
                $query->with('boards');
            }
            if (in_array('programs', $include)) {
                $query->with('programs');
            }
            if (in_array('facilities', $include)) {
                $query->with('facilities');
            }
            if (in_array('fees', $include)) {
                $query->with(['fees.feeType' => fn ($q) => $q->where('is_published', true)]);
            }
            if (in_array('admissions', $include)) {
                $query->with('admissionCirculars');
            }

            $institute = $query->firstOrFail();

            return [
                'data' => (new InstituteDetailResource($institute))->resolve(),
            ];
        });

        return response()->json($response)->withHeaders([
            'Cache-Control' => 'public, max-age=600',
        ]);
    }
}
