<?php

use App\Modules\Fee\Models\FeeStructure;
use App\Modules\Fee\Models\FeeType;
use App\Models\User;
use App\Modules\Institute\Models\Institute;
use App\Modules\Location\Models\Country;
use App\Modules\Location\Models\District;
use App\Modules\Location\Models\Division;
use App\Modules\Taxonomy\Models\InstituteType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        \Database\Seeders\RolePermissionSeeder::class,
    ]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');

    $type = InstituteType::create(['uuid' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'School', 'slug' => 'school']);
    $country = Country::create(['uuid' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'Bangladesh', 'code' => 'BD', 'slug' => 'bangladesh']);
    $division = Division::create(['uuid' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'Dhaka', 'slug' => 'dhaka', 'country_id' => $country->id]);
    $district = District::create(['uuid' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'Dhaka', 'slug' => 'dhaka', 'division_id' => $division->id]);

    $this->institute = Institute::create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
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

    $this->feeType = FeeType::create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'name' => 'Monthly Tuition',
        'slug' => 'monthly-tuition',
        'fee_category' => 'recurring',
    ]);
});

it('lists fee types', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.fees.types.index'))
        ->assertOk();
});

it('creates a fee type', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.fees.types.store'), [
            'name' => 'Admission Fee',
            'slug' => 'admission-fee',
            'fee_category' => 'one_time',
        ])
        ->assertRedirect(route('admin.fees.types.index'));

    $this->assertDatabaseHas('fee_types', ['slug' => 'admission-fee']);
});

it('deletes a fee type', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.fees.types.destroy', $this->feeType))
        ->assertRedirect(route('admin.fees.types.index'));
});

it('lists fee structures', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.fees.index'))
        ->assertOk();
});

it('shows fee create form', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.fees.create'))
        ->assertOk();
});

it('creates a fee structure', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.fees.store'), [
            'institute_id' => $this->institute->id,
            'fee_type_id' => $this->feeType->id,
            'academic_session' => '2026',
            'amount' => 1500,
            'frequency' => 'monthly',
            'currency' => 'BDT',
        ])
        ->assertRedirect(route('admin.fees.index'));
});

it('requires authentication for fee management', function () {
    $this->get(route('admin.fees.index'))->assertRedirect(route('admin.login'));
    $this->get(route('admin.fees.types.index'))->assertRedirect(route('admin.login'));
});

it('shows fee edit form', function () {
    $fee = FeeStructure::create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'institute_id' => $this->institute->id,
        'fee_type_id' => $this->feeType->id,
        'academic_session' => '2026',
        'amount' => 1500,
        'frequency' => 'monthly',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.fees.edit', $fee))
        ->assertOk();
});

it('moderates a pending fee', function () {
    $fee = FeeStructure::create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'institute_id' => $this->institute->id,
        'fee_type_id' => $this->feeType->id,
        'academic_session' => '2026',
        'amount' => 1500,
        'frequency' => 'monthly',
        'moderation_status' => 'pending_review',
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.fees.moderate', $fee), ['action' => 'approve'])
        ->assertRedirect(route('admin.fees.index'));
});

it('updates a fee structure', function () {
    $fee = FeeStructure::create([
        'uuid' => (string) \Illuminate\Support\Str::uuid(),
        'institute_id' => $this->institute->id,
        'fee_type_id' => $this->feeType->id,
        'academic_session' => '2026',
        'amount' => 1500,
        'frequency' => 'monthly',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.fees.update', $fee), [
            'institute_id' => $this->institute->id,
            'fee_type_id' => $this->feeType->id,
            'academic_session' => '2027',
            'amount' => 2000,
            'frequency' => 'monthly',
        ])
        ->assertRedirect(route('admin.fees.index'));
});
