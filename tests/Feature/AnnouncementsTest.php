<?php

declare(strict_types=1);

use App\Actions\CreateOrganizationAction;
use App\Models\Announcement;
use App\Models\User;
use App\Services\TenantContext;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('shows active announcements in shared props on dashboard', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $org = resolve(CreateOrganizationAction::class)->handle($user, 'Test Org');
    TenantContext::set($org);

    Announcement::query()->create([
        'title' => 'Global notice',
        'body' => 'A global message.',
        'scope' => App\Enums\AnnouncementScope::Global,
        'organization_id' => null,
        'starts_at' => null,
        'ends_at' => null,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('announcements')
            ->where('announcements.0.title', 'Global notice')
            ->where('announcements.0.body', 'A global message.')
        );
});

it('excludes inactive or expired announcements from shared props', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $org = resolve(CreateOrganizationAction::class)->handle($user, 'Test Org');
    TenantContext::set($org);

    Announcement::query()->create([
        'title' => 'Expired',
        'body' => 'Old.',
        'scope' => App\Enums\AnnouncementScope::Global,
        'organization_id' => null,
        'starts_at' => now()->subDays(2),
        'ends_at' => now()->subDay(),
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('announcements')
            ->where('announcements', [])
        );
});
