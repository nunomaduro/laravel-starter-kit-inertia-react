<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Actions\CreatePersonalOrganizationForUserAction;
use App\Events\User\UserCreated;
use Throwable;

final readonly class CreatePersonalOrganizationOnUserCreated
{
    public function __construct(
        private CreatePersonalOrganizationForUserAction $createPersonalOrganization
    ) {}

    public function handle(UserCreated $event): void
    {
        $invitedAsRole = session('invitation_accept_role');
        if ($invitedAsRole === 'member') {
            $create = config('tenancy.auto_create_personal_organization_for_members', false);
        } else {
            $create = config('tenancy.auto_create_personal_organization_for_admins', config('tenancy.auto_create_personal_organization', true));
        }

        if (session()->has('invitation_accept_role')) {
            session()->forget('invitation_accept_role');
        }

        if (! $create) {
            return;
        }

        try {
            $this->createPersonalOrganization->handle($event->user);
        } catch (Throwable) {
            // Fail silently so registration is never blocked
        }
    }
}
