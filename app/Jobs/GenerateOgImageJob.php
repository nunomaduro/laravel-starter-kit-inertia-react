<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\GenerateOgImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Throwable;

final class GenerateOgImageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 60;

    /** @var array<int, int> */
    public array $backoff = [5, 15];

    /**
     * @param  Model&HasMedia  $model
     */
    public function __construct(
        private readonly Model $model,
        private readonly string $title,
        private readonly ?string $excerpt = null,
        private readonly ?string $category = null,
    ) {}

    public function handle(GenerateOgImage $action): void
    {
        $action->handle($this->model, $this->title, $this->excerpt, $this->category);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('GenerateOgImageJob failed', [
            'model' => $this->model::class,
            'key' => $this->model->getKey(),
            'title' => $this->title,
            'error' => $exception->getMessage(),
        ]);
    }
}
