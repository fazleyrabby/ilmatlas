<?php

namespace Database\Seeders;

use App\Modules\Location\Models\Country;
use App\Modules\Location\Models\District;
use App\Modules\Location\Models\Division;
use App\Modules\Location\Models\Upazila;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::updateOrCreate(
            ['slug' => 'bangladesh'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Bangladesh',
                'code' => 'BD',
                'currency_code' => 'BDT',
                'phone_code' => '+880',
            ]
        );

        // Load Divisions
        $divisionsJson = json_decode(file_get_contents(database_path('data/divisions.json')), true);
        $divisionMap = [];

        foreach ($divisionsJson['divisions'] as $div) {
            $name = $div['name'];
            $slug = Str::slug($name);

            $divisionModel = Division::updateOrCreate(
                ['slug' => $slug],
                [
                    'uuid' => (string) Str::uuid(),
                    'country_id' => $country->id,
                    'name' => $name,
                    'bn_name' => $div['bn_name'],
                    'latitude' => $div['lat'] ?? null,
                    'longitude' => $div['long'] ?? null,
                ]
            );
            $divisionMap[$div['id']] = $divisionModel;
        }

        // Load Districts
        $districtsJson = json_decode(file_get_contents(database_path('data/districts.json')), true);
        $districtMap = [];

        foreach ($districtsJson['districts'] as $dist) {
            $divisionModel = $divisionMap[$dist['division_id']] ?? null;
            if (!$divisionModel) {
                continue;
            }

            $name = $dist['name'];
            $slug = Str::slug($name);

            $districtModel = District::updateOrCreate(
                [
                    'division_id' => $divisionModel->id,
                    'slug' => $slug,
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $name,
                    'bn_name' => $dist['bn_name'],
                    'latitude' => $dist['lat'] ?? null,
                    'longitude' => $dist['long'] ?? null,
                ]
            );
            $districtMap[$dist['id']] = $districtModel;
        }

        // Load Upazilas
        $upazilasJson = json_decode(file_get_contents(database_path('data/upazilas.json')), true);

        foreach ($upazilasJson['upazilas'] as $upz) {
            $districtModel = $districtMap[$upz['district_id']] ?? null;
            if (!$districtModel) {
                continue;
            }

            $name = $upz['name'];
            $slug = Str::slug($name);

            Upazila::updateOrCreate(
                [
                    'district_id' => $districtModel->id,
                    'slug' => $slug,
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $name,
                    'bn_name' => $upz['bn_name'],
                    'latitude' => $upz['lat'] ?? null,
                    'longitude' => $upz['long'] ?? null,
                ]
            );
        }
    }
}

