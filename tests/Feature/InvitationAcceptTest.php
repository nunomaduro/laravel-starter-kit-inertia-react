<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;

it('shows accept invitation page for valid token', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $org = Organization::factory()->create();
    $org->addMember($user, 'admin');

    $invitation = OrganizationInvitation::factory()->create([
        'organization_id' => $org->id,
        'email' => $user->email,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user)->get(route('invitations.show', ['token' => $invitation->token]));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('invitations/accept')
            ->has('invitation')
        );
});

it('accepts invitation for authenticated user', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $org = Organization::factory()->create();
    $invitation = OrganizationInvitation::factory()->create([
        'organization_id' => $org->id,
        'email' => $user->email,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user)->post(route('invitations.accept', ['token' => $invitation->token]));

    $response->assertRedirect();

    expect($user->belongsToOrganization($org->id))->toBeTrue();
});
