<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\OrganizationInvitationAccepted;
use App\Events\OrganizationMemberAdded;
use App\Models\OrganizationInvitation;
use App\Models\User;

final readonly class AcceptOrganizationInvitationAction
{
    public function handle(OrganizationInvitation $invitation, User $user): OrganizationInvitation
    {
        $invitation->acceptForUser($user);

        event(new OrganizationInvitationAccepted($invitation, $invitation->organization, $user, $invitation->role));
        event(new OrganizationMemberAdded($invitation->organization, $user, $invitation->role, $invitation->inviter));

        return $invitation;
    }
}
