<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\RecordAuditLog;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\Organization\OrgCustomRoleService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

final class OrgRolesController extends Controller
{
    public function __construct(
        private readonly OrgCustomRoleService $roleService,
        private readonly RecordAuditLog $auditLog,
    ) {}

    public function index(Request $request): Response|RedirectResponse
    {
        $organization = TenantContext::get();

        if (! $organization instanceof Organization) {
            return to_route('dashboard')->with('flash', ['status' => 'error', 'message' => 'No organization selected.']);
        }

        abort_unless($request->user()?->canInOrganization('org.settings.manage', $organization), 403);

        $customRoles = $this->roleService->getCustomRoles($organization)
            ->map(fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'label' => $role->getAttribute('label') ?? $role->name,
                'permissions' => $role->permissions->pluck('name')->all(),
            ])
            ->values()
            ->all();

        return Inertia::render('settings/roles', [
            'customRoles' => $customRoles,
            'roleTemplates' => Inertia::once(fn () => $this->roleService->getRoleTemplates()),
            'grantablePermissions' => Inertia::defer(fn () => $this->roleService->getGrantablePermissionNames()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = TenantContext::get();

        if (! $organization instanceof Organization) {
            return to_route('dashboard')->with('flash', ['status' => 'error', 'message' => 'No organization selected.']);
        }

        abort_unless($request->user()?->canInOrganization('org.settings.manage', $organization), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'alpha_dash', 'max:64'],
            'label' => ['required', 'string', 'max:128'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string'],
        ]);

        $role = $this->roleService->create(
            organization: $organization,
            name: $validated['name'],
            label: $validated['label'],
            permissionNames: $validated['permissions'],
        );

        $this->auditLog->handle(
            action: 'role.created',
            subjectType: 'role',
            subjectId: $role->name,
            newValue: ['label' => $validated['label'], 'permissions' => $validated['permissions']],
            organizationId: $organization->id,
        );

        return back()->with('flash', ['status' => 'success', 'message' => 'Custom role created.']);
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $organization = TenantContext::get();

        if (! $organization instanceof Organization) {
            return to_route('dashboard')->with('flash', ['status' => 'error', 'message' => 'No organization selected.']);
        }

        abort_unless($request->user()?->canInOrganization('org.settings.manage', $organization), 403);

        $this->roleService->delete($organization, $role);

        $this->auditLog->handle(
            action: 'role.deleted',
            subjectType: 'role',
            subjectId: $role->name,
            organizationId: $organization->id,
        );

        return back()->with('flash', ['status' => 'success', 'message' => 'Custom role deleted.']);
    }
}
