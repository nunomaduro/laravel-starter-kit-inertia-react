<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;
use App\Services\PageDataSourceRegistry;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('returns empty array for unregistered data source key', function (): void {
    $registry = App::make(PageDataSourceRegistry::class);
    $org = Organization::factory()->create();
    $user = User::factory()->withoutTwoFactor()->create();

    $result = $registry->resolve('unknown_key', $org, $user, []);

    expect($result)->toBeArray()->toBeEmpty();
});

it('returns keys including defaults', function (): void {
    $registry = App::make(PageDataSourceRegistry::class);
    $keys = $registry->keys();

    expect($keys)->toContain('members', 'invoices');
});

it('resolves members when user has org.members.view', function (): void {
    Artisan::call('permission:sync', ['--silent' => true]);
    $registry = App::make(PageDataSourceRegistry::class);
    $org = Organization::factory()->create();
    $user = User::factory()->withoutTwoFactor()->create();
    $org->addMember($user, 'admin');

    $result = $registry->resolve('members', $org, $user, []);

    expect($result)->toBeArray();
});

it('returns empty for members when user is guest', function (): void {
    $registry = App::make(PageDataSourceRegistry::class);
    $org = Organization::factory()->create();

    $result = $registry->resolve('members', $org, null, []);

    expect($result)->toBeArray()->toBeEmpty();
});
