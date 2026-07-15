<?php

use App\Models\User;
use App\Modules\Admission\Models\AdmissionSession;
use App\Modules\Fee\Models\FeeStructure;
use App\Modules\Fee\Models\FeeType;
use App\Modules\Fee\Services\FeeModerationService;
use App\Modules\Institute\Models\Institute;
use App\Modules\Location\Models\Country;
use App\Modules\Location\Models\District;
use App\Modules\Location\Models\Division;
use App\Modules\Taxonomy\Models\InstituteType;
use App\Modules\User\Models\UserAlert;
use App\Modules\User\Notifications\UserAlertNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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

    $this->type = InstituteType::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'School',
        'slug' => 'school',
    ]);

    $this->institute = Institute::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Al-Burooj High School',
        'slug' => 'al-burooj-high-school',
        'institute_type_id' => $this->type->id,
        'country_id' => $this->country->id,
        'division_id' => $this->division->id,
        'district_id' => $this->district->id,
        'gender' => 'co_educational',
        'religious_orientation' => 'islamic',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->institute2 = Institute::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Al-Hidaayah Academy',
        'slug' => 'al-hidaayah-academy',
        'institute_type_id' => $this->type->id,
        'country_id' => $this->country->id,
        'division_id' => $this->division->id,
        'district_id' => $this->district->id,
        'gender' => 'co_educational',
        'religious_orientation' => 'islamic',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->feeType = FeeType::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Admission Fee',
        'slug' => 'admission-fee',
        'fee_category' => 'one_time',
        'description' => 'Admission Fee structure',
    ]);

    $this->session = AdmissionSession::create([
        'uuid' => (string) Str::uuid(),
        'name' => '2026',
        'slug' => '2026',
        'session_start' => '2026-01-01',
        'session_end' => '2026-12-31',
    ]);
});

it('allows guests to register', function () {
    $response = $this->post('/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
});

it('allows users to login', function () {
    $response = $this->post('/login', [
        'email' => $this->user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($this->user);
});

it('protects dashboard from guests', function () {
    $this->get('/dashboard')->assertRedirect('/admin/login');
});

it('allows logged in users to visit dashboard', function () {
    $this->actingAs($this->user)->get('/dashboard')->assertOk();
});

it('allows users to bookmark favorites', function () {
    $this->actingAs($this->user)
        ->post(route('favorites.store'), [
            'institute_id' => $this->institute->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('user_favorites', [
        'user_id' => $this->user->id,
        'institute_id' => $this->institute->id,
    ]);
});

it('allows users to save comparisons', function () {
    $this->actingAs($this->user)
        ->post(route('comparisons.store'), [
            'name' => 'Top Schools',
            'institute_ids' => [$this->institute->uuid, $this->institute2->uuid],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('saved_comparisons', [
        'user_id' => $this->user->id,
        'name' => 'Top Schools',
    ]);
});

it('allows users to subscribe to email alerts', function () {
    $this->actingAs($this->user)
        ->post(route('alerts.store'), [
            'institute_id' => $this->institute->id,
            'alert_type' => 'fee_changes',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('user_alerts', [
        'user_id' => $this->user->id,
        'institute_id' => $this->institute->id,
        'alert_type' => 'fee_changes',
    ]);
});

it('sends fee notifications to watches', function () {
    Notification::fake();

    UserAlert::create([
        'user_id' => $this->user->id,
        'institute_id' => $this->institute->id,
        'alert_type' => 'fee_changes',
        'is_active' => true,
    ]);

    $fee = FeeStructure::create([
        'uuid' => (string) Str::uuid(),
        'institute_id' => $this->institute->id,
        'fee_type_id' => $this->feeType->id,
        'academic_session' => '2026',
        'amount' => 5000,
        'frequency' => 'monthly',
        'moderation_status' => 'pending_review',
    ]);

    $service = new FeeModerationService;
    $service->approve($fee, $this->admin);

    Notification::assertSentTo(
        $this->user,
        UserAlertNotification::class,
        function ($notification) {
            return str_contains($notification->title, 'Fee Update Alert') &&
                   str_contains($notification->content, '5000.00');
        }
    );
});

it('sends admission notifications to watches', function () {
    Notification::fake();

    UserAlert::create([
        'user_id' => $this->user->id,
        'institute_id' => $this->institute->id,
        'alert_type' => 'admission_openings',
        'is_active' => true,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.admissions.store'), [
            'institute_id' => $this->institute->id,
            'admission_session_id' => $this->session->id,
            'title' => 'Yearly Admission Opening',
            'admission_status' => 'open',
        ])
        ->assertRedirect();

    Notification::assertSentTo(
        $this->user,
        UserAlertNotification::class,
        function ($notification) {
            return str_contains($notification->title, 'Admission Open Alert') &&
                   str_contains($notification->content, 'Admission session is now OPEN');
        }
    );
});
