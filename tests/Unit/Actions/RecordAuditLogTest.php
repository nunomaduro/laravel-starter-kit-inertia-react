<?php

declare(strict_types=1);

use App\Actions\RecordAuditLog;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;

it('creates an audit log entry with all fields', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $org = Organization::factory()->create();
    $this->actingAs($user);

    $action = resolve(RecordAuditLog::class);

    $log = $action->handle(
        action: 'user.updated',
        subjectType: 'user',
        subjectId: $user->id,
        oldValue: ['name' => 'Old'],
        newValue: ['name' => 'New'],
        organizationId: $org->id,
        actorId: $user->id,
    );

    expect($log)->toBeInstanceOf(AuditLog::class)
        ->and($log->action)->toBe('user.updated')
        ->and($log->subject_type)->toBe('user')
        ->and($log->subject_id)->toBe((string) $user->id)
        ->and($log->old_value)->toBe(['name' => 'Old'])
        ->and($log->new_value)->toBe(['name' => 'New'])
        ->and($log->actor_id)->toBe($user->id)
        ->and($log->organization_id)->toBe($org->id);
});

it('defaults actor_id to the authenticated user', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $this->actingAs($user);

    $action = resolve(RecordAuditLog::class);
    $log = $action->handle(action: 'test.default.actor');

    expect($log->actor_id)->toBe($user->id);
});

it('wraps scalar old_value and new_value in arrays', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $this->actingAs($user);

    $action = resolve(RecordAuditLog::class);
    $log = $action->handle(
        action: 'test.scalar',
        oldValue: 'old-scalar',
        newValue: 'new-scalar',
    );

    expect($log->old_value)->toBe(['value' => 'old-scalar'])
        ->and($log->new_value)->toBe(['value' => 'new-scalar']);
});

it('stores null for old_value and new_value when not provided', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $this->actingAs($user);

    $action = resolve(RecordAuditLog::class);
    $log = $action->handle(action: 'test.null.values');

    expect($log->old_value)->toBeNull()
        ->and($log->new_value)->toBeNull();
});
