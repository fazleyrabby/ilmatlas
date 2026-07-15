<?php

namespace App\Modules\API\v1\Controllers;

use App\Modules\API\v1\Resources\InstituteListResource;
use App\Modules\Search\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SearchApiController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * GET /api/v1/search
     * Search institutes using Meilisearch (via SearchService).
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->query('q', '');
        $perPage = min((int) $request->query('per_page', 20), 100);

        // Gather filters
        $filters = [];
        if ($request->filled('type')) {
            $filters['type_slug'] = $request->query('type');
        }
        if ($request->filled('district')) {
            $filters['district'] = $request->query('district');
        }
        if ($request->filled('curriculums')) {
            $filters['curriculums'] = $request->query('curriculums');
        }

        $searchBuilder = $this->searchService->search($query, $filters);
        $results = $searchBuilder->paginate($perPage);

        return response()->json([
            'data' => InstituteListResource::collection($results->items()),
            'meta' => [
                'current_page' => $results->currentPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
                'last_page' => $results->lastPage(),
            ],
            'links' => [
                'first' => $results->url(1),
                'last' => $results->url($results->lastPage()),
                'prev' => $results->previousPageUrl(),
                'next' => $results->nextPageUrl(),
            ],
        ])->withHeaders([
            'Cache-Control' => 'private, no-cache',
        ]);
    }

    /**
     * GET /api/v1/search/autocomplete
     * Get autocomplete suggestions.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $query = $request->query('q', '');
        $limit = min((int) $request->query('limit', 8), 20);

        $results = $this->searchService->autocomplete($query, $limit);

        return response()->json([
            'data' => $results,
        ])->withHeaders([
            'Cache-Control' => 'private, no-cache',
        ]);
    }
}
