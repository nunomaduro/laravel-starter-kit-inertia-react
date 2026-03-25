<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Jobs\GenerateEmbeddingJob;
use App\Models\ModelEmbedding;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasEmbeddings
{
    /**
     * Return the text content that should be embedded for this model.
     */
    abstract public function toEmbeddableText(): string;

    /**
     * @return MorphOne<ModelEmbedding, $this>
     */
    public function embedding(): MorphOne
    {
        return $this->morphOne(ModelEmbedding::class, 'embeddable');
    }

    public function needsReembedding(): bool
    {
        $existing = $this->embedding;

        if ($existing === null) {
            return true;
        }

        return $existing->content_hash !== $this->contentHash();
    }

    public function contentHash(): string
    {
        return hash('sha256', $this->toEmbeddableText());
    }

    protected static function bootHasEmbeddings(): void
    {
        static::created(function ($model): void {
            GenerateEmbeddingJob::dispatch($model);
        });

        static::updated(function ($model): void {
            GenerateEmbeddingJob::dispatch($model);
        });
    }
}
