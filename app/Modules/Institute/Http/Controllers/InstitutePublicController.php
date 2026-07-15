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
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstitutePublicController extends Controller
{
    public function index(Request $request, SeoService $seo): View
    {
        $cacheKey = 'institutes:listing:'.md5(serialize($request->all()));
        $cachedData = Cache::remember($cacheKey, 300, function () use ($request) {
            $perPage = 50;
            $page = (int) $request->input('page', 1);

            $query = Institute::published()
                ->with(['type', 'district', 'upazila', 'primaryCategory'])
                ->withCount([
                    'fees' => fn ($q) => $q->where('is_published', true),
                    'facilities',
                    'contacts',
                    'curriculums',
                    'boards',
                    'programs',
                    'socialLinks',
                    'admissionCirculars' => fn ($q) => $q->where('admission_status', 'open'),
                ])
                ->when($request->type, fn ($q, $t) => $q->whereHas('type', fn ($sq) => $sq->where('slug', $t)))
                ->when(\Illuminate\Support\Arr::wrap($request->district), function ($q, $d) {
                    $ids = \App\Modules\Location\Models\District::whereIn('slug', $d)->pluck('id')->toArray();
                    return $q->whereIn('district_id', $ids);
                })
                ->when(\Illuminate\Support\Arr::wrap($request->category), fn ($q, $c) => $q->whereHas('categories', fn ($sq) => $sq->whereIn('slug', $c)))
                ->when(\Illuminate\Support\Arr::wrap($request->curriculum), fn ($q, $c) => $q->whereHas('curriculums', fn ($sq) => $sq->whereIn('slug', $c)))
                ->when($request->gender, fn ($q, $g) => $q->where('gender', $g));

            // Rank by profile completeness (fees weighted) so the most informative
            // institutes surface first; newest published breaks ties.
            $all = $query->get()->each(function ($item) {
                $item->completeness_score = $this->completenessScore($item);
            })->sort(function ($a, $b) {
                if ($b->completeness_score !== $a->completeness_score) {
                    return $b->completeness_score <=> $a->completeness_score;
                }
                return $b->published_at <=> $a->published_at;
            });

            $total = $all->count();
            $sliced = $all->forPage($page, $perPage)->values();

            $items = $sliced->map(function ($item) {
                $arr = $item->toArray();
                if ($item->relationLoaded('type') && $item->type) {
                    $arr['type'] = $item->type->toArray();
                }
                if ($item->relationLoaded('district') && $item->district) {
                    $arr['district'] = $item->district->toArray();
                }
                if ($item->relationLoaded('upazila') && $item->upazila) {
                    $arr['upazila'] = $item->upazila->toArray();
                }
                if ($item->relationLoaded('primaryCategory') && $item->primaryCategory) {
                    $arr['primary_category'] = $item->primaryCategory->toArray();
                }
                return $arr;
            })->toArray();

            return [
                'items' => $items,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
            ];
        });

        $hydratedItems = Institute::hydrate($cachedData['items']);
        foreach ($hydratedItems as $index => $item) {
            $raw = $cachedData['items'][$index];
            if (isset($raw['type'])) {
                $item->setRelation('type', (new \App\Modules\Taxonomy\Models\InstituteType)->newFromBuilder($raw['type']));
            }
            if (isset($raw['district'])) {
                $item->setRelation('district', (new \App\Modules\Location\Models\District)->newFromBuilder($raw['district']));
            }
            if (isset($raw['upazila'])) {
                $item->setRelation('upazila', (new \App\Modules\Location\Models\Upazila)->newFromBuilder($raw['upazila']));
            }
            if (isset($raw['primary_category'])) {
                $item->setRelation('primaryCategory', (new \App\Modules\Taxonomy\Models\Category)->newFromBuilder($raw['primary_category']));
            }
        }

        $institutes = new LengthAwarePaginator(
            $hydratedItems,
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

        $cachedData = Cache::remember(
            "institute:{$institute->uuid}:profile:data",
            600,
            function () use ($institute) {
                $institute->load([
                    'type', 'primaryCategory', 'country', 'division', 'district', 'upazila', 'area',
                    'categories', 'curriculums', 'boards', 'programs', 'subjects',
                    'facilities.group', 'languages', 'contacts', 'socialLinks',
                    'media', 'shifts',
                    'fees' => fn ($q) => $q->where('moderation_status', 'approved')->where('is_published', true),
                    'admissionCirculars' => fn ($q) => $q->where('is_published', true),
                ]);

                $arr = $institute->toArray();

                $relations = ['type', 'primaryCategory', 'country', 'division', 'district', 'upazila', 'area', 'categories', 'curriculums', 'boards', 'programs', 'subjects', 'facilities', 'languages', 'contacts', 'socialLinks', 'media', 'shifts', 'fees', 'admissionCirculars'];
                foreach ($relations as $rel) {
                    $snakeRel = Str::snake($rel);
                    if ($institute->relationLoaded($rel)) {
                        $arr["_relation_{$snakeRel}"] = $institute->{$rel} ? $institute->{$rel}->toArray() : null;
                    }
                }

                return $arr;
            }
        );

        $institute = (new Institute)->newFromBuilder($cachedData);
        $relations = ['type', 'primaryCategory', 'country', 'division', 'district', 'upazila', 'area', 'categories', 'curriculums', 'boards', 'programs', 'subjects', 'facilities', 'languages', 'contacts', 'socialLinks', 'media', 'shifts', 'fees', 'admissionCirculars'];
        foreach ($relations as $rel) {
            $snakeRel = Str::snake($rel);
            if (array_key_exists("_relation_{$snakeRel}", $cachedData)) {
                $relData = $cachedData["_relation_{$snakeRel}"];
                if ($relData === null) {
                    $institute->setRelation($rel, null);
                    continue;
                }

                $modelClass = match ($rel) {
                    'type' => \App\Modules\Taxonomy\Models\InstituteType::class,
                    'primaryCategory' => \App\Modules\Taxonomy\Models\Category::class,
                    'country' => \App\Modules\Location\Models\Country::class,
                    'division' => \App\Modules\Location\Models\Division::class,
                    'district' => \App\Modules\Location\Models\District::class,
                    'upazila' => \App\Modules\Location\Models\Upazila::class,
                    'area' => \App\Modules\Location\Models\Area::class,
                    'categories' => \App\Modules\Taxonomy\Models\Category::class,
                    'curriculums' => \App\Modules\Taxonomy\Models\Curriculum::class,
                    'boards' => \App\Modules\Taxonomy\Models\EducationBoard::class,
                    'programs' => \App\Modules\Taxonomy\Models\Program::class,
                    'subjects' => \App\Modules\Taxonomy\Models\Subject::class,
                    'facilities' => \App\Modules\Taxonomy\Models\Facility::class,
                    'languages' => \App\Modules\Taxonomy\Models\Language::class,
                    'contacts' => \App\Modules\Institute\Models\InstituteContact::class,
                    'socialLinks' => \App\Modules\Institute\Models\InstituteSocialLink::class,
                    'media' => \App\Modules\Institute\Models\InstituteMedia::class,
                    'shifts' => \App\Modules\Institute\Models\InstituteShift::class,
                    'fees' => \App\Modules\Fee\Models\FeeStructure::class,
                    'admissionCirculars' => \App\Modules\Admission\Models\AdmissionCircular::class,
                };

                if (in_array($rel, ['categories', 'curriculums', 'boards', 'programs', 'subjects', 'facilities', 'languages', 'contacts', 'socialLinks', 'media', 'shifts', 'fees', 'admissionCirculars'])) {
                    $collection = collect($relData)->map(fn ($itemData) => (new $modelClass)->newFromBuilder($itemData));
                    $institute->setRelation($rel, $collection);
                } else {
                    $institute->setRelation($rel, (new $modelClass)->newFromBuilder($relData));
                }
            }
        }

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

    /**
     * Profile completeness score used to rank the listing so the most
     * informative institutes surface first. Fees are weighted heaviest
     * since they are the highest-value data point for users.
     */
    private function completenessScore(Institute $institute): int
    {
        $ignore = ['not_applicable', 'n/a', ''];

        $score = 0;
        $score += $institute->established_year ? 1 : 0;
        $score += ($institute->gender && ! in_array(strtolower((string) $institute->gender), $ignore, true)) ? 1 : 0;
        $score += ($institute->religious_orientation && ! in_array(strtolower((string) $institute->religious_orientation), $ignore, true)) ? 1 : 0;
        $score += ($institute->methodology && ! in_array(strtolower((string) $institute->methodology), $ignore, true)) ? 1 : 0;
        $score += filled($institute->address) ? 1 : 0;
        $score += filled($institute->about) ? 1 : 0;
        $score += filled($institute->logo) ? 1 : 0;
        $score += filled($institute->website) ? 1 : 0;

        // Relation counts (loaded via withCount)
        $score += ($institute->fees_count ?? 0) * 3;
        $score += ($institute->facilities_count ?? 0);
        $score += ($institute->contacts_count ?? 0);
        $score += ($institute->curriculums_count ?? 0);
        $score += ($institute->boards_count ?? 0);
        $score += ($institute->programs_count ?? 0);
        $score += ($institute->social_links_count ?? 0);
        $score += ($institute->admission_circulars_count ?? 0);

        return $score;
    }
}
