<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Laravel\Pennant\Feature;
use Modules\Blog\Features\BlogFeature;

test('globally disabled feature returns 404 for all users including super-admin', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    config(['feature-flags.globally_disabled' => ['blog']]);

    $user = User::factory()->withoutTwoFactor()->create([
        'onboarding_completed' => true,
    ]);
    $user->assignRole('super-admin');
    Feature::for($user)->activate(BlogFeature::class);

    $this->actingAs($user)
        ->get(route('blog.index'))
        ->assertNotFound();
});

test('authenticated user with blog feature inactive receives 404 on blog index', function (): void {
    $user = User::factory()->withoutTwoFactor()->create([
        'onboarding_completed' => true,
    ]);
    Feature::for($user)->deactivate(BlogFeature::class);

    $this->actingAs($user)
        ->get(route('blog.index'))
        ->assertNotFound();
});

test('guest can access blog index when default is on', function (): void {
    $this->get(route('blog.index'))
        ->assertOk();
});

test('authenticated user with blog feature active can access blog index', function (): void {
    $user = User::factory()->withoutTwoFactor()->create([
        'onboarding_completed' => true,
    ]);

    $this->actingAs($user)
        ->get(route('blog.index'))
        ->assertOk();
});
