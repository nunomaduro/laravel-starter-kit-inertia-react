<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('unauthenticated get conversations returns 401', function (): void {
    getJson('/api/conversations')->assertUnauthorized();
});

test('get conversations returns only current user conversations', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();

    $userConvId = (string) Illuminate\Support\Str::uuid();
    $otherConvId = (string) Illuminate\Support\Str::uuid();
    DB::table('agent_conversations')->insert([
        ['id' => $userConvId, 'user_id' => $user->id, 'title' => 'Mine', 'created_at' => now(), 'updated_at' => now()],
        ['id' => $otherConvId, 'user_id' => $other->id, 'title' => 'Other', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $response = actingAs($user, 'sanctum')->getJson('/api/conversations');

    $response->assertOk();

    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['id'])->toBe($userConvId);
    expect($data[0]['title'])->toBe('Mine');
});

test('get conversation by id returns 200 when owner', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $convId = (string) Illuminate\Support\Str::uuid();
    DB::table('agent_conversations')->insert([
        'id' => $convId,
        'user_id' => $user->id,
        'title' => 'My conv',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = actingAs($user, 'sanctum')->getJson('/api/conversations/'.$convId);

    $response->assertOk();
    $response->assertJsonPath('data.id', $convId);
    $response->assertJsonPath('data.title', 'My conv');
    $response->assertJsonPath('data.messages', []);
});

test('get conversation by id returns 404 when not owner', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $convId = (string) Illuminate\Support\Str::uuid();
    DB::table('agent_conversations')->insert([
        'id' => $convId,
        'user_id' => $other->id,
        'title' => 'Other conv',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = actingAs($user, 'sanctum')->getJson('/api/conversations/'.$convId);

    $response->assertNotFound();
});

test('get conversation by id returns 404 when not found', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $fakeId = (string) Illuminate\Support\Str::uuid();

    $response = actingAs($user, 'sanctum')->getJson('/api/conversations/'.$fakeId);

    $response->assertNotFound();
});
