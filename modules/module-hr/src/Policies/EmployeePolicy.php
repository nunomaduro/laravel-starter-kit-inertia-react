<?php

declare(strict_types=1);

namespace Cogneiss\ModuleHr\Policies;

use App\Models\User;
use App\Services\TenantContext;
use Cogneiss\ModuleHr\Models\Employee;

final class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        $orgId = TenantContext::id();

        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('org.hr.view');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->canInOrganization('org.hr.view', $employee->organization);
    }

    public function create(User $user): bool
    {
        $orgId = TenantContext::id();

        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('org.hr.manage');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->canInOrganization('org.hr.manage', $employee->organization);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->canInOrganization('org.hr.manage', $employee->organization);
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $user->canInOrganization('org.hr.manage', $employee->organization);
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return $user->canInOrganization('org.hr.manage', $employee->organization);
    }
}
