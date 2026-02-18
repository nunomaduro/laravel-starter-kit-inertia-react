<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Config;

beforeEach(function (): void {
    Config::set('tenancy.enabled', false);
});

afterEach(function (): void {
    Config::set('tenancy.enabled', true);
});

it('redirects organizations index to dashboard when tenancy disabled', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $org = Organization::factory()->create();
    $org->addMember($user, 'admin');

    $response = $this->actingAs($user)->get(route('organizations.index'));

    $response->assertRedirect(route('dashboard'));
});

it('redirects organizations create to dashboard when tenancy disabled', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $response = $this->actingAs($user)->get(route('organizations.create'));

    $response->assertRedirect(route('dashboard'));
});

it('redirects organization show to dashboard when tenancy disabled', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $org = Organization::factory()->create();
    $org->addMember($user, 'admin');

    $response = $this->actingAs($user)->get(route('organizations.show', $org));

    $response->assertRedirect(route('dashboard'));
});

it('redirects organizations switch to dashboard when tenancy disabled', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $org = Organization::factory()->create();
    $org->addMember($user, 'admin');

    $response = $this->actingAs($user)->post(route('organizations.switch'), [
        'organization_id' => $org->id,
    ]);

    $response->assertRedirect(route('dashboard'));
});
