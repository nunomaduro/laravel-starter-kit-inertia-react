<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class OrganizationMemberRemovedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Organization $organization) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{title: string, message: string, type: string, action_url: null}
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Removed from '.$this->organization->name,
            'message' => sprintf('You have been removed from %s.', $this->organization->name),
            'type' => 'warning',
            'action_url' => null,
        ];
    }
}
