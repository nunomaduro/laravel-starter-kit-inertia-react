<?php

declare(strict_types=1);

use App\Models\ModelEmbedding;
use App\Models\Organization;
use App\Services\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Laravel\Ai\Embeddings;
use Modules\BotStudio\Jobs\ProcessKnowledgeFileJob;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Models\AgentKnowledgeFile;
use Modules\BotStudio\Services\DocumentChunker;
use Modules\BotStudio\Services\KnowledgeProcessor;
use Pgvector\Laravel\Vector;

/*
|--------------------------------------------------------------------------
| Knowledge Pipeline Tests
|--------------------------------------------------------------------------
*/

/**
 * Helper to set up a test agent with org and user context.
 *
 * @return array{org: Organization, user: App\Models\User, agent: AgentDefinition}
 */
function setupKnowledgeTest(object $test): array
{
    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $test->actingAs($user);

    $agent = AgentDefinition::query()->withoutGlobalScopes()->create([
        'organization_id' => $org->id,
        'created_by' => $user->id,
        'name' => 'Test Agent',
        'slug' => 'test-agent-'.uniqid(),
        'system_prompt' => 'You are helpful.',
        'model' => 'gpt-4o-mini',
        'temperature' => 0.7,
        'max_tokens' => 4096,
        'enabled_tools' => [],
        'knowledge_config' => [],
        'conversation_starters' => [],
    ]);

    return ['org' => $org, 'user' => $user, 'agent' => $agent];
}

// ── KnowledgeProcessor: File Type Validation ──────────────────────────────

it('accepts valid file types via KnowledgeProcessor', function (string $extension, string $mimeType): void {
    Queue::fake();
    ['agent' => $agent] = setupKnowledgeTest($this);

    $file = UploadedFile::fake()->create("test.{$extension}", 100, $mimeType);

    $processor = app(KnowledgeProcessor::class);
    $knowledgeFile = $processor->upload($agent, $file);

    expect($knowledgeFile)->toBeInstanceOf(AgentKnowledgeFile::class)
        ->and($knowledgeFile->status)->toBe('pending')
        ->and($knowledgeFile->filename)->toBe("test.{$extension}");

    Queue::assertPushed(ProcessKnowledgeFileJob::class);
})->with([
    'pdf' => ['pdf', 'application/pdf'],
    'txt' => ['txt', 'text/plain'],
    'csv' => ['csv', 'text/csv'],
]);

it('rejects invalid file types via KnowledgeProcessor', function (): void {
    Queue::fake();
    ['agent' => $agent] = setupKnowledgeTest($this);

    $file = UploadedFile::fake()->create('test.exe', 100, 'application/x-msdownload');

    $processor = app(KnowledgeProcessor::class);
    $processor->upload($agent, $file);
})->throws(ValidationException::class);

// ── KnowledgeProcessor: File Size Validation ──────────────────────────────

it('rejects files exceeding max size', function (): void {
    Queue::fake();
    ['agent' => $agent] = setupKnowledgeTest($this);

    $file = UploadedFile::fake()->create('large.txt', 11000, 'text/plain');

    $processor = app(KnowledgeProcessor::class);
    $processor->upload($agent, $file);
})->throws(ValidationException::class);

// ── ProcessKnowledgeFileJob: TXT extraction ──────────────────────────────

