<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\InviteToOrganizationAction;
use App\Events\OrganizationInvitationSent;
use App\Http\Requests\StoreInvitationRequest;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class OrganizationInvitationController
{
    use AuthorizesRequests;

    public function index(Organization $organization): Response
    {
        $this->authorize('view', $organization);

        $invitations = $organization->invitations()
            ->with(['inviter:id,name'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return Inertia::render('invitations/index', [
            'organization' => $organization->only('id', 'name', 'slug'),
            'invitations' => $invitations,
            'assignableRoles' => Organization::ASSIGNABLE_ORG_ROLES,
        ]);
    }

    public function create(Organization $organization): Response
    {
        $this->authorize('addMember', $organization);

        return Inertia::render('invitations/create', [
            'organization' => $organization->only('id', 'name', 'slug'),
            'assignableRoles' => Organization::ASSIGNABLE_ORG_ROLES,
        ]);
    }

    public function store(StoreInvitationRequest $request, Organization $organization, InviteToOrganizationAction $action): RedirectResponse
    {
        $invitation = $action->handle(
            $organization,
            $request->string('email')->value(),
            $request->string('role')->value(),
            $request->user()
        );

        return back()->with('status', __('Invitation sent to :email.', ['email' => $invitation->email]));
    }

    public function destroy(Organization $organization, OrganizationInvitation $invitation): RedirectResponse
    {
        $this->authorize('delete', $invitation);

        abort_if($invitation->organization_id !== $organization->id, 404);

        $invitation->markAsCancelled();

        return back()->with('status', __('Invitation cancelled.'));
    }

    public function update(Organization $organization, OrganizationInvitation $invitation): RedirectResponse
    {
        $this->authorize('update', $invitation);

        abort_if($invitation->organization_id !== $organization->id, 404);

        if (! $invitation->canBeResent()) {
            return back()->withErrors(['invitation' => __('Invitation cannot be resent.')]);
        }

        $invitation->resend();
        $invitation->load('inviter');
        event(new OrganizationInvitationSent(
            $invitation,
            $invitation->organization,
            $invitation->email,
            $invitation->role,
            $invitation->inviter
        ));

        return back()->with('status', __('Invitation resent.'));
    }
}
