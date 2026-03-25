<?php

declare(strict_types=1);

use App\Models\DataTableSavedView;
use App\Models\Organization;
use App\Models\User;
use App\Services\TenantContext;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Permission;

/*
|--------------------------------------------------------------------------
| DataTable Saved Views — Shared Views Feature Tests
|--------------------------------------------------------------------------
|
| Covers: grouped view retrieval, creating private/shared/system views,
| permission checks for system views, and delete authorization.
|
*/

beforeEach(function (): void {
    $this->org = Organization::factory()->create();
    $this->user = createTestUser();
    $this->org->users()->attach($this->user);
    TenantContext::set($this->org);
});

// ── Grouped Views ────────────────────────────────────────────────────────

it('returns grouped views for the current user and org', function (): void {
    $otherUser = createTestUser();
    $this->org->users()->attach($otherUser);

    // Private view by current user
    DataTableSavedView::factory()->forUser($this->user)->forTable('products')->create(['name' => 'My View']);

    // Private view by another user (should not appear)
    DataTableSavedView::factory()->forUser($otherUser)->forTable('products')->create(['name' => 'Other Private']);

    // Team shared view
    DataTableSavedView::factory()->shared($this->org, $otherUser)->forTable('products')->create(['name' => 'Team View']);

    // System view
    DataTableSavedView::factory()->system($this->org, $otherUser)->forTable('products')->create(['name' => 'System View']);

    // View for a different table (should not appear)
    DataTableSavedView::factory()->forUser($this->user)->forTable('orders')->create(['name' => 'Wrong Table']);

    $grouped = DataTableSavedView::grouped('products', $this->user->id, $this->org->id);

    expect($grouped['my_views'])->toHaveCount(1)
        ->and($grouped['my_views']->first()->name)->toBe('My View')
        ->and($grouped['team_views'])->toHaveCount(1)
        ->and($grouped['team_views']->first()->name)->toBe('Team View')
        ->and($grouped['system_views'])->toHaveCount(1)
        ->and($grouped['system_views']->first()->name)->toBe('System View');
});

// ── API Index ────────────────────────────────────────────────────────────

it('returns grouped views via API index', function (): void {
    DataTableSavedView::factory()->forUser($this->user)->forTable('products')->create(['name' => 'My API View']);
    DataTableSavedView::factory()->shared($this->org, $this->user)->forTable('products')->create(['name' => 'Shared API View']);

    $response = $this->actingAs($this->user)
        ->getJson('/api/data-table-saved-views?table_name=products');

    $response->assertOk()
        ->assertJsonCount(1, 'my_views')
        ->assertJsonCount(1, 'team_views')
        ->assertJsonCount(0, 'system_views');
});

it('requires table_name parameter', function (): void {
    $this->actingAs($this->user)
        ->getJson('/api/data-table-saved-views')
        ->assertUnprocessable();
});

// ── Create Private View ──────────────────────────────────────────────────

