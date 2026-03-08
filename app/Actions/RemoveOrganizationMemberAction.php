<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\OrganizationMemberRemoved;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use function getPermissionsTeamId;
use function setPermissionsTeamId;

final readonly class RemoveOrganizationMemberAction
{
    public function __construct(private RecordAuditLog $auditLog) {}

    /**
     * Remove a member from the organization. If the member is the owner, transfer ownership to the first admin or delete the organization if empty.
     */
    public function handle(Organization $organization, User $member, ?User $removedBy = null): void
    {
        $previousTeamId = getPermissionsTeamId();
        setPermissionsTeamId($organization->id);
        $previousRole = $member->getRoleNames()->first();
        setPermissionsTeamId($previousTeamId);

        DB::transaction(function () use ($organization, $member, $previousRole, $removedBy): void {
            $wasOwner = $organization->isOwner($member);

            $organization->removeMember($member);

            $this->auditLog->handle(
                action: 'member.removed',
                subjectType: 'user',
                subjectId: (string) $member->id,
                oldValue: ['role' => $previousRole, 'name' => $member->name],
                organizationId: $organization->id,
                actorId: $removedBy?->id,
            );

            event(new OrganizationMemberRemoved($organization, $member, $previousRole, $removedBy));

            if ($wasOwner) {
                $newOwner = $organization->members()->first();
                if ($newOwner instanceof User) {
                    $organization->update(['owner_id' => $newOwner->id]);
                } else {
                    $organization->delete();
                }
            }
        });
    }
}
