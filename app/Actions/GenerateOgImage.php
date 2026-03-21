<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Spatie\Browsershot\Browsershot;
use Spatie\MediaLibrary\HasMedia;

final readonly class GenerateOgImage
{
    /**
     * Generate an OG image for a model and store it in the 'og_image' media collection.
     *
     * @param  Model&HasMedia  $model
     */
    public function handle(Model $model, string $title, ?string $excerpt = null, ?string $category = null): void
    {
        $html = view('og-image', compact('title', 'excerpt', 'category'))->render();

        $browsershot = Browsershot::html($html)
            ->windowSize(1200, 630)
            ->deviceScaleFactor(1);

        $chromePath = config('browsershot.chrome_path', '');

        if ($chromePath !== '') {
            $browsershot->setChromePath($chromePath);
        }

        $png = $browsershot->screenshot();

        $model->clearMediaCollection('og_image');
        $model
            ->addMediaFromString($png)
            ->usingFileName('og-image.png')
            ->toMediaCollection('og_image');
    }
}
