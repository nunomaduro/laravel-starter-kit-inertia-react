<?php

declare(strict_types=1);

use App\Enums\ActivityType;
use App\Features\ImpersonationFeature;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Laravel\Pennant\Feature;
use Spatie\Activitylog\Models\Activity;
use STS\FilamentImpersonate\Facades\Impersonation;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('super-admin can impersonate; non-org-admin cannot', function (): void {
    $superAdmin = User::factory()->withoutTwoFactor()->create();
    $superAdmin->assignRole('super-admin');

    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->assignRole('admin');
    // Ensure admin is not org admin (e.g. may have personal org as owner from listeners)
    $admin->organizations()->each(fn ($org) => $org->removeMember($admin));
    $org = Organization::factory()->create();
    $org->addMember($admin, 'member');

    $user = User::factory()->withoutTwoFactor()->create();
    $user->assignRole('user');
    $user->organizations()->each(fn ($org) => $org->removeMember($user));
    $org2 = Organization::factory()->create();
    $org2->addMember($user, 'member');

    expect($superAdmin->canImpersonate())->toBeTrue()
        ->and($admin->canImpersonate())->toBeFalse()
        ->and($user->canImpersonate())->toBeFalse();
});

test('org admin can impersonate when feature is active', function (): void {
    $org = Organization::factory()->create();
    $orgAdmin = User::factory()->withoutTwoFactor()->create();
    $orgAdmin->assignRole('user');

    $org->addMember($orgAdmin, 'admin');

    expect($orgAdmin->canImpersonate())->toBeTrue();
});

test('org admin cannot impersonate when impersonation feature is inactive', function (): void {
    $org = Organization::factory()->create();
    $orgAdmin = User::factory()->withoutTwoFactor()->create();
    $orgAdmin->assignRole('user');

    $org->addMember($orgAdmin, 'admin');
    Feature::for($orgAdmin)->deactivate(ImpersonationFeature::class);

    expect($orgAdmin->canImpersonate())->toBeFalse();
});

test('org admin can impersonate only same-org member', function (): void {
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();

    $orgAdmin = User::factory()->withoutTwoFactor()->create();
    $orgAdmin->assignRole('user');

    $orgA->addMember($orgAdmin, 'admin');

    $memberA = User::factory()->withoutTwoFactor()->create();
    $memberA->assignRole('user');

    $orgA->addMember($memberA, 'member');

    $memberB = User::factory()->withoutTwoFactor()->create();
    $memberB->assignRole('user');

    $orgB->addMember($memberB, 'member');

    $this->actingAs($orgAdmin);

    expect($memberA->canBeImpersonated())->toBeTrue()
        ->and($memberB->canBeImpersonated())->toBeFalse();
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

    $this->actingAs($superAdmin);

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

test('actions during impersonation are logged with impersonator as causer', function (): void {
    $superAdmin = User::factory()->withoutTwoFactor()->create(['name' => 'Super Admin']);
    $superAdmin->assignRole('super-admin');

    $target = User::factory()->withoutTwoFactor()->create(['name' => 'Target User']);
    $target->assignRole('user');

    $this->actingAs($superAdmin);
    Impersonation::enter($superAdmin, $target, 'web');
    $this->actingAs($target);

    resolve(App\Services\ActivityLogRbac::class)->logRolesAssigned($target, ['user']);

    $activity = Activity::query()
        ->where('description', ActivityType::RolesAssigned->value)
        ->where('subject_id', $target->getKey())
        ->where('subject_type', User::class)
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($superAdmin->getKey())
        ->and($activity->causer_type)->toBe(User::class);
});
