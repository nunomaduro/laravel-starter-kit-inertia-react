<?php

declare(strict_types=1);

use App\Models\AuditLog;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function (): void {
    Permission::findOrCreate('access admin panel', 'web');
});

it('allows admin to view any audit logs', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->givePermissionTo('access admin panel');

    expect($admin->can('viewAny', AuditLog::class))->toBeTrue();
});

it('denies non-admin from viewing any audit logs', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    expect($user->can('viewAny', AuditLog::class))->toBeFalse();
});

it('allows admin to view an audit log', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->givePermissionTo('access admin panel');
    $auditLog = AuditLog::factory()->create();

    expect($admin->can('view', $auditLog))->toBeTrue();
});

it('denies non-admin from viewing an audit log', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $auditLog = AuditLog::factory()->create();

    expect($user->can('view', $auditLog))->toBeFalse();
});

it('denies anyone from creating an audit log', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->givePermissionTo('access admin panel');

    expect($admin->can('create', AuditLog::class))->toBeFalse();
});

it('denies anyone from updating an audit log', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->givePermissionTo('access admin panel');
    $auditLog = AuditLog::factory()->create();

    expect($admin->can('update', $auditLog))->toBeFalse();
});

it('denies anyone from deleting an audit log', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->givePermissionTo('access admin panel');
    $auditLog = AuditLog::factory()->create();

    expect($admin->can('delete', $auditLog))->toBeFalse();
});

it('denies anyone from restoring an audit log', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->givePermissionTo('access admin panel');
    $auditLog = AuditLog::factory()->create();

    expect($admin->can('restore', $auditLog))->toBeFalse();
});

it('denies anyone from force deleting an audit log', function (): void {
    $admin = User::factory()->withoutTwoFactor()->create();
    $admin->givePermissionTo('access admin panel');
    $auditLog = AuditLog::factory()->create();

    expect($admin->can('forceDelete', $auditLog))->toBeFalse();
});
