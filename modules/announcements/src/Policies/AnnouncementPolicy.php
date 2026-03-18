<?php

declare(strict_types=1);

namespace Modules\Announcements\Policies;

use App\Models\User;
use App\Services\TenantContext;
use Modules\Announcements\Enums\AnnouncementScope;
use Modules\Announcements\Models\Announcement;

final class AnnouncementPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        if ($user->can('announcements.manage_global')) {
            return true;
        }

        $orgId = TenantContext::id();
        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('announcements.manage', \App\Models\Organization::query()->find($orgId));
    }

    public function update(User $user, Announcement $announcement): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($announcement->governor_owned_by !== null && $announcement->governor_owned_by === $user->getKey()) {
            return true;
        }

        if ($user->can('announcements.manage_global')) {
            return true;
        }

        if ($announcement->scope === AnnouncementScope::Global) {
            return false;
        }

        $orgId = $announcement->organization_id;
        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('announcements.manage', $announcement->organization);
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $this->update($user, $announcement);
    }

    public function restore(User $user, Announcement $announcement): bool
    {
        return $this->update($user, $announcement);
    }

    public function forceDelete(User $user, Announcement $announcement): bool
    {
        return $this->update($user, $announcement);
    }
}
