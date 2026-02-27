<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AcceptOrganizationInvitationAction;
use App\Models\OrganizationInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final readonly class InvitationAcceptController
{
    /**
     * Show the accept invitation page (public - token in URL).
     */
    public function show(string $token): Response|RedirectResponse
    {
        $invitation = OrganizationInvitation::findByToken($token);

        if (! $invitation instanceof OrganizationInvitation) {
            return to_route('home')->withErrors(['invitation' => __('Invalid or expired invitation.')]);
        }

        if (! $invitation->isValid()) {
            return to_route('home')->withErrors(['invitation' => __('This invitation has expired or was cancelled.')]);
        }

        $invitation->load(['organization', 'inviter']);

        return Inertia::render('invitations/accept', [
            'invitation' => [
                'token' => $invitation->token,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'organization' => $invitation->organization->only(['id', 'name', 'slug']),
                'inviter' => $invitation->inviter->only(['name']),
                'expires_at' => $invitation->expires_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Process acceptance (authenticated user or after registration).
     */
    public function store(Request $request, string $token, AcceptOrganizationInvitationAction $action): RedirectResponse
    {
        $invitation = OrganizationInvitation::findValidByToken($token);
        if (! $invitation instanceof OrganizationInvitation) {
            return to_route('home')->withErrors(['invitation' => __('Invalid or expired invitation.')]);
        }

        $user = $request->user();
        if (! $user) {
            return to_route('login')->with('url.intended', route('invitations.accept', ['token' => $token]));
        }

        if (mb_strtolower((string) $user->email) !== mb_strtolower($invitation->email)) {
            return back()->withErrors(['email' => __('This invitation was sent to :email.', ['email' => $invitation->email])]);
        }

        $action->handle($invitation, $user);

        $user->switchOrganization($invitation->organization);

        return to_route('organizations.show', $invitation->organization)
            ->with('status', __('You have joined :organization.', ['organization' => $invitation->organization->name]));
    }
}
