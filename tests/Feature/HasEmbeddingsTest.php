<?php

declare(strict_types=1);

use App\Jobs\GenerateEmbeddingJob;
use App\Models\EmbeddingDemo;
use App\Models\ModelEmbedding;
use App\Models\Organization;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    if (DB::getDriverName() !== 'pgsql') {
        $this->markTestSkipped('HasEmbeddings tests require PostgreSQL.');
    }

    Bus::fake([GenerateEmbeddingJob::class]);

    $this->org = Organization::factory()->create();
    TenantContext::set($this->org);
});

it('dispatches GenerateEmbeddingJob on model create', function (): void {
    $demo = EmbeddingDemo::query()->create(['content' => 'test content']);

    Bus::assertDispatched(GenerateEmbeddingJob::class, function (GenerateEmbeddingJob $job) use ($demo): bool {
        return $job->model->is($demo);
    });
});

it('dispatches GenerateEmbeddingJob on model update', function (): void {
    $demo = EmbeddingDemo::query()->create(['content' => 'original']);
    Bus::fake([GenerateEmbeddingJob::class]);

    $demo->update(['content' => 'updated']);

    Bus::assertDispatched(GenerateEmbeddingJob::class);
});

it('returns correct content hash via contentHash()', function (): void {
    $demo = EmbeddingDemo::query()->create(['content' => 'hashable content']);

    $expected = hash('sha256', $demo->toEmbeddableText());

    expect($demo->contentHash())->toBe($expected);
});

it('detects when re-embedding is needed', function (): void {
    $demo = EmbeddingDemo::query()->create(['content' => 'original text']);

    expect($demo->needsReembedding())->toBeTrue();

    ModelEmbedding::query()->create([
        'organization_id' => $this->org->id,
        'embeddable_type' => $demo->getMorphClass(),
        'embeddable_id' => $demo->getKey(),
        'chunk_index' => 0,
        'embedding' => new Pgvector\Laravel\Vector(array_fill(0, (int) config('ai.embeddings.dimensions', 1536), 0.0)),
        'content_hash' => $demo->contentHash(),
    ]);

    expect($demo->fresh()->needsReembedding())->toBeFalse();
});

it('detects re-embedding needed when content changes', function (): void {
    $demo = EmbeddingDemo::query()->create(['content' => 'original text']);

    ModelEmbedding::query()->create([
        'organization_id' => $this->org->id,
        'embeddable_type' => $demo->getMorphClass(),
        'embeddable_id' => $demo->getKey(),
        'chunk_index' => 0,
        'embedding' => new Pgvector\Laravel\Vector(array_fill(0, (int) config('ai.embeddings.dimensions', 1536), 0.0)),
        'content_hash' => $demo->contentHash(),
    ]);

    $demo->update(['content' => 'changed text']);
    Bus::fake([GenerateEmbeddingJob::class]);

    expect($demo->fresh()->needsReembedding())->toBeTrue();
});

it('has morphOne embedding relationship', function (): void {
    $demo = EmbeddingDemo::query()->create(['content' => 'relational test']);

    expect($demo->embedding())->toBeInstanceOf(Illuminate\Database\Eloquent\Relations\MorphOne::class);
});