it('extracts text from TXT and creates embeddings', function (): void {
    if (DB::getDriverName() !== 'pgsql') {
        $this->markTestSkipped('Embedding tests require PostgreSQL with pgvector.');
    }

    ['org' => $org, 'agent' => $agent] = setupKnowledgeTest($this);

    $content = 'This is test content for the knowledge pipeline. It should be chunked and embedded properly.';

    $knowledgeFile = AgentKnowledgeFile::query()->create([
        'agent_definition_id' => $agent->id,
        'organization_id' => $org->id,
        'filename' => 'test.txt',
        'mime_type' => 'text/plain',
        'file_size' => mb_strlen($content),
        'status' => 'pending',
        'chunk_count' => 0,
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'knowledge_');
    file_put_contents($tempFile, $content);
    $knowledgeFile->addMedia($tempFile)->toMediaCollection('knowledge');

    Embeddings::shouldReceive('for')
        ->andReturnSelf();
    Embeddings::shouldReceive('generate')
        ->andReturn(collect([new Vector(array_fill(0, 1536, 0.1))]));

    (new ProcessKnowledgeFileJob($knowledgeFile->refresh()))->handle(
        new DocumentChunker(),
    );

    $knowledgeFile->refresh();

    expect($knowledgeFile->status)->toBe('indexed')
        ->and($knowledgeFile->chunk_count)->toBeGreaterThan(0)
        ->and($knowledgeFile->processed_at)->not->toBeNull();

    $embeddingCount = ModelEmbedding::query()
        ->where('embeddable_type', AgentKnowledgeFile::class)
        ->where('embeddable_id', $knowledgeFile->id)
        ->count();

    expect($embeddingCount)->toBe($knowledgeFile->chunk_count);
});

// ── ProcessKnowledgeFileJob: chunks create model_embeddings rows ──────────

it('creates model_embedding rows with metadata content', function (): void {
    if (DB::getDriverName() !== 'pgsql') {
        $this->markTestSkipped('Embedding tests require PostgreSQL with pgvector.');
    }

    ['org' => $org, 'agent' => $agent] = setupKnowledgeTest($this);

    $content = 'Knowledge chunk content for citation purposes.';

    $knowledgeFile = AgentKnowledgeFile::query()->create([
        'agent_definition_id' => $agent->id,
        'organization_id' => $org->id,
        'filename' => 'citation.txt',
        'mime_type' => 'text/plain',
        'file_size' => mb_strlen($content),
        'status' => 'pending',
        'chunk_count' => 0,
    ]);

    $tempFile = tempnam(sys_get_temp_dir(), 'knowledge_');
    file_put_contents($tempFile, $content);
    $knowledgeFile->addMedia($tempFile)->toMediaCollection('knowledge');

    Embeddings::shouldReceive('for')->andReturnSelf();
    Embeddings::shouldReceive('generate')
        ->andReturn(collect([new Vector(array_fill(0, 1536, 0.1))]));

    (new ProcessKnowledgeFileJob($knowledgeFile->refresh()))->handle(
        new DocumentChunker(),
    );

    $embedding = ModelEmbedding::query()
        ->where('embeddable_type', AgentKnowledgeFile::class)
        ->where('embeddable_id', $knowledgeFile->id)
        ->first();

    expect($embedding)->not->toBeNull()
        ->and($embedding->metadata)->toBeArray()
        ->and($embedding->metadata['content'])->toContain('Knowledge chunk content');
});

// ── ProcessKnowledgeFileJob: sets status to 'failed' on error ─────────────

it('sets status to failed when job encounters an error', function (): void {
    ['agent' => $agent, 'org' => $org] = setupKnowledgeTest($this);

    $knowledgeFile = AgentKnowledgeFile::query()->create([
        'agent_definition_id' => $agent->id,
        'organization_id' => $org->id,
        'filename' => 'broken.txt',
        'mime_type' => 'text/plain',
        'file_size' => 100,
        'status' => 'pending',
        'chunk_count' => 0,
    ]);

    $job = new ProcessKnowledgeFileJob($knowledgeFile);

    try {
        $job->handle(new DocumentChunker());
    } catch (RuntimeException) {
        // Expected - no media attached
    }

    $job->failed(new RuntimeException('No media file attached'));

    $knowledgeFile->refresh();

    expect($knowledgeFile->status)->toBe('failed')
        ->and($knowledgeFile->error_message)->toContain('No media file');
});

// ── Deleting knowledge file deletes embeddings ───────────────────────────

it('deletes embeddings when knowledge file is deleted', function (): void {
    if (DB::getDriverName() !== 'pgsql') {
        $this->markTestSkipped('Embedding tests require PostgreSQL with pgvector.');
    }

    ['org' => $org, 'agent' => $agent] = setupKnowledgeTest($this);

    $knowledgeFile = AgentKnowledgeFile::query()->create([
        'agent_definition_id' => $agent->id,
        'organization_id' => $org->id,
        'filename' => 'delete-me.txt',
        'mime_type' => 'text/plain',
        'file_size' => 100,
        'status' => 'indexed',
        'chunk_count' => 2,
        'processed_at' => now(),
    ]);

    ModelEmbedding::query()->insert([
        [
            'organization_id' => $org->id,
            'embeddable_type' => AgentKnowledgeFile::class,
            'embeddable_id' => $knowledgeFile->id,
            'chunk_index' => 0,
            'embedding' => (string) new Vector(array_fill(0, 1536, 0.1)),
            'content_hash' => md5('chunk0'),
            'metadata' => json_encode(['content' => 'chunk 0']),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'organization_id' => $org->id,
            'embeddable_type' => AgentKnowledgeFile::class,
            'embeddable_id' => $knowledgeFile->id,
            'chunk_index' => 1,
            'embedding' => (string) new Vector(array_fill(0, 1536, 0.1)),
            'content_hash' => md5('chunk1'),
            'metadata' => json_encode(['content' => 'chunk 1']),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    expect(ModelEmbedding::query()
        ->where('embeddable_type', AgentKnowledgeFile::class)
        ->where('embeddable_id', $knowledgeFile->id)
        ->count())->toBe(2);

    $knowledgeFile->delete();

    expect(ModelEmbedding::query()
        ->where('embeddable_type', AgentKnowledgeFile::class)
        ->where('embeddable_id', $knowledgeFile->id)
        ->count())->toBe(0);
});

// ── Retry resets status and dispatches new job ───────────────────────────

it('retries a failed knowledge file via controller', function (): void {
    Queue::fake();
    ['org' => $org, 'agent' => $agent] = setupKnowledgeTest($this);

    $knowledgeFile = AgentKnowledgeFile::query()->create([
        'agent_definition_id' => $agent->id,
        'organization_id' => $org->id,
        'filename' => 'retry-me.txt',
        'mime_type' => 'text/plain',
        'file_size' => 100,
        'status' => 'failed',
        'error_message' => 'Previous error',
        'chunk_count' => 0,
    ]);

    $response = $this->postJson(
        "/bot-studio/{$agent->slug}/knowledge/{$knowledgeFile->id}/retry",
    );

    $response->assertOk()
        ->assertJsonPath('knowledge_file.status', 'pending');

    $knowledgeFile->refresh();

    expect($knowledgeFile->status)->toBe('pending')
        ->and($knowledgeFile->error_message)->toBeNull();

    Queue::assertPushed(ProcessKnowledgeFileJob::class);
});

// ── Controller: store uploads file ──────────────────────────────────────

it('uploads a knowledge file via controller', function (): void {
    Queue::fake();
    ['agent' => $agent] = setupKnowledgeTest($this);

    $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

    $response = $this->postJson(
        "/bot-studio/{$agent->slug}/knowledge",
        ['file' => $file],
    );

    $response->assertCreated()
        ->assertJsonStructure(['knowledge_file' => ['id', 'filename', 'status']]);

    Queue::assertPushed(ProcessKnowledgeFileJob::class);
});

// ── Controller: destroy deletes file ────────────────────────────────────

it('deletes a knowledge file via controller', function (): void {
    ['org' => $org, 'agent' => $agent] = setupKnowledgeTest($this);

    $knowledgeFile = AgentKnowledgeFile::query()->create([
        'agent_definition_id' => $agent->id,
        'organization_id' => $org->id,
        'filename' => 'delete-via-controller.txt',
        'mime_type' => 'text/plain',
        'file_size' => 100,
        'status' => 'indexed',
        'chunk_count' => 5,
        'processed_at' => now(),
    ]);

    $response = $this->deleteJson(
        "/bot-studio/{$agent->slug}/knowledge/{$knowledgeFile->id}",
    );

    $response->assertNoContent();

    expect(AgentKnowledgeFile::query()->find($knowledgeFile->id))->toBeNull();
});
