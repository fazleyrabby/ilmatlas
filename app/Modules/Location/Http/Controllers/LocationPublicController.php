<?php

namespace App\Modules\Location\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Institute\Models\Institute;
use App\Modules\Location\Models\District;
use App\Modules\Location\Models\Division;
use App\Modules\Location\Models\Upazila;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class LocationPublicController extends Controller
{
    public function division(string $division): View
    {
        $division = Division::where('slug', $division)->firstOrFail();
        $division->loadMissing('districts');

        $districts = $division->districts()
            ->whereHas('institutes', fn ($q) => $q->published())
            ->get();

        $institutes = Institute::published()
            ->where('division_id', $division->id)
            ->with(['type', 'district'])
            ->latest('published_at')
            ->paginate(20);

        return view('public.locations.division', compact('division', 'districts', 'institutes'));
    }

    public function district(string $district): View
    {
        $district = District::where('slug', $district)->firstOrFail();
        $district->loadMissing('division');

        $upazilas = $district->upazilas()
            ->whereHas('institutes', fn ($q) => $q->published())
            ->get();

        $institutes = Institute::published()
            ->where('district_id', $district->id)
            ->with(['type', 'upazila'])
            ->latest('published_at')
            ->paginate(20);

        return view('public.locations.district', compact('district', 'upazilas', 'institutes'));
    }

    public function upazila(string $upazila): View
    {
        $upazila = Upazila::where('slug', $upazila)->firstOrFail();
        $upazila->loadMissing('district.division');

        $institutes = Institute::published()
            ->where('upazila_id', $upazila->id)
            ->with(['type', 'area'])
            ->latest('published_at')
            ->paginate(20);

        return view('public.locations.upazila', compact('upazila', 'institutes'));
    }

    /**
     * Get all divisions (cached 24h).
     */
    public static function getCachedDivisions()
    {
        $data = Cache::remember('location:divisions:all', 86400, fn () => Division::all()->toArray());
        return Division::hydrate($data);
    }

    /**
     * Get all districts (cached 24h).
     */
    public static function getCachedDistricts()
    {
        $data = Cache::remember('location:districts:all', 86400, function () {
            return District::with('division')->get()->map(function ($district) {
                $arr = $district->toArray();
                if ($district->relationLoaded('division') && $district->division) {
                    $arr['division'] = $district->division->toArray();
                }
                return $arr;
            })->toArray();
        });

        $districts = District::hydrate($data);
        foreach ($districts as $index => $district) {
            $raw = $data[$index];
            if (isset($raw['division'])) {
                $district->setRelation('division', (new Division)->newFromBuilder($raw['division']));
            }
        }
        return $districts;
    }
}

