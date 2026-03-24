<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Pennant\Feature;
use Laravel\Sanctum\Sanctum;

/*
|--------------------------------------------------------------------------
| Task 26: API Endpoint Security Tests
|--------------------------------------------------------------------------
|
| Verify authentication, rate limiting, and input validation on API routes.
|
*/

// ── Unauthenticated requests return 401 ──────────────────────────────────

it('returns 401 for unauthenticated chat request', function (): void {
    $this->postJson('/api/chat')
        ->assertUnauthorized();
});

it('returns 401 for unauthenticated chat memories request', function (): void {
    $this->getJson('/api/chat/memories')
        ->assertUnauthorized();
});

it('returns 401 for unauthenticated conversations index', function (): void {
    $this->getJson('/api/conversations')
        ->assertUnauthorized();
});

it('returns 401 for unauthenticated conversations show', function (): void {
    $this->getJson('/api/conversations/1')
        ->assertUnauthorized();
});

it('returns 401 for unauthenticated conversations update', function (): void {
    $this->patchJson('/api/conversations/1')
        ->assertUnauthorized();
});

it('returns 401 for unauthenticated conversations destroy', function (): void {
    $this->deleteJson('/api/conversations/1')
        ->assertUnauthorized();
});

it('returns 401 for unauthenticated v1 users index', function (): void {
    $this->getJson('/api/v1/users')
        ->assertUnauthorized();
});

it('returns 401 for unauthenticated v1 users store', function (): void {
    $this->postJson('/api/v1/users')
        ->assertUnauthorized();
});

it('returns 401 for unauthenticated v1 users show', function (): void {
    $this->getJson('/api/v1/users/1')
        ->assertUnauthorized();
});

it('returns 401 for unauthenticated v1 users update', function (): void {
    $this->putJson('/api/v1/users/1')
        ->assertUnauthorized();
});

it('returns 401 for unauthenticated v1 users destroy', function (): void {
    $this->deleteJson('/api/v1/users/1')
        ->assertUnauthorized();
});

it('returns 401 for unauthenticated v1 users batch', function (): void {
    $this->postJson('/api/v1/users/batch')
        ->assertUnauthorized();
});

it('returns 401 for unauthenticated v1 users search', function (): void {
    $this->postJson('/api/v1/users/search')
        ->assertUnauthorized();
});

// ── Rate limiting returns 429 ────────────────────────────────────────────

it('returns 429 after exceeding chat rate limit', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    Sanctum::actingAs($user);

    // Chat group has throttle:30,1 — send 31 requests
    for ($i = 0; $i < 30; $i++) {
        $this->postJson('/api/chat', ['message' => 'test']);
    }

    $this->postJson('/api/chat', ['message' => 'test'])
        ->assertStatus(429);
});

it('returns 429 after exceeding v1 rate limit', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    Sanctum::actingAs($user);

    Feature::define('api_access', fn () => true);

    // V1 group has throttle:60,1 — send 61 requests
    for ($i = 0; $i < 60; $i++) {
        $this->getJson('/api/v1/users');
    }

    $this->getJson('/api/v1/users')
        ->assertStatus(429);
});

// ── Public endpoints are accessible ──────────────────────────────────────

it('allows unauthenticated access to api root', function (): void {
    $this->getJson('/api/')
        ->assertOk()
        ->assertJsonStructure(['name', 'version', 'message']);
});

it('allows unauthenticated access to v1 info', function (): void {
    $this->getJson('/api/v1/')
        ->assertOk()
        ->assertJsonStructure(['name', 'version', 'message']);
});

// ── Invalid input returns 422 ────────────────────────────────────────────

it('returns 422 for invalid v1 user store input', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    Sanctum::actingAs($user);

    Feature::define('api_access', fn () => true);

    $this->postJson('/api/v1/users', [
        'name' => '', // required
        'email' => 'not-an-email',
        'password' => 'short',
    ])->assertUnprocessable();
});

it('returns 422 for duplicate email on v1 user store', function (): void {
    $existing = User::factory()->withoutTwoFactor()->create();
    Sanctum::actingAs($existing);

    Feature::define('api_access', fn () => true);

    $this->postJson('/api/v1/users', [
        'name' => 'Duplicate',
        'email' => $existing->email, // duplicate
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertUnprocessable();
});
