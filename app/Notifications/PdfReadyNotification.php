<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class PdfReadyNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $filename,
        private readonly string $path,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{filename: string, path: string}
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'filename' => $this->filename,
            'path' => $this->path,
        ];
    }
}
