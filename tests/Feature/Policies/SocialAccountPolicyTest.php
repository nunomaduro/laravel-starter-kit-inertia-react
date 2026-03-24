<?php

declare(strict_types=1);

use App\Models\SocialAccount;
use App\Models\User;

it('allows any authenticated user to view any social accounts', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    expect($user->can('viewAny', SocialAccount::class))->toBeTrue();
});

it('allows owner to view their social account', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $account = SocialAccount::factory()->create(['user_id' => $user->id]);

    expect($user->can('view', $account))->toBeTrue();
});

it('denies non-owner from viewing another users social account', function (): void {
    $owner = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $account = SocialAccount::factory()->create(['user_id' => $owner->id]);

    expect($other->can('view', $account))->toBeFalse();
});

it('allows any authenticated user to create a social account', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    expect($user->can('create', SocialAccount::class))->toBeTrue();
});

it('allows owner to update their social account', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $account = SocialAccount::factory()->create(['user_id' => $user->id]);

    expect($user->can('update', $account))->toBeTrue();
});

it('denies non-owner from updating another users social account', function (): void {
    $owner = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $account = SocialAccount::factory()->create(['user_id' => $owner->id]);

    expect($other->can('update', $account))->toBeFalse();
});

it('allows owner to delete their social account', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $account = SocialAccount::factory()->create(['user_id' => $user->id]);

    expect($user->can('delete', $account))->toBeTrue();
});

it('denies non-owner from deleting another users social account', function (): void {
    $owner = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $account = SocialAccount::factory()->create(['user_id' => $owner->id]);

    expect($other->can('delete', $account))->toBeFalse();
});

it('allows owner to restore their social account', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $account = SocialAccount::factory()->create(['user_id' => $user->id]);

    expect($user->can('restore', $account))->toBeTrue();
});

it('denies non-owner from restoring another users social account', function (): void {
    $owner = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $account = SocialAccount::factory()->create(['user_id' => $owner->id]);

    expect($other->can('restore', $account))->toBeFalse();
});

it('allows owner to force delete their social account', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $account = SocialAccount::factory()->create(['user_id' => $user->id]);

    expect($user->can('forceDelete', $account))->toBeTrue();
});

it('denies non-owner from force deleting another users social account', function (): void {
    $owner = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $account = SocialAccount::factory()->create(['user_id' => $owner->id]);

    expect($other->can('forceDelete', $account))->toBeFalse();
});
