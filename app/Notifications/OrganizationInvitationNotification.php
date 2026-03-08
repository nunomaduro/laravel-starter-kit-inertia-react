<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\OrganizationInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class OrganizationInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly OrganizationInvitation $invitation) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{title: string, message: string, type: string, action_url: string}
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => "You've been invited to ".$this->invitation->organization->name,
            'message' => 'Accept your invitation to join the team.',
            'type' => 'info',
            'action_url' => route('invitations.show', $this->invitation->token),
        ];
    }
}
