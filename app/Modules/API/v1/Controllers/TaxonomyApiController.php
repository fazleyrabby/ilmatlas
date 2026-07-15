<?php

namespace App\Modules\API\v1\Controllers;

use App\Modules\API\v1\Resources\TaxonomyResource;
use App\Modules\Fee\Models\FeeType;
use App\Modules\Taxonomy\Models\Category;
use App\Modules\Taxonomy\Models\Curriculum;
use App\Modules\Taxonomy\Models\EducationBoard;
use App\Modules\Taxonomy\Models\Facility;
use App\Modules\Taxonomy\Models\InstituteType;
use App\Modules\Taxonomy\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class TaxonomyApiController extends Controller
{
    /**
     * GET /api/v1/taxonomies/types
     */
    public function types(): JsonResponse
    {
        $types = Cache::remember('api:v1:taxonomies:types', 86400, fn () => InstituteType::all());

        return response()->json([
            'data' => TaxonomyResource::collection($types),
        ])->withHeaders(['Cache-Control' => 'public, max-age=86400']);
    }

    /**
     * GET /api/v1/taxonomies/categories
     */
    public function categories(): JsonResponse
    {
        $categories = Cache::remember('api:v1:taxonomies:categories', 86400, fn () => Category::where('is_active', true)->get());

        return response()->json([
            'data' => TaxonomyResource::collection($categories),
        ])->withHeaders(['Cache-Control' => 'public, max-age=86400']);
    }

    /**
     * GET /api/v1/taxonomies/curriculums
     */
    public function curriculums(): JsonResponse
    {
        $curriculums = Cache::remember('api:v1:taxonomies:curriculums', 86400, fn () => Curriculum::where('is_active', true)->get());

        return response()->json([
            'data' => TaxonomyResource::collection($curriculums),
        ])->withHeaders(['Cache-Control' => 'public, max-age=86400']);
    }

    /**
     * GET /api/v1/taxonomies/boards
     */
    public function boards(): JsonResponse
    {
        $boards = Cache::remember('api:v1:taxonomies:boards', 86400, fn () => EducationBoard::all());

        return response()->json([
            'data' => TaxonomyResource::collection($boards),
        ])->withHeaders(['Cache-Control' => 'public, max-age=86400']);
    }

    /**
     * GET /api/v1/taxonomies/programs
     */
    public function programs(): JsonResponse
    {
        $programs = Cache::remember('api:v1:taxonomies:programs', 86400, fn () => Program::all());

        return response()->json([
            'data' => TaxonomyResource::collection($programs),
        ])->withHeaders(['Cache-Control' => 'public, max-age=86400']);
    }

    /**
     * GET /api/v1/taxonomies/facilities
     */
    public function facilities(): JsonResponse
    {
        $facilities = Cache::remember('api:v1:taxonomies:facilities', 86400, fn () => Facility::all());

        return response()->json([
            'data' => TaxonomyResource::collection($facilities),
        ])->withHeaders(['Cache-Control' => 'public, max-age=86400']);
    }

    /**
     * GET /api/v1/taxonomies/fee-types
     */
    public function feeTypes(): JsonResponse
    {
        $feeTypes = Cache::remember('api:v1:taxonomies:fee-types', 86400, fn () => FeeType::all());

        return response()->json([
            'data' => TaxonomyResource::collection($feeTypes),
        ])->withHeaders(['Cache-Control' => 'public, max-age=86400']);
    }
}
