<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\GeneratePdf;
use App\Models\User;
use App\Notifications\PdfFailedNotification;
use App\Notifications\PdfReadyNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class GeneratePdfJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 120;

    public function __construct(
        private readonly string $view,
        private readonly array $data,
        private readonly string $filename,
        private readonly int $userId,
        private readonly ?Model $attachTo = null,
        private readonly string $collection = 'documents',
    ) {}

    public function handle(GeneratePdf $generatePdf): void
    {
        $path = $generatePdf->handle(
            view: $this->view,
            data: $this->data,
            filename: $this->filename,
            attachTo: $this->attachTo,
            collection: $this->collection,
        );

        $user = User::find($this->userId);

        $user?->notify(new PdfReadyNotification(
            filename: $this->filename,
            path: $path,
        ));
    }

    public function failed(Throwable $e): void
    {
        Log::error('GeneratePdfJob failed', [
            'view' => $this->view,
            'filename' => $this->filename,
            'user_id' => $this->userId,
            'error' => $e->getMessage(),
        ]);

        $user = User::find($this->userId);

        $user?->notify(new PdfFailedNotification(
            filename: $this->filename,
            errorMessage: $e->getMessage(),
        ));
    }
}
