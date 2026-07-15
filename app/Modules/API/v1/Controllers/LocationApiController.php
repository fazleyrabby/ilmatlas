<?php

namespace App\Modules\API\v1\Controllers;

use App\Modules\API\v1\Resources\DistrictResource;
use App\Modules\API\v1\Resources\DivisionResource;
use App\Modules\API\v1\Resources\UpazilaResource;
use App\Modules\Location\Http\Controllers\LocationPublicController;
use App\Modules\Location\Models\District;
use App\Modules\Location\Models\Division;
use App\Modules\Location\Models\Upazila;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class LocationApiController extends Controller
{
    /**
     * GET /api/v1/locations/divisions
     * List all divisions (cached).
     */
    public function divisions(Request $request): JsonResponse
    {
        $divisions = LocationPublicController::getCachedDivisions();

        return response()->json([
            'data' => DivisionResource::collection($divisions),
        ])->withHeaders([
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * GET /api/v1/locations/districts
     * List districts, optionally filtered by division (ID or slug).
     */
    public function districts(Request $request): JsonResponse
    {
        $divisionParam = $request->query('division');
        $divisionIdParam = $request->query('division_id');

        if ($divisionParam || $divisionIdParam) {
            $query = District::query()->where('is_active', true);

            if ($divisionIdParam) {
                $query->where('division_id', $divisionIdParam);
            } elseif ($divisionParam) {
                $query->whereHas('division', fn ($q) => $q->where('slug', $divisionParam)->orWhere('id', $divisionParam));
            }

            $districts = $query->get();
        } else {
            $districts = LocationPublicController::getCachedDistricts();
        }

        return response()->json([
            'data' => DistrictResource::collection($districts),
        ])->withHeaders([
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * GET /api/v1/locations/upazilas
     * List upazilas, optionally filtered by district (ID or slug).
     */
    public function upazilas(Request $request): JsonResponse
    {
        $districtParam = $request->query('district');
        $districtIdParam = $request->query('district_id');

        $cacheKey = 'api:v1:locations:upazilas:'.md5(serialize($request->all()));

        $upazilas = Cache::remember($cacheKey, 3600, function () use ($districtParam, $districtIdParam) {
            $query = Upazila::query()->where('is_active', true);

            if ($districtIdParam) {
                $query->where('district_id', $districtIdParam);
            } elseif ($districtParam) {
                $query->whereHas('district', fn ($q) => $q->where('slug', $districtParam)->orWhere('id', $districtParam));
            }

            return $query->get();
        });

        return response()->json([
            'data' => UpazilaResource::collection($upazilas),
        ])->withHeaders([
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
