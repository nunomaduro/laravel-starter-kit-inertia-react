<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;
use App\Services\TenantContext;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Artisan;
use Modules\PageBuilder\Models\Page;

afterEach(function (): void {
    TenantContext::forget();
});

it('redirects when tenant context is missing', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::factory()->withoutTwoFactor()->create();
    $user->organizations()->detach();
    TenantContext::forget();
    $org = Organization::factory()->create();
    $page = Page::factory()->for($org)->published()->create(['slug' => 'about']);

    $response = $this->actingAs($user)->get(route('pages.show', $page->slug));

    $response->assertRedirect(route('dashboard'));
});

it('shows published page when tenant is set', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::factory()->withoutTwoFactor()->create();
    $org = $user->defaultOrganization();
    if (! $org) {
        $org = Organization::factory()->create();
        $org->addMember($user, 'admin');
    }

    Artisan::call('permission:sync', ['--silent' => true]);
    $page = Page::factory()->for($org)->published()->create(['slug' => 'about']);

    TenantContext::set($org);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->get(route('pages.show', $page->slug));

    $response->assertOk()
        ->assertInertia(fn ($p) => $p
            ->component('pages/show')
            ->has('page')
            ->where('page.slug', 'about')
        );
});

it('shows published page for authenticated user with tenant', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::factory()->withoutTwoFactor()->create();
    $org = $user->defaultOrganization();
    if (! $org) {
        $org = Organization::factory()->create();
        $org->addMember($user, 'admin');
    }

    Artisan::call('permission:sync', ['--silent' => true]);
    $page = Page::factory()->for($org)->published()->create(['slug' => 'welcome']);

    TenantContext::set($org);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->get(route('pages.show', $page->slug));

    $response->assertOk()
        ->assertInertia(fn ($p) => $p
            ->component('pages/show')
            ->where('page.name', $page->name)
        );
});

it('denies viewing draft page when user cannot manage pages', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $org = Organization::factory()->create();
    $user = User::factory()->withoutTwoFactor()->create(['onboarding_completed' => true]);
    $org->addMember($user, 'member');
    Artisan::call('permission:sync', ['--silent' => true]);
    $page = Page::factory()->for($org)->create(['slug' => 'draft', 'is_published' => false]);

    TenantContext::set($org);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->get(route('pages.show', $page->slug));

    $response->assertForbidden();
});

it('returns 404 for non-existent slug', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::factory()->withoutTwoFactor()->create(['onboarding_completed' => true]);
    $org = $user->defaultOrganization();
    if (! $org) {
        $org = Organization::factory()->create();
        $org->addMember($user, 'admin');
    }

    TenantContext::set($org);

    $response = $this->actingAs($user)
        ->withSession(['current_organization_id' => $org->id])
        ->get(route('pages.show', 'non-existent'));

    $response->assertNotFound();
});
