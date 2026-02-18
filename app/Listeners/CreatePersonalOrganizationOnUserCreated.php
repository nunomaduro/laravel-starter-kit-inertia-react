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
        if (! config('tenancy.auto_create_personal_organization', true)) {
            return;
        }

        try {
            $this->createPersonalOrganization->handle($event->user);
        } catch (Throwable) {
            // Fail silently so registration is never blocked
        }
    }
}
