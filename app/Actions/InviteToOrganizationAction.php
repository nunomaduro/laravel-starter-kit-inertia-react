<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\OrganizationInvitationSent;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use InvalidArgumentException;
use Spatie\Permission\Models\Role;

final readonly class InviteToOrganizationAction
{
    public function __construct(private RecordAuditLog $auditLog) {}

    /**
     * Create an invitation and send the notification email.
     *
     * Accepted roles: any value from ASSIGNABLE_ORG_ROLES or a custom role
     * prefixed with 'custom_{org_id}_' belonging to the organization.
     *
     * @throws InvalidArgumentException If role is not valid for the organization
     */
    public function handle(Organization $organization, string $email, string $role, User $invitedBy): OrganizationInvitation
    {
        if (! $this->isValidRole($organization, $role)) {
            $valid = implode(', ', Organization::ASSIGNABLE_ORG_ROLES);
            throw new InvalidArgumentException(
                sprintf("Invalid role '%s'. Must be one of: %s — or a custom role for this organization.", $role, $valid)
            );
        }

        $invitation = OrganizationInvitation::query()->create([
            'organization_id' => $organization->id,
            'email' => $email,
            'role' => $role,
            'invited_by' => $invitedBy->id,
        ]);

        $this->auditLog->handle(
            action: 'member.invited',
            subjectType: 'user',
            subjectId: $email,
            newValue: ['role' => $role, 'invited_by' => $invitedBy->id],
            organizationId: $organization->id,
            actorId: $invitedBy->id,
        );

        event(new OrganizationInvitationSent($invitation, $organization, $email, $role, $invitedBy));

        return $invitation;
    }

    private function isValidRole(Organization $organization, string $role): bool
    {
        if (in_array($role, Organization::ASSIGNABLE_ORG_ROLES, true)) {
            return true;
        }

        // Allow custom roles that belong to this organization
        $teamKey = config('permission.column_names.team_foreign_key');

        return Role::query()
            ->where('name', $role)
            ->where('guard_name', 'web')
            ->where($teamKey, $organization->id)
            ->where('name', 'like', 'custom_%')
            ->exists();
    }
}
