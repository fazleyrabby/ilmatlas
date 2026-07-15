<?php

use App\Models\User;
use App\Modules\Fee\Models\FeeType;
use App\Modules\Institute\Models\Institute;
use App\Modules\Institute\Models\Review;
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

    $this->feeType = FeeType::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Admission Fee',
        'slug' => 'admission-fee',
        'fee_category' => 'one_time',
        'description' => 'Entrance fee structure',
    ]);
});

it('allows authenticated users to submit a review', function () {
    $response = $this->actingAs($this->user)
        ->post(route('institutes.reviews.store', $this->institute), [
            'rating' => 5,
            'comment' => 'This school has an excellent curriculum and clean campus.',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('reviews', [
        'user_id' => $this->user->id,
        'institute_id' => $this->institute->id,
        'rating' => 5,
        'moderation_status' => 'pending_review',
    ]);
});

it('prevents multiple reviews from the same user for one school', function () {
    Review::create([
        'user_id' => $this->user->id,
        'institute_id' => $this->institute->id,
        'rating' => 4,
        'comment' => 'First review comment details.',
        'moderation_status' => 'approved',
    ]);

    $response = $this->actingAs($this->user)
        ->post(route('institutes.reviews.store', $this->institute), [
            'rating' => 5,
            'comment' => 'This is a second review attempt.',
        ]);

    $response->assertSessionHasErrors(['comment']);
    $this->assertDatabaseMissing('reviews', [
        'comment' => 'This is a second review attempt.',
    ]);
});

it('allows community members to submit fee details', function () {
    $response = $this->actingAs($this->user)
        ->post(route('institutes.fees.submit', $this->institute), [
            'fee_type_id' => $this->feeType->id,
            'academic_session' => '2026',
            'amount' => 4500,
            'frequency' => 'monthly',
            'notes' => 'From admission guide brochure 2026.',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('fee_structures', [
        'institute_id' => $this->institute->id,
        'fee_type_id' => $this->feeType->id,
        'academic_session' => '2026',
        'amount' => 4500,
        'verification_status' => 'community_reported',
        'moderation_status' => 'pending_review',
    ]);
});

it('allows admin to moderate reviews', function () {
    $review = Review::create([
        'user_id' => $this->user->id,
        'institute_id' => $this->institute->id,
        'rating' => 5,
        'comment' => 'Pending review to be approved.',
        'moderation_status' => 'pending_review',
    ]);

    // View queue
    $this->actingAs($this->admin)->get(route('admin.reviews.index'))->assertOk();

    // Approve review
    $this->actingAs($this->admin)
        ->post(route('admin.reviews.approve', $review))
        ->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'id' => $review->id,
        'moderation_status' => 'approved',
        'moderated_by' => $this->admin->id,
    ]);

    // Reset status to test reject
    $review->update(['moderation_status' => 'pending_review']);

    // Reject review
    $this->actingAs($this->admin)
        ->post(route('admin.reviews.reject', $review))
        ->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'id' => $review->id,
        'moderation_status' => 'rejected',
        'moderated_by' => $this->admin->id,
    ]);
});
