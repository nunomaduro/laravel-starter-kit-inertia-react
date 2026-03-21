<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\GenerateOgImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\MediaLibrary\HasMedia;

final class GenerateOgImageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

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
}
