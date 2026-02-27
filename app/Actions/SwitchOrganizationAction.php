<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Organization;
use App\Models\User;

final readonly class SwitchOrganizationAction
{
    public function handle(User $user, Organization|int $organization): bool
    {
        return $user->switchOrganization($organization);
    }
}
