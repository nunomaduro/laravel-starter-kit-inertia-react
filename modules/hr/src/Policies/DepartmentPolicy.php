<?php

declare(strict_types=1);

namespace Modules\Hr\Policies;

use App\Models\User;
use App\Services\TenantContext;
use Modules\Hr\Models\Department;

final class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        $orgId = TenantContext::id();

        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('org.hr.view');
    }

    public function view(User $user, Department $department): bool
    {
        return $user->canInOrganization('org.hr.view', $department->organization);
    }

    public function create(User $user): bool
    {
        $orgId = TenantContext::id();

        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('org.hr.manage');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->canInOrganization('org.hr.manage', $department->organization);
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->canInOrganization('org.hr.manage', $department->organization);
    }

    public function restore(User $user, Department $department): bool
    {
        return $user->canInOrganization('org.hr.manage', $department->organization);
    }

    public function forceDelete(User $user, Department $department): bool
    {
        return $user->canInOrganization('org.hr.manage', $department->organization);
    }
}
