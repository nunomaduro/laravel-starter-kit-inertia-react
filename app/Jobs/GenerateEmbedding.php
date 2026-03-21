<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Laravel\Ai\Embeddings;

final class GenerateEmbedding implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        private readonly Model $model,
        private readonly string $textColumn,
    ) {}

    public function handle(): void
    {
        $text = strip_tags((string) $this->model->{$this->textColumn});

        if (blank($text)) {
            return;
        }

        $response = Embeddings::for([$text])->generate();

        $this->model->update(['embedding' => $response->first()]);
    }
}
