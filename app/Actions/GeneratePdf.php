<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\MediaLibrary\HasMedia;

final readonly class GeneratePdf
{
    public function handle(
        string $view,
        array $data,
        string $filename,
        ?Model $attachTo = null,
        string $collection = 'documents',
    ): string {
        $path = storage_path('app/pdf/'.$filename);

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        Pdf::view($view, $data)->save($path);

        if ($attachTo instanceof HasMedia) {
            $attachTo->addMedia($path)
                ->toMediaCollection($collection);
        }

        return $path;
    }
}
