<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class OrganizationMemberAddedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Organization $organization,
        private readonly User $newMember,
    ) {}

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
            'title' => sprintf('%s joined %s', $this->newMember->name, $this->organization->name),
            'message' => 'A new member has joined your organization.',
            'type' => 'info',
            'action_url' => null,
        ];
    }
}
