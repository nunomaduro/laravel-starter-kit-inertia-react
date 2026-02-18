<?php

declare(strict_types=1);

use App\Enums\ActivityType;
use App\Features\ImpersonationFeature;
use App\Models\User;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Laravel\Pennant\Feature;
use Spatie\Activitylog\Models\Activity;
use STS\FilamentImpersonate\Facades\Impersonation;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('only super-admin can impersonate', function (): void {
    $superAdmin = User::factory()->withoutTwoFactor()->create();
    $superAdmin->assignRole('super-admin');

    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->assignRole('admin');

    $user = User::factory()->withoutTwoFactor()->create();
    $user->assignRole('user');

    expect($superAdmin->canImpersonate())->toBeTrue()
        ->and($admin->canImpersonate())->toBeFalse()
        ->and($user->canImpersonate())->toBeFalse();
});

test('super-admin cannot impersonate when impersonation feature is inactive', function (): void {
    $superAdmin = User::factory()->withoutTwoFactor()->create();
    $superAdmin->assignRole('super-admin');
    Feature::for($superAdmin)->deactivate(ImpersonationFeature::class);

    expect($superAdmin->canImpersonate())->toBeFalse();
});

test('super-admin cannot be impersonated', function (): void {
    $superAdmin = User::factory()->withoutTwoFactor()->create();
    $superAdmin->assignRole('super-admin');

    $user = User::factory()->withoutTwoFactor()->create();
    $user->assignRole('user');

    expect($superAdmin->canBeImpersonated())->toBeFalse()
        ->and($user->canBeImpersonated())->toBeTrue();
});

test('taking impersonation logs activity with impersonator as causer', function (): void {
    $superAdmin = User::factory()->withoutTwoFactor()->create(['name' => 'Super Admin']);
    $superAdmin->assignRole('super-admin');

    $target = User::factory()->withoutTwoFactor()->create(['name' => 'Target User']);
    $target->assignRole('user');

    $this->actingAs($superAdmin);

    Impersonation::enter($superAdmin, $target, 'web');

    $activity = Activity::query()
        ->where('description', ActivityType::ImpersonationStarted->value)
        ->where('causer_id', $superAdmin->getKey())
        ->where('causer_type', User::class)
        ->where('subject_id', $target->getKey())
        ->where('subject_type', User::class)
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('impersonator_name'))->toBe('Super Admin')
        ->and($activity->properties->get('impersonated_name'))->toBe('Target User');
});

test('leaving impersonation logs activity with impersonator as causer', function (): void {
    $superAdmin = User::factory()->withoutTwoFactor()->create(['name' => 'Super Admin']);
    $superAdmin->assignRole('super-admin');

    $target = User::factory()->withoutTwoFactor()->create(['name' => 'Target User']);
    $target->assignRole('user');

    $this->actingAs($superAdmin);
    Impersonation::enter($superAdmin, $target, 'web');

    $this->actingAs($target);
    Impersonation::leave();

    $activity = Activity::query()
        ->where('description', ActivityType::ImpersonationEnded->value)
        ->where('causer_id', $superAdmin->getKey())
        ->where('causer_type', User::class)
        ->where('subject_id', $target->getKey())
        ->where('subject_type', User::class)
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties->get('impersonator_name'))->toBe('Super Admin')
        ->and($activity->properties->get('impersonated_name'))->toBe('Target User');
});
