<?php

declare(strict_types=1);

namespace Modules\Crm\Policies;

use App\Models\User;
use App\Services\TenantContext;
use Modules\Crm\Models\Activity;

final class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        $orgId = TenantContext::id();

        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('org.crm.view');
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->canInOrganization('org.crm.view', $activity->organization);
    }

    public function create(User $user): bool
    {
        $orgId = TenantContext::id();

        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('org.crm.manage');
    }

    public function update(User $user, Activity $activity): bool
    {
        return $user->canInOrganization('org.crm.manage', $activity->organization);
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $user->canInOrganization('org.crm.manage', $activity->organization);
    }

    public function restore(User $user, Activity $activity): bool
    {
        return $user->canInOrganization('org.crm.manage', $activity->organization);
    }

    public function forceDelete(User $user, Activity $activity): bool
    {
        return $user->canInOrganization('org.crm.manage', $activity->organization);
    }
}
