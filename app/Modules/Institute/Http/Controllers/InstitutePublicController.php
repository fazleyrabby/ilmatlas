<?php

namespace App\Modules\Institute\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Institute\Models\Institute;
use App\Modules\Location\Models\District;
use App\Modules\SEO\Services\SeoService;
use App\Modules\Taxonomy\Models\Category;
use App\Modules\Taxonomy\Models\Curriculum;
use App\Modules\Taxonomy\Models\InstituteType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class InstitutePublicController extends Controller
{
    public function index(Request $request): View
    {
        $cacheKey = 'institutes:listing:'.md5(serialize($request->all()));
        $institutes = Cache::remember($cacheKey, 300, function () use ($request) {
            return Institute::published()
                ->select([
                    'id', 'uuid', 'name', 'slug', 'institute_type_id',
                    'district_id', 'upazila_id', 'estimated_monthly_fee',
                    'verification_status', 'status', 'published_at',
                ])
                ->with(['type', 'district', 'upazila', 'primaryCategory'])
                ->when($request->type, fn ($q, $t) => $q->whereHas('type', fn ($sq) => $sq->where('slug', $t)))
                ->when($request->district, fn ($q, $d) => $q->where('district_id', $d))
                ->when($request->category, fn ($q, $c) => $q->whereHas('categories', fn ($sq) => $sq->where('slug', $c)))
                ->when($request->curriculum, fn ($q, $c) => $q->whereHas('curriculums', fn ($sq) => $sq->where('slug', $c)))
                ->when($request->gender, fn ($q, $g) => $q->where('gender', $g))
                ->latest('published_at')
                ->paginate(20);
        });

        $types = Cache::remember('taxonomy:types:all', 86400, fn () => InstituteType::all());
        $categories = Cache::remember('taxonomy:categories:active', 86400, fn () => Category::where('is_active', true)->get());
        $curriculums = Cache::remember('taxonomy:curriculums:active', 86400, fn () => Curriculum::where('is_active', true)->get());

        return view('public.institutes.index', compact('institutes', 'types', 'categories', 'curriculums'));
    }

    public function byType(InstituteType $type, Request $request, SeoService $seo): View
    {
        $institutes = Institute::published()
            ->where('institute_type_id', $type->id)
            ->with(['type', 'district', 'upazila', 'primaryCategory'])
            ->latest('published_at')
            ->paginate(20);

        $meta = $seo->forLocation('Institute', $type->name, $institutes->total(), "{$type->name}s");

        $types = Cache::remember('taxonomy:types:all', 86400, fn () => InstituteType::all());
        $categories = Cache::remember('taxonomy:categories:active', 86400, fn () => Category::where('is_active', true)->get());
        $curriculums = Cache::remember('taxonomy:curriculums:active', 86400, fn () => Curriculum::where('is_active', true)->get());

        return view('public.institutes.index', [
            'institutes' => $institutes,
            'types' => $types,
            'categories' => $categories,
            'curriculums' => $curriculums,
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

        $types = Cache::remember('taxonomy:types:all', 86400, fn () => InstituteType::all());
        $categories = Cache::remember('taxonomy:categories:active', 86400, fn () => Category::where('is_active', true)->get());
        $curriculums = Cache::remember('taxonomy:curriculums:active', 86400, fn () => Curriculum::where('is_active', true)->get());

        return view('public.institutes.index', [
            'institutes' => $institutes,
            'types' => $types,
            'categories' => $categories,
            'curriculums' => $curriculums,
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

        $types = Cache::remember('taxonomy:types:all', 86400, fn () => InstituteType::all());
        $categories = Cache::remember('taxonomy:categories:active', 86400, fn () => Category::where('is_active', true)->get());
        $curriculums = Cache::remember('taxonomy:curriculums:active', 86400, fn () => Curriculum::where('is_active', true)->get());

        return view('public.institutes.index', [
            'institutes' => $institutes,
            'types' => $types,
            'categories' => $categories,
            'curriculums' => $curriculums,
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

        return view('public.institutes.show', [
            'institute' => $institute,
            'seo' => $meta,
        ]);
    }
}
