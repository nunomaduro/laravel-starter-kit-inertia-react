<?php

declare(strict_types=1);

namespace Modules\Changelog\Policies;

use App\Models\User;
use App\Services\TenantContext;
use Modules\Changelog\Models\ChangelogEntry;

final class ChangelogEntryPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(?User $user, ChangelogEntry $entry): bool
    {
        if ($entry->is_published) {
            return true;
        }

        return $user !== null && $user->canInOrganization('org.changelog.manage', $entry->organization);
    }

    public function create(User $user): bool
    {
        $orgId = TenantContext::id();

        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('org.changelog.manage');
    }

    public function update(User $user, ChangelogEntry $entry): bool
    {
        return $user->canInOrganization('org.changelog.manage', $entry->organization);
    }

    public function delete(User $user, ChangelogEntry $entry): bool
    {
        return $user->canInOrganization('org.changelog.manage', $entry->organization);
    }

    public function restore(User $user, ChangelogEntry $entry): bool
    {
        return $user->canInOrganization('org.changelog.manage', $entry->organization);
    }

    public function forceDelete(User $user, ChangelogEntry $entry): bool
    {
        return $user->canInOrganization('org.changelog.manage', $entry->organization);
    }
}
