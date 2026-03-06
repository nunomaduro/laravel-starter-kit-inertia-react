<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SwitchOrganizationAction;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final readonly class OrganizationSwitchController
{
    public function __invoke(Request $request, SwitchOrganizationAction $action): RedirectResponse
    {
        $organizationId = $request->input('organization_id');
        if (! $organizationId) {
            return back()->withErrors(['organization_id' => __('Please select an organization.')]);
        }

        $organization = Organization::query()->find($organizationId);
        if (! $organization instanceof Organization) {
            return back()->withErrors(['organization_id' => __('Invalid organization.')]);
        }

        $user = $request->user();
        if (! $action->handle($user, $organization)) {
            return back()->withErrors(['organization_id' => __('You do not have access to that organization.')]);
        }

        $message = __('Switched to :name.', ['name' => $organization->name]);

        return back()
            ->with('status', $message)
            ->with('filament_org_switch_message', $message);
    }
}
