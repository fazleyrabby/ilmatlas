<?php

use App\Models\User;
use App\Modules\Institute\Models\Institute;
use App\Modules\Institute\Models\InstituteClaim;
use App\Modules\Location\Models\Country;
use App\Modules\Location\Models\District;
use App\Modules\Location\Models\Division;
use App\Modules\Taxonomy\Models\InstituteType;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class]);

    $this->user = User::factory()->create();
    $this->admin = User::factory()->create()->assignRole('super_admin');

    $this->country = Country::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Bangladesh',
        'code' => 'BD',
        'slug' => 'bangladesh',
    ]);

    $this->division = Division::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Dhaka',
        'slug' => 'dhaka',
        'country_id' => $this->country->id,
    ]);

    $this->district = District::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Dhaka',
        'slug' => 'dhaka',
        'division_id' => $this->division->id,
    ]);

    $this->type = InstituteType::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'School',
        'slug' => 'school',
    ]);

    $this->institute = Institute::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test School',
        'slug' => 'test-school',
        'institute_type_id' => $this->type->id,
        'country_id' => $this->country->id,
        'division_id' => $this->division->id,
        'district_id' => $this->district->id,
        'gender' => 'co_educational',
        'religious_orientation' => 'not_applicable',
        'status' => 'published',
        'published_at' => now(),
    ]);
});

it('allows user to submit an ownership claim request', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->create('proof.pdf', 100);

    $response = $this->actingAs($this->user)
        ->post(route('institutes.claim.store', $this->institute), [
            'notes' => 'I am the principal of this institute and want to claim it.',
            'proof' => $file,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('institute_claims', [
        'user_id' => $this->user->id,
        'institute_id' => $this->institute->id,
        'status' => 'pending',
    ]);
});

it('allows admin to approve a claim and set school owner', function () {
    $claim = InstituteClaim::create([
        'user_id' => $this->user->id,
        'institute_id' => $this->institute->id,
        'notes' => 'Claim notes verification.',
        'status' => 'pending',
    ]);

    // Approve
    $this->actingAs($this->admin)
        ->post(route('admin.claims.approve', $claim))
        ->assertRedirect();

    $this->assertDatabaseHas('institute_claims', [
        'id' => $claim->id,
        'status' => 'approved',
    ]);

    $this->assertDatabaseHas('institutes', [
        'id' => $this->institute->id,
        'owner_id' => $this->user->id,
    ]);

    expect($this->user->fresh()->hasRole('editor'))->toBeTrue();
});

it('allows verified owner to edit school profile parameters', function () {
    $this->institute->update(['owner_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->get(route('portal.edit', $this->institute))
        ->assertOk();

    $this->actingAs($this->user)
        ->put(route('portal.update', $this->institute), [
            'description' => 'Updated school profile about content.',
            'motto' => 'Truth and Knowledge',
        ])
        ->assertRedirect(route('portal.index'));

    $this->assertDatabaseHas('institutes', [
        'id' => $this->institute->id,
        'description' => 'Updated school profile about content.',
        'motto' => 'Truth and Knowledge',
    ]);
});

it('allows verified owner to view profile analytics', function () {
    $this->institute->update(['owner_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->get(route('portal.analytics', $this->institute))
        ->assertOk()
        ->assertViewHas('viewCount');
});

it('prevents non-owners from editing claimed school profile', function () {
    $anotherUser = User::factory()->create();
    $this->institute->update(['owner_id' => $this->user->id]);

    $this->actingAs($anotherUser)
        ->get(route('portal.edit', $this->institute))
        ->assertStatus(403);

    $this->actingAs($anotherUser)
        ->put(route('portal.update', $this->institute), [
            'motto' => 'Hacked Motto',
        ])
        ->assertStatus(403);
});
