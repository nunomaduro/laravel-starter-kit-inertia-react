<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class PdfFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $filename,
        private readonly string $errorMessage,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{filename: string, error: string}
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'filename' => $this->filename,
            'error' => $this->errorMessage,
        ];
    }
}
