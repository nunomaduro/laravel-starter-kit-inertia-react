<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\BulkSoftDeleteUsers;
use App\Actions\DuplicateUser;
use App\DataTables\UserDataTable;
use App\Http\Requests\BulkSoftDeleteUsersRequest;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class UsersTableController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeViewUsers($request);

        return Inertia::render('users/table', UserDataTable::inertiaProps($request));
    }

    public function bulkSoftDelete(BulkSoftDeleteUsersRequest $request, BulkSoftDeleteUsers $action): RedirectResponse
    {
        $count = $action->handle(array_map('intval', $request->validated('ids')), $request->user());

        return back()->with('flash', ['type' => 'success', 'message' => "{$count} user(s) soft-deleted."]);
    }

    public function duplicate(User $user, DuplicateUser $action, Request $request): RedirectResponse
    {
        $this->ensureCanViewUser($user, $request);
        $copy = $action->handle($user);

        return redirect()->route('users.table')->with('flash', ['type' => 'success', 'message' => 'User duplicated as '.$copy->name.'.']);
    }

    public function show(User $user, Request $request): Response
    {
        $this->ensureCanViewUser($user, $request);

        return Inertia::render('users/show', UserDataTable::showProps($user));
    }

    private function authorizeViewUsers(Request $request): void
    {
        $u = $request->user();
        abort_unless(
            $u?->can('bypass-permissions') || (config('tenancy.enabled', true) && $u?->canInOrganization('org.members.view')),
            403,
        );
    }

    private function ensureCanViewUser(User $user, Request $request): void
    {
        $this->authorizeViewUsers($request);
        $org = TenantContext::get();
        if ($org && ! $request->user()?->can('bypass-permissions')) {
            abort_unless($user->organizations()->where('organizations.id', $org->id)->exists(), 404);
        }
    }
}
