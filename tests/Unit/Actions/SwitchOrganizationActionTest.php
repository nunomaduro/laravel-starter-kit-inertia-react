<?php

declare(strict_types=1);

use App\Actions\SwitchOrganizationAction;
use App\Models\Organization;
use App\Models\User;

it('switches the user current organization', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($user->id, [
        'is_default' => false,
        'joined_at' => now(),
    ]);

    $action = resolve(SwitchOrganizationAction::class);
    $result = $action->handle($user, $org);

    expect($result)->toBeTrue();
});

it('accepts an organization id integer', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $org = Organization::factory()->create();
    $org->users()->attach($user->id, [
        'is_default' => false,
        'joined_at' => now(),
    ]);

    $action = resolve(SwitchOrganizationAction::class);
    $result = $action->handle($user, $org->id);

    expect($result)->toBeTrue();
});
