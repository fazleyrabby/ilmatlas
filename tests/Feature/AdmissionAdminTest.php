<?php

use App\Models\User;
use App\Modules\Admission\Models\AdmissionCircular;
use App\Modules\Admission\Models\AdmissionSession;
use App\Modules\Institute\Models\Institute;
use App\Modules\Location\Models\Country;
use App\Modules\Location\Models\District;
use App\Modules\Location\Models\Division;
use App\Modules\Taxonomy\Models\InstituteType;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');

    $type = InstituteType::create(['uuid' => (string) Str::uuid(), 'name' => 'School', 'slug' => 'school']);
    $country = Country::create(['uuid' => (string) Str::uuid(), 'name' => 'Bangladesh', 'code' => 'BD', 'slug' => 'bangladesh']);
    $division = Division::create(['uuid' => (string) Str::uuid(), 'name' => 'Dhaka', 'slug' => 'dhaka', 'country_id' => $country->id]);
    $district = District::create(['uuid' => (string) Str::uuid(), 'name' => 'Dhaka', 'slug' => 'dhaka', 'division_id' => $division->id]);

    $this->institute = Institute::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Institute',
        'slug' => 'test-institute',
        'institute_type_id' => $type->id,
        'country_id' => $country->id,
        'division_id' => $division->id,
        'district_id' => $district->id,
        'gender' => 'co_educational',
        'religious_orientation' => 'not_applicable',
        'status' => 'published',
    ]);

    $this->session = AdmissionSession::create([
        'uuid' => (string) Str::uuid(),
        'name' => '2026',
        'slug' => '2026',
        'session_start' => '2026-01-01',
        'session_end' => '2026-12-31',
    ]);
});

it('lists admission circulars', function () {
    $this->actingAs($this->admin)->get(route('admin.admissions.index'))->assertOk();
});

it('shows create form', function () {
    $this->actingAs($this->admin)->get(route('admin.admissions.create'))->assertOk();
});

it('creates an admission circular', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.admissions.store'), [
            'institute_id' => $this->institute->id,
            'admission_session_id' => $this->session->id,
            'title' => 'Class 1 Admission',
            'admission_status' => 'open',
            'application_start_date' => '2026-01-01',
            'application_end_date' => '2026-03-31',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.admissions.index'));

    $this->assertDatabaseHas('admission_circulars', [
        'institute_id' => $this->institute->id,
        'title' => 'Class 1 Admission',
    ]);
});

it('edits an admission circular', function () {
    $circular = AdmissionCircular::create([
        'uuid' => (string) Str::uuid(),
        'institute_id' => $this->institute->id,
        'admission_session_id' => $this->session->id,
        'title' => 'Class 1 Admission',
        'admission_status' => 'open',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->actingAs($this->admin)->get(route('admin.admissions.edit', $circular))->assertOk();
});

it('redirects unauthenticated users to login', function () {
    $response = $this->get(route('admin.admissions.index'));
    expect($response->status())->toBe(302);
});
