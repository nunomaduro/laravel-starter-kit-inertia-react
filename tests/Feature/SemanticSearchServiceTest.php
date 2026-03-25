<?php

declare(strict_types=1);

use App\Models\EmbeddingDemo;
use App\Models\ModelEmbedding;
use App\Models\Organization;
use App\Services\SemanticSearchService;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Embeddings;
use Pgvector\Laravel\Vector;

beforeEach(function (): void {
    if (DB::getDriverName() !== 'pgsql') {
        $this->markTestSkipped('SemanticSearchService tests require PostgreSQL.');
    }

    Bus::fake();

    $this->org = Organization::factory()->create();
    TenantContext::set($this->org);
});

it('throws when forOrganization is not called', function (): void {
    SemanticSearchService::query('test')
        ->scope(EmbeddingDemo::class)
        ->get();
})->throws(InvalidArgumentException::class, 'forOrganization() is required');

it('returns empty collection when no embeddings exist', function (): void {
    Embeddings::shouldReceive('for')
        ->once()
        ->andReturnSelf();
    Embeddings::shouldReceive('generate')
        ->once()
        ->andReturn(collect([new Vector(array_fill(0, 1536, 0.1))]));

    $results = SemanticSearchService::query('test query')
        ->scope(EmbeddingDemo::class)
        ->forOrganization($this->org->id)
        ->get();

    expect($results)->toBeEmpty();
});

it('returns models matching semantic search with similarity score', function (): void {
    $demo = EmbeddingDemo::query()->create(['content' => 'machine learning basics']);

    $storedVector = array_fill(0, 1536, 0.5);
    ModelEmbedding::query()->create([
        'organization_id' => $this->org->id,
        'embeddable_type' => $demo->getMorphClass(),
        'embeddable_id' => $demo->getKey(),
        'chunk_index' => 0,
        'embedding' => new Vector($storedVector),
        'content_hash' => hash('sha256', 'machine learning basics'),
    ]);

    $queryVector = array_fill(0, 1536, 0.5);

    Embeddings::shouldReceive('for')
        ->once()
        ->andReturnSelf();
    Embeddings::shouldReceive('generate')
        ->once()
        ->andReturn(collect([new Vector($queryVector)]));

    $results = SemanticSearchService::query('what is machine learning')
        ->scope(EmbeddingDemo::class)
        ->forOrganization($this->org->id)
        ->limit(5)
        ->get();

    expect($results)->toHaveCount(1);
    expect($results->first())->toBeInstanceOf(EmbeddingDemo::class);
    expect($results->first()->similarity_score)->toBeGreaterThan(0);
});

it('filters by scope model types', function (): void {
    $demo = EmbeddingDemo::query()->create(['content' => 'scoped test']);

    ModelEmbedding::query()->create([
        'organization_id' => $this->org->id,
        'embeddable_type' => $demo->getMorphClass(),
        'embeddable_id' => $demo->getKey(),
        'chunk_index' => 0,
        'embedding' => new Vector(array_fill(0, 1536, 0.3)),
        'content_hash' => hash('sha256', 'scoped test'),
    ]);

    $queryVector = array_fill(0, 1536, 0.3);

    Embeddings::shouldReceive('for')
        ->once()
        ->andReturnSelf();
    Embeddings::shouldReceive('generate')
        ->once()
        ->andReturn(collect([new Vector($queryVector)]));

    $results = SemanticSearchService::query('scoped search')
        ->scope(EmbeddingDemo::class)
        ->forOrganization($this->org->id)
        ->get();

    expect($results)->toHaveCount(1);
});

it('respects threshold filter', function (): void {
    $demo = EmbeddingDemo::query()->create(['content' => 'threshold test']);

    ModelEmbedding::query()->create([
        'organization_id' => $this->org->id,
        'embeddable_type' => $demo->getMorphClass(),
        'embeddable_id' => $demo->getKey(),
        'chunk_index' => 0,
        'embedding' => new Vector(array_fill(0, 1536, 0.1)),
        'content_hash' => hash('sha256', 'threshold test'),
    ]);

    $queryVector = array_fill(0, 1536, -0.1);

    Embeddings::shouldReceive('for')
        ->once()
        ->andReturnSelf();
    Embeddings::shouldReceive('generate')
        ->once()
        ->andReturn(collect([new Vector($queryVector)]));

    $results = SemanticSearchService::query('unrelated query')
        ->scope(EmbeddingDemo::class)
        ->forOrganization($this->org->id)
        ->threshold(0.99)
        ->get();

    expect($results)->toBeEmpty();
});

it('respects limit', function (): void {
    for ($i = 0; $i < 5; $i++) {
        $demo = EmbeddingDemo::query()->create(['content' => "item {$i}"]);
        ModelEmbedding::query()->create([
            'organization_id' => $this->org->id,
            'embeddable_type' => $demo->getMorphClass(),
            'embeddable_id' => $demo->getKey(),
            'chunk_index' => 0,
            'embedding' => new Vector(array_fill(0, 1536, 0.5 + ($i * 0.01))),
            'content_hash' => hash('sha256', "item {$i}"),
        ]);
    }

    $queryVector = array_fill(0, 1536, 0.5);

    Embeddings::shouldReceive('for')
        ->once()
        ->andReturnSelf();
    Embeddings::shouldReceive('generate')
        ->once()
        ->andReturn(collect([new Vector($queryVector)]));

    $results = SemanticSearchService::query('items')
        ->scope(EmbeddingDemo::class)
        ->forOrganization($this->org->id)
        ->limit(3)
        ->get();

    expect($results)->toHaveCount(3);
});
