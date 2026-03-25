<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ModelEmbedding;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Embeddings;
use Spatie\RateLimitedMiddleware\RateLimited;
use Throwable;

final class GenerateEmbeddingJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 120;

    public function __construct(public readonly Model $model) {}

    public function uniqueId(): string
    {
        /** @var string $morphClass */
        $morphClass = $this->model->getMorphClass();

        /** @var string|int $key */
        $key = $this->model->getKey();

        return $morphClass.':'.$key;
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new RateLimited)
                ->allow(60)
                ->everySeconds(60)
                ->releaseAfterSeconds(10),
        ];
    }

    public function handle(): void
    {
        $model = $this->model;

        $text = strip_tags((string) $model->toEmbeddableText()); // @phpstan-ignore method.notFound, cast.string

        if (blank($text)) {
            return;
        }

        if (! $model->needsReembedding()) { // @phpstan-ignore method.notFound
            return;
        }

        $response = Embeddings::for([$text])->generate();
        $vector = $response->first();

        /** @var int|null $organizationId */
        $organizationId = $model->getAttribute('organization_id');

        ModelEmbedding::query()->updateOrCreate(
            [
                'embeddable_type' => $model->getMorphClass(),
                'embeddable_id' => $model->getKey(),
                'chunk_index' => 0,
            ],
            [
                'organization_id' => $organizationId,
                'embedding' => $vector,
                'content_hash' => $model->contentHash(), // @phpstan-ignore method.notFound
            ],
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::error('GenerateEmbeddingJob failed', [
            'model' => $this->model::class,
            'key' => $this->model->getKey(),
            'error' => $exception->getMessage(),
        ]);
    }
}
