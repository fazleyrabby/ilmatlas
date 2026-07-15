<?php

use App\Modules\Institute\Models\Institute;
use App\Modules\Location\Models\Country;
use App\Modules\Location\Models\District;
use App\Modules\Location\Models\Division;
use App\Modules\Location\Models\Upazila;
use App\Modules\Taxonomy\Models\InstituteType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->country = Country::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Bangladesh',
        'code' => 'BD',
        'slug' => 'bangladesh',
    ]);

    $this->division = Division::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Chattogram',
        'slug' => 'chattogram',
        'country_id' => $this->country->id,
    ]);

    $this->district = District::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Chattogram',
        'slug' => 'chattogram',
        'division_id' => $this->division->id,
    ]);

    $this->upazila = Upazila::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Hathazari',
        'slug' => 'hathazari',
        'district_id' => $this->district->id,
    ]);

    $this->type = InstituteType::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'School',
        'slug' => 'school',
    ]);

    $this->institute = Institute::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Al-Hidaayah Academy',
        'slug' => 'al-hidaayah-academy',
        'institute_type_id' => $this->type->id,
        'country_id' => $this->country->id,
        'division_id' => $this->division->id,
        'district_id' => $this->district->id,
        'upazila_id' => $this->upazila->id,
        'gender' => 'co_educational',
        'religious_orientation' => 'islamic',
        'status' => 'published',
        'published_at' => now(),
    ]);
});

it('lists institutes via API', function () {
    $response = $this->getJson(route('api.v1.institutes.index'));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'uuid',
                    'name',
                    'slug',
                    'estimated_monthly_fee',
                    'verification_status',
                    'status',
                ],
            ],
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            'links' => ['first', 'last', 'prev', 'next'],
        ]);
});

it('shows detailed institute via API', function () {
    $response = $this->getJson(route('api.v1.institutes.show', $this->institute->uuid));

    $response->assertOk()
        ->assertJsonPath('data.uuid', $this->institute->uuid)
        ->assertJsonPath('data.name', $this->institute->name);
});

it('lists divisions via API', function () {
    $response = $this->getJson(route('api.v1.locations.divisions'));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'uuid', 'name', 'slug'],
            ],
        ]);
});

it('lists districts via API', function () {
    $response = $this->getJson(route('api.v1.locations.districts'));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'uuid', 'name', 'slug', 'division_id'],
            ],
        ]);
});

it('lists upazilas via API', function () {
    $response = $this->getJson(route('api.v1.locations.upazilas'));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'uuid', 'name', 'slug', 'district_id'],
            ],
        ]);
});

it('lists taxonomies via API', function () {
    $response = $this->getJson(route('api.v1.taxonomies.types'));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'slug'],
            ],
        ]);
});
