<?php

declare(strict_types=1);

use App\Models\TermsVersion;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function (): void {
    Permission::findOrCreate('access admin panel', 'web');
});

it('allows any authenticated user to view any terms versions', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    expect($user->can('viewAny', TermsVersion::class))->toBeTrue();
});

it('allows any authenticated user to view a terms version', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $termsVersion = TermsVersion::factory()->create();

    expect($user->can('view', $termsVersion))->toBeTrue();
});

it('allows admin to create a terms version', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->givePermissionTo('access admin panel');

    expect($admin->can('create', TermsVersion::class))->toBeTrue();
});

it('denies non-admin from creating a terms version', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    expect($user->can('create', TermsVersion::class))->toBeFalse();
});

it('allows admin to update a terms version', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->givePermissionTo('access admin panel');
    $termsVersion = TermsVersion::factory()->create();

    expect($admin->can('update', $termsVersion))->toBeTrue();
});

it('denies non-admin from updating a terms version', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $termsVersion = TermsVersion::factory()->create();

    expect($user->can('update', $termsVersion))->toBeFalse();
});

it('allows admin to delete a terms version', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->givePermissionTo('access admin panel');
    $termsVersion = TermsVersion::factory()->create();

    expect($admin->can('delete', $termsVersion))->toBeTrue();
});

it('denies non-admin from deleting a terms version', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $termsVersion = TermsVersion::factory()->create();

    expect($user->can('delete', $termsVersion))->toBeFalse();
});

it('allows admin to restore a terms version', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->givePermissionTo('access admin panel');
    $termsVersion = TermsVersion::factory()->create();

    expect($admin->can('restore', $termsVersion))->toBeTrue();
});

it('denies non-admin from restoring a terms version', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $termsVersion = TermsVersion::factory()->create();

    expect($user->can('restore', $termsVersion))->toBeFalse();
});

it('allows admin to force delete a terms version', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->givePermissionTo('access admin panel');
    $termsVersion = TermsVersion::factory()->create();

    expect($admin->can('forceDelete', $termsVersion))->toBeTrue();
});

it('denies non-admin from force deleting a terms version', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $termsVersion = TermsVersion::factory()->create();

    expect($user->can('forceDelete', $termsVersion))->toBeFalse();
});
