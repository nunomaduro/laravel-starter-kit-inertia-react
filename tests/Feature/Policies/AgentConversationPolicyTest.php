<?php

declare(strict_types=1);

use App\Models\AgentConversation;
use App\Models\User;

it('allows any authenticated user to view any agent conversations', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    expect($user->can('viewAny', AgentConversation::class))->toBeTrue();
});

it('allows owner to view their agent conversation', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $conversation = AgentConversation::factory()->create(['user_id' => $user->id]);

    expect($user->can('view', $conversation))->toBeTrue();
});

it('denies non-owner from viewing another users agent conversation', function (): void {
    $owner = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $conversation = AgentConversation::factory()->create(['user_id' => $owner->id]);

    expect($other->can('view', $conversation))->toBeFalse();
});

it('allows any authenticated user to create an agent conversation', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    expect($user->can('create', AgentConversation::class))->toBeTrue();
});

it('allows owner to update their agent conversation', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $conversation = AgentConversation::factory()->create(['user_id' => $user->id]);

    expect($user->can('update', $conversation))->toBeTrue();
});

it('denies non-owner from updating another users agent conversation', function (): void {
    $owner = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $conversation = AgentConversation::factory()->create(['user_id' => $owner->id]);

    expect($other->can('update', $conversation))->toBeFalse();
});

it('allows owner to delete their agent conversation', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $conversation = AgentConversation::factory()->create(['user_id' => $user->id]);

    expect($user->can('delete', $conversation))->toBeTrue();
});

it('denies non-owner from deleting another users agent conversation', function (): void {
    $owner = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $conversation = AgentConversation::factory()->create(['user_id' => $owner->id]);

    expect($other->can('delete', $conversation))->toBeFalse();
});

it('allows owner to restore their agent conversation', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $conversation = AgentConversation::factory()->create(['user_id' => $user->id]);

    expect($user->can('restore', $conversation))->toBeTrue();
});

it('denies non-owner from restoring another users agent conversation', function (): void {
    $owner = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $conversation = AgentConversation::factory()->create(['user_id' => $owner->id]);

    expect($other->can('restore', $conversation))->toBeFalse();
});

it('allows owner to force delete their agent conversation', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $conversation = AgentConversation::factory()->create(['user_id' => $user->id]);

    expect($user->can('forceDelete', $conversation))->toBeTrue();
});

it('denies non-owner from force deleting another users agent conversation', function (): void {
    $owner = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $conversation = AgentConversation::factory()->create(['user_id' => $owner->id]);

    expect($other->can('forceDelete', $conversation))->toBeFalse();
});
