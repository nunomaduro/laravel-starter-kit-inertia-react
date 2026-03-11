<?php

declare(strict_types=1);

use App\Actions\CreateOrganizationAction;
use App\Enums\AnnouncementScope;
use App\Models\Announcement;
use App\Models\User;
use App\Services\TenantContext;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('allows super-admin to create and update global and org announcements', function (): void {
    $superAdmin = User::factory()->withoutTwoFactor()->create();
    $superAdmin->assignRole('super-admin');

    expect(Gate::forUser($superAdmin)->allows('create', Announcement::class))->toBeTrue();

    $global = Announcement::query()->create([
        'title' => 'Global',
        'body' => 'Body',
        'scope' => AnnouncementScope::Global,
        'organization_id' => null,
        'is_active' => true,
        'created_by' => $superAdmin->id,
    ]);
    expect(Gate::forUser($superAdmin)->allows('update', $global))->toBeTrue();
    expect(Gate::forUser($superAdmin)->allows('delete', $global))->toBeTrue();
});

it('allows org admin to create announcement when tenant context is set', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $org = resolve(CreateOrganizationAction::class)->handle($admin, 'Test Org');
    resolve(App\Services\Organization\OrganizationRoleService::class)->syncRolePermissions($org);

    TenantContext::set($org);

    expect(Gate::forUser($admin)->allows('create', Announcement::class))->toBeTrue();
});

it('denies org admin from updating global announcement', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $org = resolve(CreateOrganizationAction::class)->handle($admin, 'Test Org');

    $global = Announcement::query()->create([
        'title' => 'Global',
        'body' => 'Body',
        'scope' => AnnouncementScope::Global,
        'organization_id' => null,
        'is_active' => true,
        'created_by' => null,
    ]);

    TenantContext::set($org);
    expect(Gate::forUser($admin)->denies('update', $global))->toBeTrue();
});

it('allows org admin to update own org announcement', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $org = resolve(CreateOrganizationAction::class)->handle($admin, 'Test Org');
    resolve(App\Services\Organization\OrganizationRoleService::class)->syncRolePermissions($org);

    $orgAnnouncement = Announcement::query()->create([
        'title' => 'Org',
        'body' => 'Body',
        'scope' => AnnouncementScope::Organization,
        'organization_id' => $org->id,
        'is_active' => true,
        'created_by' => $admin->id,
    ]);

    TenantContext::set($org);
    expect(Gate::forUser($admin)->allows('update', $orgAnnouncement))->toBeTrue();
});

it('denies member from creating announcement', function (): void {
    $owner = User::factory()->withoutTwoFactor()->create();
    $org = resolve(CreateOrganizationAction::class)->handle($owner, 'Test Org');
    $member = User::factory()->withoutTwoFactor()->create();
    $org->users()->attach($member->id, ['is_default' => false, 'joined_at' => now(), 'invited_by' => $owner->id]);

    TenantContext::set($org);
    $member->assignRole('member');

    expect(Gate::forUser($member)->denies('create', Announcement::class))->toBeTrue();
});
