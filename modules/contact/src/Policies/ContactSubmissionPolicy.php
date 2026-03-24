<?php

declare(strict_types=1);

namespace Modules\Contact\Policies;

use App\Models\User;
use App\Services\TenantContext;
use Modules\Contact\Models\ContactSubmission;

final class ContactSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        $orgId = TenantContext::id();

        if ($orgId === null) {
            return false;
        }

        return $user->canInOrganization('org.contact.manage');
    }

    public function view(User $user, ContactSubmission $submission): bool
    {
        return $user->canInOrganization('org.contact.manage', $submission->organization);
    }

    public function create(): bool
    {
        return true;
    }

    public function update(User $user, ContactSubmission $submission): bool
    {
        return $user->canInOrganization('org.contact.manage', $submission->organization);
    }

    public function delete(User $user, ContactSubmission $submission): bool
    {
        return $user->canInOrganization('org.contact.manage', $submission->organization);
    }

    public function restore(User $user, ContactSubmission $submission): bool
    {
        return $user->canInOrganization('org.contact.manage', $submission->organization);
    }

    public function forceDelete(User $user, ContactSubmission $submission): bool
    {
        return $user->canInOrganization('org.contact.manage', $submission->organization);
    }
}
