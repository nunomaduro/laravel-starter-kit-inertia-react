<?php

declare(strict_types=1);

use App\Ai\Agents\AssistantAgent;
use Eznix86\AI\Memory\Facades\AgentMemory;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('Laravel AI Memory tests require PostgreSQL with pgvector.');
    }
});

test('AssistantAgent can be instantiated with context and has memory tools and middleware', function (): void {
    $agent = new AssistantAgent(['user_id' => 1]);

    expect($agent->instructions())->not->toBeEmpty();
    expect(iterator_to_array($agent->tools()))->toHaveCount(2);
    expect($agent->middleware())->toHaveCount(1);
});

test('AgentMemory facade can store and recall with fake', function (): void {
    AgentMemory::fake();

    AgentMemory::store('User prefers dark mode', ['user_id' => 'test-123']);

    $memories = AgentMemory::recall('preferences', ['user_id' => 'test-123']);

    expect($memories)->toHaveCount(1)
        ->and($memories->first()->content)->toBe('User prefers dark mode');
});
