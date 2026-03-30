<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\BatchUpdateUsersAction;
use App\Actions\BulkSoftDeleteUsers;
use App\Actions\DuplicateUser;
use App\DataTables\UserDataTable;
use App\Http\Requests\BatchUpdateUsersRequest;
use App\Http\Requests\BulkSoftDeleteUsersRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateManagedUserRequest;
use App\Models\User;
use App\Services\ActivityLogRbac;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Tags\Tag;

final class UsersTableController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeViewUsers($request);

        $props = UserDataTable::inertiaProps($request);
        $props['dataTableAi'] = $this->dataTableAiProps();
        $props['batchEditAllowedColumns'] = BatchUpdateUsersAction::ALLOWED_COLUMNS;

        return Inertia::render('users/table', $props);
    }

    public function bulkSoftDelete(BulkSoftDeleteUsersRequest $request, BulkSoftDeleteUsers $action): RedirectResponse
    {
        $count = $action->handle(array_map(intval(...), $request->validated('ids')), $request->user());

        return back()->with('flash', ['type' => 'success', 'message' => $count.' user(s) soft-deleted.']);
    }

    public function batchUpdate(BatchUpdateUsersRequest $request, BatchUpdateUsersAction $action): RedirectResponse
    {
        $count = $action->handle(
            array_map(intval(...), $request->validated('ids')),
            $request->validated('column'),
            $request->validated('value'),
        );

        return back()->with('flash', ['type' => 'success', 'message' => $count.' user(s) updated.']);
    }

    public function duplicate(User $user, DuplicateUser $action, Request $request): RedirectResponse
    {
        $this->ensureCanViewUser($user, $request);
        $copy = $action->handle($user);

        return to_route('users.table')->with('flash', ['type' => 'success', 'message' => 'User duplicated as '.$copy->name.'.']);
    }

    public function show(User $user, Request $request): Response
    {
        $this->ensureCanViewUser($user, $request);

        return Inertia::render('users/show', UserDataTable::showProps($user));
    }

    public function create(Request $request): Response
    {
        $this->authorizeManageUsers($request);

        return Inertia::render('users/create', [
            'roles' => Role::query()->orderBy('name')->get(['id', 'name'])->toArray(),
            'tagSuggestions' => Tag::query()->pluck('name')->unique()->values()->all(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        if (! empty($data['roles'])) {
            $user->syncRoles(Role::query()->whereIn('id', $data['roles'])->get());
            $user->load('roles');
            resolve(ActivityLogRbac::class)->logRolesAssigned(
                $user,
                ActivityLogRbac::roleNamesFrom($user),
            );
        }

        $tagNames = array_filter($data['tag_names'] ?? [], fn ($v): bool => is_string($v) && $v !== '');
        if ($tagNames !== []) {
            $user->syncTags($tagNames);
        }

        return to_route('users.show', $user)->with('status', __('User created.'));
    }

    public function edit(User $user, Request $request): Response
    {
        $this->authorizeManageUsers($request);

        return Inertia::render('users/edit', [
            'user' => [
                'id' => $user->id,
                'hash_id' => $user->hash_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role_ids' => $user->roles->pluck('id')->all(),
                'tag_names' => $user->tags->pluck('name')->values()->all(),
            ],
            'roles' => Role::query()->orderBy('name')->get(['id', 'name'])->toArray(),
            'tagSuggestions' => Tag::query()->pluck('name')->unique()->values()->all(),
        ]);
    }

    public function update(User $user, UpdateManagedUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $fields = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ];

        if (filled($data['password'] ?? null)) {
            $fields['password'] = Hash::make($data['password']);
        }

        $user->update($fields);

        if (array_key_exists('roles', $data)) {
            $previousRoleNames = ActivityLogRbac::roleNamesFrom($user);
            $newRoleIds = $data['roles'] ?? [];
            $superAdminRole = Role::query()->where('name', 'super-admin')->first();

            if ($user->isLastSuperAdmin() && $superAdminRole && ! in_array($superAdminRole->getKey(), $newRoleIds, true)) {
                abort(403, 'Cannot remove the super-admin role from the last super-admin user.');
            }

            $user->syncRoles(Role::query()->whereIn('id', $newRoleIds)->get());
            $user->load('roles');
            resolve(ActivityLogRbac::class)->logRolesUpdated(
                $user,
                $previousRoleNames,
                ActivityLogRbac::roleNamesFrom($user),
            );
        }

        $tagNames = array_filter($data['tag_names'] ?? [], fn ($v): bool => is_string($v) && $v !== '');
        $user->syncTags($tagNames);

        return to_route('users.show', $user)->with('status', __('User updated.'));
    }

    public function destroy(User $user, Request $request): RedirectResponse
    {
        $this->ensureCanViewUser($user, $request);
        abort_if($user->id === $request->user()?->id, 403, 'Cannot delete yourself.');
        abort_if($user->isLastSuperAdmin(), 403, 'Cannot delete the last super-admin.');

        $user->delete();

        return to_route('users.table')->with('status', __('User deleted.'));
    }

    public function restore(string $id, Request $request): RedirectResponse
    {
        $this->authorizeViewUsers($request);
        $user = User::withTrashed()->findOrFail((int) $id);
        abort_if(! $user->trashed(), 404);
        abort_if($user->id === $request->user()?->id, 403, 'Cannot restore yourself.');
        $user->restore();

        return back()->with('flash', ['type' => 'success', 'message' => "User {$user->name} restored."]);
    }

    public function forceDelete(string $id, Request $request): RedirectResponse
    {
        $this->authorizeViewUsers($request);
        $user = User::withTrashed()->findOrFail((int) $id);
        abort_if($user->id === $request->user()?->id, 403, 'Cannot delete yourself.');
        $user->forceDelete();

        return back()->with('flash', ['type' => 'success', 'message' => 'User permanently deleted.']);
    }

    /**
     * Opt-in AI props: only expose AI panel / Thesys when configured.
     * When no AI backend or no Thesys key, those features are disabled.
     *
     * @return array{aiBaseUrl: string|null, thesysEnabled: bool}
     */
    private function dataTableAiProps(): array
    {
        $aiBackend = interface_exists(\Laravel\Ai\Contracts\Agent::class)
            || class_exists(\Laravel\Ai\AiManager::class)
            || class_exists(\PrismPHP\Prism::class)
            || class_exists(\EchoLabs\Prism\Prism::class);
        $thesysKey = (bool) config('services.thesys.api_key');

        return [
            'aiBaseUrl' => $aiBackend ? url('/data-table/ai/users') : null,
            'thesysEnabled' => $thesysKey,
        ];
    }

    private function authorizeViewUsers(Request $request): void
    {
        $u = $request->user();
        abort_unless(
            $u?->isSuperAdmin()
            || $u?->can('bypass-permissions')
            || (config('tenancy.enabled', true) && $u?->canInOrganization('org.members.view')),
            403,
        );
    }

    private function authorizeManageUsers(Request $request): void
    {
        $u = $request->user();
        abort_unless(
            $u?->isSuperAdmin()
            || $u?->can('bypass-permissions')
            || (config('tenancy.enabled', true) && $u?->canInOrganization('org.members.manage')),
            403,
        );
    }

    private function ensureCanViewUser(User $user, Request $request): void
    {
        $this->authorizeViewUsers($request);
        $org = TenantContext::get();
        $canBypass = $request->user()?->isSuperAdmin() || $request->user()?->can('bypass-permissions');
        if ($org && ! $canBypass) {
            abort_unless($user->organizations()->where('organizations.id', $org->id)->exists(), 404);
        }
    }
}
