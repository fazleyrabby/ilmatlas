<?php

namespace App\Modules\API\v1\Controllers;

use App\Modules\API\v1\Resources\FeeHistoryResource;
use App\Modules\API\v1\Resources\FeeStructureResource;
use App\Modules\Fee\Models\FeeHistory;
use App\Modules\Institute\Models\Institute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FeeApiController extends Controller
{
    /**
     * GET /api/v1/institutes/{uuid}/fees
     * List published fee structures for an institute.
     */
    public function index(Request $request, Institute $institute): JsonResponse
    {
        $fees = $institute->fees()
            ->with('feeType')
            ->where('is_published', true)
            ->orderBy('fee_type_id')
            ->get();

        return response()->json([
            'data' => FeeStructureResource::collection($fees),
        ])->withHeaders([
            'Cache-Control' => 'public, max-age=600',
        ]);
    }

    /**
     * GET /api/v1/institutes/{uuid}/fees/history
     * List fee change history for an institute.
     */
    public function history(Request $request, Institute $institute): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 20), 100);

        $history = FeeHistory::with('feeType')
            ->where('institute_id', $institute->id)
            ->orderByDesc('effective_date')
            ->paginate($perPage);

        return response()->json([
            'data' => FeeHistoryResource::collection($history->items()),
            'meta' => [
                'current_page' => $history->currentPage(),
                'per_page' => $history->perPage(),
                'total' => $history->total(),
                'last_page' => $history->lastPage(),
            ],
            'links' => [
                'first' => $history->url(1),
                'last' => $history->url($history->lastPage()),
                'prev' => $history->previousPageUrl(),
                'next' => $history->nextPageUrl(),
            ],
        ])->withHeaders([
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
