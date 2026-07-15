<?php

namespace Database\Seeders;

use App\Modules\Fee\Models\FeeStructure;
use App\Modules\Fee\Models\FeeType;
use App\Modules\Institute\Actions\CreateInstituteAction;
use App\Modules\Institute\Actions\PublishInstituteAction;
use App\Modules\Institute\DTOs\InstituteData;
use App\Modules\Institute\Models\Institute;
use App\Modules\Institute\Models\InstituteContact;
use App\Modules\Institute\Models\InstituteSocialLink;
use App\Modules\Location\Models\Country;
use App\Modules\Location\Models\District;
use App\Modules\Location\Models\Upazila;
use App\Modules\Taxonomy\Models\Category;
use App\Modules\Taxonomy\Models\Curriculum;
use App\Modules\Taxonomy\Models\EducationBoard;
use App\Modules\Taxonomy\Models\Facility;
use App\Modules\Taxonomy\Models\InstituteType;
use App\Modules\Taxonomy\Models\Language;
use App\Modules\Taxonomy\Models\Program;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InstituteSeeder extends Seeder
{
    public function run(): void
    {
        $action = app(CreateInstituteAction::class);
        $publishAction = app(PublishInstituteAction::class);

        $typeIds = InstituteType::pluck('id', 'slug');
        $categoryIds = Category::pluck('id', 'slug');
        $curriculumIds = Curriculum::pluck('id', 'slug');
        $boardIds = EducationBoard::pluck('id', 'slug');
        $programIds = Program::pluck('id', 'slug');
        $languageIds = Language::pluck('id', 'code');
        $facilityIds = Facility::pluck('id', 'slug');
        $countryId = Country::first()?->id ?? 1;

        // Ensure Fee Types exist and map them
        $feeTypeIds = [];
        $feeTypes = [
            ['name' => 'Monthly Tuition', 'slug' => 'monthly-tuition', 'fee_category' => 'recurring'],
            ['name' => 'Admission Fee', 'slug' => 'admission-fee', 'fee_category' => 'one_time'],
            ['name' => 'Session Fee', 'slug' => 'session-fee', 'fee_category' => 'one_time'],
            ['name' => 'Exam Fee', 'slug' => 'exam-fee', 'fee_category' => 'recurring'],
            ['name' => 'Transport Fee', 'slug' => 'transport-fee', 'fee_category' => 'recurring'],
        ];
        foreach ($feeTypes as $ft) {
            $feeTypeIds[$ft['slug']] = FeeType::updateOrCreate(
                ['slug' => $ft['slug']],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $ft['name'],
                    'fee_category' => $ft['fee_category'],
                ]
            )->id;
        }

        // Landmark institutes with correct category bindings
        $landmarkInstitutes = [
            [
                'name' => 'Dhaka College',
                'slug' => 'dhaka-college',
                'type' => 'college',
                'gender' => 'boys',
                'religion' => 'not_applicable',
                'est' => 1841,
                'code' => '100001',
                'district' => 'Dhaka',
                'category' => 'government',
                'curriculum' => 'national-curriculum-bangladesh',
                'board' => 'dhaka-board',
            ],
            [
                'name' => 'Notre Dame College, Dhaka',
                'slug' => 'notre-dame-college-dhaka',
                'type' => 'college',
                'gender' => 'boys',
                'religion' => 'christianity',
                'est' => 1949,
                'code' => '100002',
                'district' => 'Dhaka',
                'category' => 'bangla-medium',
                'curriculum' => 'national-curriculum-bangladesh',
                'board' => 'dhaka-board',
            ],
            [
                'name' => 'Viqarunnisa Noon School & College',
                'slug' => 'viqarunnisa-noon-school-college',
                'type' => 'school',
                'gender' => 'girls',
                'religion' => 'not_applicable',
                'est' => 1952,
                'code' => '100003',
                'district' => 'Dhaka',
                'category' => 'bangla-medium',
                'curriculum' => 'national-curriculum-bangladesh',
                'board' => 'dhaka-board',
            ],
            [
                'name' => 'St. Joseph Higher Secondary School',
                'slug' => 'st-joseph-higher-secondary-school',
                'type' => 'school',
                'gender' => 'boys',
                'religion' => 'christianity',
                'est' => 1954,
                'code' => '100004',
                'district' => 'Dhaka',
                'category' => 'bangla-medium',
                'curriculum' => 'national-curriculum-bangladesh',
                'board' => 'dhaka-board',
            ],
            [
                'name' => 'Government Laboratory High School, Dhaka',
                'slug' => 'government-laboratory-high-school-dhaka',
                'type' => 'school',
                'gender' => 'boys',
                'religion' => 'not_applicable',
                'est' => 1961,
                'code' => '100005',
                'district' => 'Dhaka',
                'category' => 'government',
                'curriculum' => 'national-curriculum-bangladesh',
                'board' => 'dhaka-board',
            ],
            [
                'name' => 'Government Azizul Haque College, Bogura',
                'slug' => 'government-azizul-haque-college-bogura',
                'type' => 'college',
                'gender' => 'co_educational',
                'religion' => 'not_applicable',
                'est' => 1939,
                'code' => '100007',
                'district' => 'Bogura',
                'category' => 'government',
                'curriculum' => 'national-curriculum-bangladesh',
                'board' => 'rajshahi-board',
            ],
            [
                'name' => 'Comilla Zilla School',
                'slug' => 'comilla-zilla-school',
                'type' => 'school',
                'gender' => 'boys',
                'religion' => 'not_applicable',
                'est' => 1837,
                'code' => '100008',
                'district' => 'Cumilla',
                'category' => 'government',
                'curriculum' => 'national-curriculum-bangladesh',
                'board' => 'cumilla-board',
            ],
            [
                'name' => 'Rajshahi College',
                'slug' => 'rajshahi-college',
                'type' => 'college',
                'gender' => 'co_educational',
                'religion' => 'not_applicable',
                'est' => 1873,
                'code' => '100009',
                'district' => 'Rajshahi',
                'category' => 'government',
                'curriculum' => 'national-curriculum-bangladesh',
                'board' => 'rajshahi-board',
            ],
            [
                'name' => 'Jashore Zilla School',
                'slug' => 'jashore-zilla-school',
                'type' => 'school',
                'gender' => 'boys',
                'religion' => 'not_applicable',
                'est' => 1856,
                'code' => '100010',
                'district' => 'Jashore',
                'category' => 'government',
                'curriculum' => 'national-curriculum-bangladesh',
                'board' => 'jashore-board',
            ],
            [
                'name' => 'Kishoreganj Govt. Boys High School',
                'slug' => 'kishoreganj-govt-boys-high-school',
                'type' => 'school',
                'gender' => 'boys',
                'religion' => 'not_applicable',
                'est' => 1882,
                'code' => '100011',
                'district' => 'Kishoreganj',
                'category' => 'government',
                'curriculum' => 'national-curriculum-bangladesh',
                'board' => 'dhaka-board',
            ],
        ];

        $districts = District::with('division')->get()->keyBy('name');

        // Seed Landmark Institutes
        foreach ($landmarkInstitutes as $data) {
            $districtName = $data['district'] === 'Jashore' ? 'Jashore' : ($data['district'] === 'Cumilla' ? 'Cumilla' : $data['district']);
            $district = $districts->get($districtName);
            if (!$district) {
                continue;
            }

            $division = $district->division;
            $upazila = Upazila::where('district_id', $district->id)->first();
            $typeId = $typeIds[$data['type']] ?? $typeIds['school'];
            $primaryCategorySlug = $data['category'];
            $primaryCategoryId = $categoryIds[$primaryCategorySlug] ?? $categoryIds['bangla-medium'];

            $institute = $action->execute(new InstituteData(
                name: $data['name'],
                shortName: null,
                slug: Str::slug($data['name']),
                instituteTypeId: $typeId,
                countryId: $countryId,
                divisionId: $division->id,
                districtId: $district->id,
                upazilaId: $upazila?->id,
                areaId: null,
                establishedYear: $data['est'],
                description: "{$data['name']} is a renowned educational institution in Bangladesh, established in {$data['est']}.",
                instituteCode: $data['code'],
                primaryCategoryId: $primaryCategoryId,
                religiousOrientation: $data['religion'],
                methodology: null,
                gender: $data['gender'],
                fullAddress: "{$districtName}, Bangladesh",
                postalCode: null,
                latitude: null,
                longitude: null,
                googleMapsUrl: null,
                nearbyLandmark: null,
                status: 'draft',
                categoryIds: [$primaryCategoryId],
                curriculumIds: isset($curriculumIds[$data['curriculum']]) ? [$curriculumIds[$data['curriculum']]] : [],
                boardIds: isset($boardIds[$data['board']]) ? [$boardIds[$data['board']]] : [],
                programIds: $programIds->random(min(4, $programIds->count()))->toArray(),
                facilityIds: $facilityIds->random(min(4, $facilityIds->count()))->toArray(),
                languageIds: [$languageIds['en'] ?? 1, $languageIds['bn'] ?? 2],
            ));

            $this->seedContactsAndFees($institute, $feeTypeIds);
            $publishAction->execute($institute);
        }

        // Seed Curated Chattogram JSON Institutes
        $chattogramFile = database_path('data/institutes_chattogram.json');
        if (file_exists($chattogramFile)) {
            $chattogramInstitutes = json_decode(file_get_contents($chattogramFile), true);

            foreach ($chattogramInstitutes as $data) {
                $district = $districts->get('Chattogram');
                if (!$district) {
                    continue;
                }

                $division = $district->division;
                $upazila = Upazila::where('district_id', $district->id)
                    ->where('name', $data['upazila'])
                    ->first() ?? Upazila::where('district_id', $district->id)->first();

                $typeId = $typeIds[$data['type']] ?? $typeIds['school'];
                $primaryCategoryId = $categoryIds[$data['category']] ?? $categoryIds['bangla-medium'];

                $cIds = [];
                if (isset($curriculumIds[$data['curriculum']])) {
                    $cIds[] = $curriculumIds[$data['curriculum']];
                }

                $bIds = [];
                if (isset($boardIds[$data['board']])) {
                    $bIds[] = $boardIds[$data['board']];
                }

                $pIds = [];
                foreach ($data['programs'] as $pSlug) {
                    if (isset($programIds[$pSlug])) {
                        $pIds[] = $programIds[$pSlug];
                    }
                }

                $fIds = [];
                foreach ($data['facilities'] as $fSlug) {
                    if (isset($facilityIds[$fSlug])) {
                        $fIds[] = $facilityIds[$fSlug];
                    }
                }

                $institute = $action->execute(new InstituteData(
                    name: $data['name'],
                    shortName: $data['short_name'] ?? null,
                    slug: Str::slug($data['name']),
                    instituteTypeId: $typeId,
                    countryId: $countryId,
                    divisionId: $division->id,
                    districtId: $district->id,
                    upazilaId: $upazila?->id,
                    areaId: null,
                    establishedYear: $data['established_year'] ?? null,
                    description: "{$data['name']} is a prominent institution located in {$data['full_address']}.",
                    instituteCode: $data['institute_code'],
                    primaryCategoryId: $primaryCategoryId,
                    religiousOrientation: $data['religious_orientation'] ?? 'not_applicable',
                    methodology: null,
                    gender: $data['gender'],
                    fullAddress: $data['full_address'],
                    postalCode: null,
                    latitude: $data['latitude'] ?? null,
                    longitude: $data['longitude'] ?? null,
                    googleMapsUrl: null,
                    nearbyLandmark: null,
                    status: 'draft',
                    categoryIds: [$primaryCategoryId],
                    curriculumIds: $cIds,
                    boardIds: $bIds,
                    programIds: $pIds,
                    facilityIds: $fIds,
                    languageIds: [$languageIds['en'] ?? 1, $languageIds['bn'] ?? 2],
                ));

                // Contacts
                foreach ($data['contacts'] as $i => $contact) {
                    InstituteContact::create([
                        'uuid' => (string) Str::uuid(),
                        'institute_id' => $institute->id,
                        'contact_type' => $contact['type'],
                        'contact_value' => $contact['value'],
                        'is_public' => true,
                        'sort_order' => $i + 1,
                    ]);
                }

                // Social website
                if (isset($data['source_url'])) {
                    InstituteSocialLink::create([
                        'uuid' => (string) Str::uuid(),
                        'institute_id' => $institute->id,
                        'platform' => 'website',
                        'url' => $data['source_url'],
                        'is_public' => true,
                        'sort_order' => 1,
                    ]);

                    \App\Modules\Scraper\Models\ScraperSource::create([
                        'uuid' => (string) Str::uuid(),
                        'institute_id' => $institute->id,
                        'name' => $institute->name . ' Website Scraper',
                        'source_type' => 'website',
                        'adapter_class' => \App\Modules\Scraper\Services\InstitutionWebsiteAdapter::class,
                        'base_url' => $data['source_url'],
                        'config' => [],
                        'trust_level' => 'trusted',
                        'schedule_frequency' => 'monthly',
                        'is_active' => true,
                    ]);
                }

                // Custom Fees mapping
                foreach ($data['fees'] as $feeTypeSlug => $feeDetail) {
                    $feeTypeId = $feeTypeIds[$feeTypeSlug] ?? null;
                    if ($feeTypeId) {
                        FeeStructure::create([
                            'uuid' => (string) Str::uuid(),
                            'institute_id' => $institute->id,
                            'fee_type_id' => $feeTypeId,
                            'academic_session' => '2026',
                            'amount' => $feeDetail['amount'],
                            'currency' => 'BDT',
                            'frequency' => $feeDetail['frequency'] ?? 'monthly',
                            'moderation_status' => 'approved',
                            'is_published' => true,
                            'published_at' => now(),
                        ]);
                    }
                }

                $publishAction->execute($institute);
            }
        }

        // Seed from open source datasets
        $district = $districts->get('Chattogram');
        if ($district) {
            $division = $district->division;
            $upazilas = Upazila::where('district_id', $district->id)->get()->keyBy(fn($u) => strtolower($u->name));
            $upazilaList = $upazilas->values();

            $filesToIngest = [
                'bangla-medium' => database_path('data/banglaMediumSchools.json'),
                'english-medium' => database_path('data/englishMediumSchools.json')
            ];

            $codeCounter = 135000;

            foreach ($filesToIngest as $catSlug => $filePath) {
                if (file_exists($filePath)) {
                    $schoolNames = json_decode(file_get_contents($filePath), true);
                    if (is_array($schoolNames)) {
                        foreach ($schoolNames as $schoolName) {
                            $upperName = strtoupper($schoolName);
                            $isChittagong = false;
                            $keywords = ['CHITTAGONG', 'CTG', 'KAFCO', 'FAUJDARHAT', 'PATENGA', 'DHALGHAT', 'KUMIRA', 'HATHAZARI', 'POTIYA', 'PATIYA', 'PANCHLAISH', 'DOUBLE MOORING', 'KHULSHI', 'BAKALIA', 'CHANDGAON', 'HALISHAHAR', 'KOTWALI', 'SANDWIP', 'SITAKUNDA', 'SITAKUND', 'ANWARA', 'BOALKHALI', 'RANGUNIA', 'RAOZAN', 'MIRSHARAI', 'SATKANIA', 'LOHAGARA', 'BANSHKHALI', 'PEKUA'];
                            foreach ($keywords as $kw) {
                                if (str_contains($upperName, $kw)) {
                                    $isChittagong = true;
                                    break;
                                }
                            }

                            if ($isChittagong) {
                                $name = Str::title(trim($schoolName));
                                $slug = Str::slug($name);

                                if (Institute::where('slug', $slug)->exists()) {
                                    continue;
                                }

                                $matchedUpazila = null;
                                foreach ($upazilas as $upzName => $upzModel) {
                                    if (str_contains(strtolower($name), $upzName)) {
                                        $matchedUpazila = $upzModel;
                                        break;
                                    }
                                }
                                if (!$matchedUpazila) {
                                    $matchedUpazila = $upazilaList->random();
                                }

                                $typeId = $typeIds['school'];
                                $primaryCategoryId = $categoryIds[$catSlug] ?? $categoryIds['bangla-medium'];

                                $institute = $action->execute(new InstituteData(
                                    name: $name,
                                    shortName: null,
                                    slug: $slug,
                                    instituteTypeId: $typeId,
                                    countryId: $countryId,
                                    divisionId: $division->id,
                                    districtId: $district->id,
                                    upazilaId: $matchedUpazila?->id,
                                    areaId: null,
                                    establishedYear: rand(1960, 2015),
                                    description: "{$name} is a reputed school in Chittagong, offering quality education.",
                                    instituteCode: (string) $codeCounter++,
                                    primaryCategoryId: $primaryCategoryId,
                                    religiousOrientation: 'not_applicable',
                                    methodology: null,
                                    gender: 'co_educational',
                                    fullAddress: "{$matchedUpazila->name}, Chittagong, Bangladesh",
                                    postalCode: null,
                                    latitude: null,
                                    longitude: null,
                                    googleMapsUrl: null,
                                    nearbyLandmark: null,
                                    status: 'draft',
                                    categoryIds: [$primaryCategoryId],
                                    curriculumIds: [
                                        $catSlug === 'english-medium' 
                                            ? ($curriculumIds['cambridge-international'] ?? $curriculumIds['national-curriculum-bangladesh']) 
                                            : ($curriculumIds['national-curriculum-bangladesh'] ?? 1)
                                    ],
                                    boardIds: [$boardIds['chattogram-board'] ?? 1],
                                    programIds: $catSlug === 'english-medium'
                                        ? [
                                            $programIds['playgroup'] ?? 1,
                                            $programIds['nursery'] ?? 2,
                                            $programIds['class-1'] ?? 3,
                                            $programIds['class-5'] ?? 4,
                                            $programIds['class-9'] ?? 5,
                                            $programIds['class-10-ssc'] ?? 6
                                        ]
                                        : [
                                            $programIds['class-6'] ?? 7,
                                            $programIds['class-7'] ?? 8,
                                            $programIds['class-8'] ?? 9,
                                            $programIds['class-9'] ?? 5,
                                            $programIds['class-10-ssc'] ?? 6
                                        ],
                                    facilityIds: [
                                        $facilityIds['library'] ?? 1,
                                        $facilityIds['computer-lab'] ?? 2,
                                        $facilityIds['science-lab'] ?? 3,
                                        $facilityIds['playground'] ?? 4
                                    ],
                                    languageIds: [$languageIds['en'] ?? 1, $languageIds['bn'] ?? 2],
                                ));

                                $this->seedContactsAndFees($institute, $feeTypeIds);
                                $publishAction->execute($institute);
                            }
                        }
                    }
                }
            }
        }
    }

    private function seedContactsAndFees(Institute $institute, array $feeTypeIds): void
    {
        InstituteContact::create([
            'uuid' => (string) Str::uuid(),
            'institute_id' => $institute->id,
            'contact_type' => 'phone',
            'contact_value' => '01' . (string) rand(700000000, 999999999),
            'is_public' => true,
            'sort_order' => 1,
        ]);

        InstituteContact::create([
            'uuid' => (string) Str::uuid(),
            'institute_id' => $institute->id,
            'contact_type' => 'email',
            'contact_value' => 'info@' . Str::slug($institute->name) . '.edu.bd',
            'is_public' => true,
            'sort_order' => 2,
        ]);

        InstituteSocialLink::create([
            'uuid' => (string) Str::uuid(),
            'institute_id' => $institute->id,
            'platform' => 'website',
            'url' => 'https://www.' . Str::slug($institute->name) . '.edu.bd',
            'is_public' => true,
            'sort_order' => 1,
        ]);

        FeeStructure::create([
            'uuid' => (string) Str::uuid(),
            'institute_id' => $institute->id,
            'fee_type_id' => $feeTypeIds['monthly-tuition'] ?? 1,
            'academic_session' => '2026',
            'amount' => rand(500, 3000),
            'currency' => 'BDT',
            'frequency' => 'monthly',
            'moderation_status' => 'approved',
            'is_published' => true,
            'published_at' => now(),
        ]);

        FeeStructure::create([
            'uuid' => (string) Str::uuid(),
            'institute_id' => $institute->id,
            'fee_type_id' => $feeTypeIds['admission-fee'] ?? 2,
            'academic_session' => '2026',
            'amount' => rand(2000, 15000),
            'currency' => 'BDT',
            'frequency' => 'one_time',
            'moderation_status' => 'approved',
            'is_published' => true,
            'published_at' => now(),
        ]);
    }
}