it('creates a private view for the current user', function (): void {
    $response = $this->actingAs($this->user)
        ->postJson('/api/data-table-saved-views', [
            'table_name' => 'products',
            'name' => 'My Custom View',
            'filters' => ['status' => 'active'],
            'sort' => '-created_at',
            'columns' => ['id', 'name', 'status'],
            'column_order' => ['id', 'name', 'status'],
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'My Custom View')
        ->assertJsonPath('data.is_shared', false)
        ->assertJsonPath('data.is_system', false);

    $this->assertDatabaseHas('data_table_saved_views', [
        'user_id' => $this->user->id,
        'table_name' => 'products',
        'name' => 'My Custom View',
        'is_shared' => false,
        'is_system' => false,
        'organization_id' => null,
        'created_by' => $this->user->id,
    ]);
});

// ── Create Shared View ───────────────────────────────────────────────────

it('creates a shared view scoped to the org', function (): void {
    $response = $this->actingAs($this->user)
        ->postJson('/api/data-table-saved-views', [
            'table_name' => 'products',
            'name' => 'Team Shared View',
            'is_shared' => true,
            'filters' => ['status' => 'active'],
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.is_shared', true);

    $this->assertDatabaseHas('data_table_saved_views', [
        'user_id' => $this->user->id,
        'table_name' => 'products',
        'name' => 'Team Shared View',
        'is_shared' => true,
        'organization_id' => $this->org->id,
        'created_by' => $this->user->id,
    ]);
});

// ── Shared Views — Intentionally Open (Design Decision) ─────────────

it('allows any authenticated user to create shared views without special permission', function (): void {
    // By design, shared views are open to all authenticated team members.
    // No additional permission gate is required — any user in the org
    // can share views with their team.
    $response = $this->actingAs($this->user)
        ->postJson('/api/data-table-saved-views', [
            'table_name' => 'products',
            'name' => 'Shared By Regular User',
            'is_shared' => true,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.is_shared', true);
});

// ── Cross-Tenant Delete Protection ──────────────────────────────────

it('prevents deleting a shared view from another organization', function (): void {
    $otherOrg = Organization::factory()->create();
    $otherUser = createTestUser();
    $otherOrg->users()->attach($otherUser);

    // Create a shared view in the other org
    TenantContext::set($otherOrg);
    $view = DataTableSavedView::factory()->shared($otherOrg, $otherUser)->forTable('products')->create();

    // Switch back to original org context and try to delete
    TenantContext::set($this->org);

    $this->actingAs($this->user)
        ->deleteJson("/api/data-table-saved-views/{$view->id}")
        ->assertForbidden();

    $this->assertDatabaseHas('data_table_saved_views', ['id' => $view->id]);
});

it('prevents deleting a private view belonging to another user', function (): void {
    $otherUser = createTestUser();

    // Private view with no org (belongs to otherUser)
    $view = DataTableSavedView::factory()->forUser($otherUser)->forTable('products')->create();

    $this->actingAs($this->user)
        ->deleteJson("/api/data-table-saved-views/{$view->id}")
        ->assertForbidden();

    $this->assertDatabaseHas('data_table_saved_views', ['id' => $view->id]);
});

// ── Create System View (permission check) ────────────────────────────────

it('rejects system view creation without permission', function (): void {
    $response = $this->actingAs($this->user)
        ->postJson('/api/data-table-saved-views', [
            'table_name' => 'products',
            'name' => 'System Wide View',
            'is_system' => true,
        ]);

    $response->assertForbidden();
});

it('allows system view creation with manage system views permission', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);

    // Create and assign permission
    $permission = Permission::firstOrCreate(
        ['name' => 'manage system views', 'guard_name' => 'web'],
    );
    $this->user->givePermissionTo($permission);

    $response = $this->actingAs($this->user)
        ->postJson('/api/data-table-saved-views', [
            'table_name' => 'products',
            'name' => 'System Wide View',
            'is_system' => true,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.is_system', true)
        ->assertJsonPath('data.is_shared', false);

    $this->assertDatabaseHas('data_table_saved_views', [
        'name' => 'System Wide View',
        'is_system' => true,
        'organization_id' => $this->org->id,
    ]);
});

// ── Delete ────────────────────────────────────────────────────────────────

it('allows creator to delete their own view', function (): void {
    $view = DataTableSavedView::factory()->forUser($this->user)->forTable('products')->create();

    $this->actingAs($this->user)
        ->deleteJson("/api/data-table-saved-views/{$view->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('data_table_saved_views', ['id' => $view->id]);
});

it('prevents non-creator from deleting a shared view', function (): void {
    $otherUser = createTestUser();
    $this->org->users()->attach($otherUser);

    $view = DataTableSavedView::factory()->shared($this->org, $otherUser)->forTable('products')->create();

    $this->actingAs($this->user)
        ->deleteJson("/api/data-table-saved-views/{$view->id}")
        ->assertForbidden();

    $this->assertDatabaseHas('data_table_saved_views', ['id' => $view->id]);
});

it('allows admin to delete any view in their org', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);

    $permission = Permission::firstOrCreate(
        ['name' => 'manage system views', 'guard_name' => 'web'],
    );
    $this->user->givePermissionTo($permission);

    $otherUser = createTestUser();
    $this->org->users()->attach($otherUser);
    $view = DataTableSavedView::factory()->shared($this->org, $otherUser)->forTable('products')->create();

    $this->actingAs($this->user)
        ->deleteJson("/api/data-table-saved-views/{$view->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('data_table_saved_views', ['id' => $view->id]);
});

// ── Model Relationships ──────────────────────────────────────────────────

it('has correct relationships', function (): void {
    $view = DataTableSavedView::factory()->shared($this->org, $this->user)->forTable('products')->create();

    expect($view->user)->toBeInstanceOf(User::class)
        ->and($view->creator)->toBeInstanceOf(User::class)
        ->and($view->organization)->toBeInstanceOf(Organization::class);
});
