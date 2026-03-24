<?php

declare(strict_types=1);

namespace Modules\Hr\Policies;

use App\Models\User;
use App\Services\TenantContext;
use Modules\Hr\Models\LeaveRequest;

final class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        $orgId = TenantContext::id();

        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('org.hr.view');
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($this->isOwnLeaveRequest($user, $leaveRequest)) {
            return true;
        }

        return $user->canInOrganization('org.hr.manage', $leaveRequest->organization);
    }

    public function create(User $user): bool
    {
        $orgId = TenantContext::id();

        if ($orgId === null) {
            return false;
        }

        return $user->belongsToOrganization($orgId);
    }

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($this->isOwnLeaveRequest($user, $leaveRequest) && $leaveRequest->status === 'pending') {
            return true;
        }

        return $user->canInOrganization('org.hr.manage', $leaveRequest->organization);
    }

    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($this->isOwnLeaveRequest($user, $leaveRequest) && $leaveRequest->status === 'pending') {
            return true;
        }

        return $user->canInOrganization('org.hr.manage', $leaveRequest->organization);
    }

    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->canInOrganization('org.hr.manage', $leaveRequest->organization);
    }

    public function restore(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->canInOrganization('org.hr.manage', $leaveRequest->organization);
    }

    public function forceDelete(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->canInOrganization('org.hr.manage', $leaveRequest->organization);
    }

    private function isOwnLeaveRequest(User $user, LeaveRequest $leaveRequest): bool
    {
        $employee = $leaveRequest->employee;

        return $employee !== null && $employee->user_id === $user->id;
    }
}
