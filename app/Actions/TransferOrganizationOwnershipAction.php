<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Organization;
use App\Models\User;
use App\Support\AssignRoleViaDb;
use Illuminate\Support\Facades\DB;

final readonly class TransferOrganizationOwnershipAction
{
    /**
     * Transfer organization ownership to the new owner. New owner must be a member; they receive admin role.
     */
    public function handle(Organization $organization, User $newOwner): void
    {
        if (! $organization->hasMember($newOwner)) {
            return;
        }

        DB::transaction(function () use ($organization, $newOwner): void {
            $organization->update(['owner_id' => $newOwner->id]);
            AssignRoleViaDb::assignOrg($newOwner, $organization->id, 'admin');
        });
    }
}
