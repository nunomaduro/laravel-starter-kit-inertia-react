<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\actingAs;

test('api chat accepts context payload without validation error', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    // Set a dummy API key so we get past the provider check — the request will
    // fail at the streaming stage, but we only care that context passes validation.
    Config::set('ai.default', 'openai');
    Config::set('ai.providers.openai.key', 'sk-test-fake-key');

    $response = actingAs($user, 'sanctum')->postJson('/api/chat', [
        'messages' => [['role' => 'user', 'content' => 'Hello']],
        'context' => [
            'page' => '/contacts/42',
            'entity_type' => 'contact',
            'entity_id' => 42,
            'entity_name' => 'Jane Doe',
        ],
    ]);

    // Should NOT be 422 — context fields pass validation.
    // The request may return 200 (streaming) or 502 (AI provider unreachable in test),
    // but it must never be a 422 validation error.
    expect($response->status())->not->toBe(422);
});

test('api chat accepts empty context payload', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    Config::set('ai.default', 'openai');
    Config::set('ai.providers.openai.key', 'sk-test-fake-key');

    $response = actingAs($user, 'sanctum')->postJson('/api/chat', [
        'messages' => [['role' => 'user', 'content' => 'Hello']],
        'context' => [],
    ]);

    expect($response->status())->not->toBe(422);
});

test('api chat accepts request without context field', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    Config::set('ai.default', 'openai');
    Config::set('ai.providers.openai.key', 'sk-test-fake-key');

    $response = actingAs($user, 'sanctum')->postJson('/api/chat', [
        'messages' => [['role' => 'user', 'content' => 'Hello']],
    ]);

    // Should pass validation (context is nullable).
    expect($response->status())->not->toBe(422);
});

test('api chat rejects invalid context entity_id type', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $response = actingAs($user, 'sanctum')->postJson('/api/chat', [
        'messages' => [['role' => 'user', 'content' => 'Hello']],
        'context' => [
            'page' => '/contacts/42',
            'entity_id' => 'not-a-number',
        ],
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('context.entity_id');
});
