<?php

namespace App\Modules\Institute\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fee\Models\FeeType;
use App\Modules\Institute\Models\Institute;
use App\Modules\Location\Models\District;
use App\Modules\SEO\Services\SeoService;
use App\Modules\Taxonomy\Models\Category;
use App\Modules\Taxonomy\Models\Curriculum;
use App\Modules\Taxonomy\Models\InstituteType;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class InstitutePublicController extends Controller
{
    public function index(Request $request, SeoService $seo): View
    {
        $cacheKey = 'institutes:listing:'.md5(serialize($request->all()));
        $cachedData = Cache::remember($cacheKey, 300, function () use ($request) {
            $paginator = Institute::published()
                ->with(['type', 'district', 'upazila', 'primaryCategory'])
                ->when($request->type, fn ($q, $t) => $q->whereHas('type', fn ($sq) => $sq->where('slug', $t)))
                ->when(\Illuminate\Support\Arr::wrap($request->district), fn ($q, $d) => $q->whereIn('district_id', $d))
                ->when(\Illuminate\Support\Arr::wrap($request->category), fn ($q, $c) => $q->whereHas('categories', fn ($sq) => $sq->whereIn('slug', $c)))
                ->when(\Illuminate\Support\Arr::wrap($request->curriculum), fn ($q, $c) => $q->whereHas('curriculums', fn ($sq) => $sq->whereIn('slug', $c)))
                ->when($request->gender, fn ($q, $g) => $q->where('gender', $g))
                ->latest('published_at')
                ->paginate(20);

            return [
                'items' => $paginator->items(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
            ];
        });

        $institutes = new LengthAwarePaginator(
            $cachedData['items'],
            $cachedData['total'],
            $cachedData['per_page'],
            $cachedData['current_page'],
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        $types = InstituteType::all();
        $categories = Category::where('is_active', true)->get();
        $curriculums = Curriculum::where('is_active', true)->get();
        $districts = District::orderBy('name')->get();

        $meta = $seo->forLocation('Institute', 'All', $institutes->total(), 'educational institutes');

        return view('public.institutes.index', [
            'institutes' => $institutes,
            'types' => $types,
            'categories' => $categories,
            'curriculums' => $curriculums,
            'districts' => $districts,
            'seo' => $meta,
        ]);
    }

    public function byType(InstituteType $type, Request $request, SeoService $seo): View
    {
        $institutes = Institute::published()
            ->where('institute_type_id', $type->id)
            ->with(['type', 'district', 'upazila', 'primaryCategory'])
            ->latest('published_at')
            ->paginate(20);

        $districts = District::orderBy('name')->get();

        $meta = $seo->forLocation('Institute', $type->name, $institutes->total(), "{$type->name}s");

        $types = InstituteType::all();
        $categories = Category::where('is_active', true)->get();
        $curriculums = Curriculum::where('is_active', true)->get();

        return view('public.institutes.index', [
            'institutes' => $institutes,
            'types' => $types,
            'categories' => $categories,
            'curriculums' => $curriculums,
            'districts' => $districts,
            'seo' => $meta,
            'currentType' => $type,
        ]);
    }

    public function byDistrict(District $district, Request $request, SeoService $seo): View
    {
        $institutes = Institute::published()
            ->where('district_id', $district->id)
            ->with(['type', 'district', 'upazila', 'primaryCategory'])
            ->latest('published_at')
            ->paginate(20);

        $meta = $seo->forLocation('Institute', $district->name, $institutes->total(), "institutes in {$district->name}");

        $types = InstituteType::all();
        $categories = Category::where('is_active', true)->get();
        $curriculums = Curriculum::where('is_active', true)->get();
        $districts = District::orderBy('name')->get();

        return view('public.institutes.index', [
            'institutes' => $institutes,
            'types' => $types,
            'categories' => $categories,
            'curriculums' => $curriculums,
            'districts' => $districts,
            'seo' => $meta,
            'currentDistrict' => $district,
        ]);
    }

    public function byTypeAndDistrict(InstituteType $type, District $district, Request $request, SeoService $seo): View
    {
        $institutes = Institute::published()
            ->where('institute_type_id', $type->id)
            ->where('district_id', $district->id)
            ->with(['type', 'district', 'upazila', 'primaryCategory'])
            ->latest('published_at')
            ->paginate(20);

        $meta = $seo->forPSEO($district->name, $type->slug, "{$type->name}s", $institutes->total());

        $types = InstituteType::all();
        $categories = Category::where('is_active', true)->get();
        $curriculums = Curriculum::where('is_active', true)->get();
        $districts = District::orderBy('name')->get();

        return view('public.institutes.index', [
            'institutes' => $institutes,
            'types' => $types,
            'categories' => $categories,
            'curriculums' => $curriculums,
            'districts' => $districts,
            'seo' => $meta,
            'currentType' => $type,
            'currentDistrict' => $district,
        ]);
    }

    public function show(Institute $institute, SeoService $seo): View
    {
        abort_unless($institute->status === 'published', 404);

        $institute = Cache::remember(
            "institute:{$institute->uuid}:profile",
            600,
            function () use ($institute) {
                return $institute->load([
                    'type', 'primaryCategory', 'country', 'division', 'district', 'upazila', 'area',
                    'categories', 'curriculums', 'boards', 'programs', 'subjects',
                    'facilities.group', 'languages', 'contacts', 'socialLinks',
                    'media', 'shifts',
                    'fees' => fn ($q) => $q->where('moderation_status', 'approved')->where('is_published', true),
                    'admissionCirculars' => fn ($q) => $q->where('is_published', true),
                ]);
            }
        );

        $institute->increment('view_count');

        $meta = $seo->forInstitute($institute);

        $reviews = $institute->reviews()
            ->where('moderation_status', 'approved')
            ->with('user')
            ->latest()
            ->get();

        $feeTypes = Cache::remember('taxonomy:fee-types:all', 86400, fn () => FeeType::all());

        return view('public.institutes.show', [
            'institute' => $institute,
            'reviews' => $reviews,
            'feeTypes' => $feeTypes,
            'seo' => $meta,
        ]);
    }
}
