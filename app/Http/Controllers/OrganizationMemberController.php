<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RemoveOrganizationMemberAction;
use App\Models\Organization;
use App\Models\User;
use App\Services\TenantContext;
use App\Support\AssignRoleViaDb;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use function getPermissionsTeamId;
use function setPermissionsTeamId;

final readonly class OrganizationMemberController
{
    use AuthorizesRequests;

    public function index(Organization $organization): Response
    {
        $this->authorize('view', $organization);

        $members = $organization->users()
            ->withPivot(['is_default', 'joined_at', 'invited_by'])
            ->orderBy('name')
            ->get()
            ->map(function (User $user) use ($organization): array {
                $previousTeamId = getPermissionsTeamId();
                setPermissionsTeamId($organization->id);
                $role = $user->getRoleNames()->first() ?? 'member';
                setPermissionsTeamId($previousTeamId);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_owner' => $organization->isOwner($user),
                    'role' => $role,
                    'joined_at' => $user->pivot->joined_at?->toIso8601String(),
                ];
            });

        $pendingInvitations = $organization->pendingInvitations()->get();

        return Inertia::render('organizations/members', [
            'organization' => $organization,
            'members' => $members,
            'pendingInvitations' => $pendingInvitations,
        ]);
    }

    public function update(Request $request, Organization $organization, User $member): RedirectResponse
    {
        $this->authorize('update', $organization);

        $role = $request->string('role')->value();
        if (! in_array($role, Organization::ASSIGNABLE_ORG_ROLES, true)) {
            return back()->withErrors(['role' => __('Invalid role.')]);
        }

        AssignRoleViaDb::syncOrg($member, $organization->id, $role);

        return back()->with('status', __('Role updated.'));
    }

    public function destroy(Request $request, Organization $organization, User $member, RemoveOrganizationMemberAction $action): RedirectResponse
    {
        $this->authorize('update', $organization);

        $action->handle($organization, $member, $request->user());

        if ($member->id === $request->user()?->id) {
            TenantContext::forget();
            $default = $request->user()->defaultOrganization();
            if ($default instanceof Organization) {
                $request->user()->switchOrganization($default);
            }

            return to_route('organizations.index')->with('status', __('You left the organization.'));
        }

        return back()->with('status', __('Member removed.'));
    }
}
