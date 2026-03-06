<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

test('unauthenticated post to api chat returns 401', function (): void {
    $response = postJson('/api/chat', [
        'messages' => [['role' => 'user', 'content' => 'Hello']],
    ]);

    $response->assertUnauthorized();
});

test('api chat accepts TanStack UIMessage format with parts instead of content', function (): void {
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('Chat with memory requires PostgreSQL (pgvector).');
    }

    $user = User::factory()->withoutTwoFactor()->create();
    Eznix86\AI\Memory\Facades\AgentMemory::fake();
    Illuminate\Support\Facades\Http::fake([
        '*' => function ($request) {
            $uri = (string) $request->getUri();
            if (str_contains($uri, 'embeddings')) {
                return Illuminate\Support\Facades\Http::response([
                    'data' => [['embedding' => array_fill(0, 1536, 0.01), 'index' => 0]],
                    'usage' => ['prompt_tokens' => 1, 'total_tokens' => 1],
                ], 200);
            }

            if (str_contains($uri, 'chat/completions')) {
                $body = "data: {\"choices\":[{\"delta\":{\"content\":\"Hi\"},\"index\":0}]}\n"
                    ."data: {\"choices\":[{\"delta\":{},\"finish_reason\":\"stop\",\"index\":0}]}\n"
                    ."data: [DONE]\n";

                return new Illuminate\Http\Client\Response(200, ['Content-Type' => 'text/event-stream'], $body);
            }

            return Illuminate\Support\Facades\Http::response([], 200);
        },
    ]);

    $response = actingAs($user, 'sanctum')->postJson('/api/chat', [
        'messages' => [
            [
                'id' => 'msg-1',
                'role' => 'user',
                'parts' => [['type' => 'text', 'content' => 'Hello from parts']],
            ],
        ],
    ]);

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/x-ndjson');
});

test('api chat returns 422 when messages are missing', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $response = actingAs($user, 'sanctum')->postJson('/api/chat', []);

    $response->assertUnprocessable();
    $response->assertJsonFragment(['detail' => 'The messages field is required.']);
});

test('api chat returns 503 when AI provider has no API key', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    Config::set('ai.default', 'openai');
    Config::set('ai.providers.openai.key');

    $response = actingAs($user, 'sanctum')->postJson('/api/chat', [
        'messages' => [['role' => 'user', 'content' => 'Hi']],
    ]);

    $response->assertStatus(503);
    $response->assertJsonPath('message', 'AI provider is not configured. Set OPENAI_API_KEY in your .env.');
});

test('api chat returns 422 when conversation_id is invalid', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $fakeUuid = '00000000-0000-0000-0000-000000000001';

    $response = actingAs($user, 'sanctum')->postJson('/api/chat', [
        'messages' => [['role' => 'user', 'content' => 'Hi']],
        'conversation_id' => $fakeUuid,
    ]);

    $response->assertUnprocessable();
});

test('api chat returns 422 when conversation_id belongs to another user', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $other = User::factory()->withoutTwoFactor()->create();
    $convId = (string) Illuminate\Support\Str::uuid();
    DB::table('agent_conversations')->insert([
        'id' => $convId,
        'user_id' => $other->id,
        'title' => 'Other',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = actingAs($user, 'sanctum')->postJson('/api/chat', [
        'messages' => [['role' => 'user', 'content' => 'Hi']],
        'conversation_id' => $convId,
    ]);

    $response->assertUnprocessable();
});

test('api chat without conversation_id creates a conversation for the user', function (): void {
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('Chat with memory requires PostgreSQL (pgvector).');
    }

    $user = User::factory()->withoutTwoFactor()->create();
    $countBefore = DB::table('agent_conversations')->where('user_id', $user->id)->count();

    Eznix86\AI\Memory\Facades\AgentMemory::fake();
    Illuminate\Support\Facades\Http::fake([
        '*' => function ($request) {
            $uri = (string) $request->getUri();
            if (str_contains($uri, 'embeddings')) {
                return Illuminate\Support\Facades\Http::response([
                    'data' => [['embedding' => array_fill(0, 1536, 0.01), 'index' => 0]],
                    'usage' => ['prompt_tokens' => 1, 'total_tokens' => 1],
                ], 200);
            }

            if (str_contains($uri, 'chat/completions')) {
                $body = "data: {\"choices\":[{\"delta\":{\"content\":\"Hi\"},\"index\":0}]}\n"
                    ."data: {\"choices\":[{\"delta\":{},\"finish_reason\":\"stop\",\"index\":0}]}\n"
                    ."data: [DONE]\n";

                return new Illuminate\Http\Client\Response(200, ['Content-Type' => 'text/event-stream'], $body);
            }

            return Illuminate\Support\Facades\Http::response([], 200);
        },
    ]);

    $response = actingAs($user, 'sanctum')->postJson('/api/chat', [
        'messages' => [['role' => 'user', 'content' => 'Hello']],
    ]);

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/x-ndjson');

    $countAfter = DB::table('agent_conversations')->where('user_id', $user->id)->count();
    expect($countAfter)->toBe($countBefore + 1);
});
