<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\BatchUpdateUsersAction;
use App\Actions\BulkSoftDeleteUsers;
use App\Actions\DuplicateUser;
use App\DataTables\UserDataTable;
use App\Http\Requests\BatchUpdateUsersRequest;
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

    /**
     * Opt-in AI props: only expose AI panel / Thesys when configured.
     * When no AI backend or no Thesys key, those features are disabled.
     *
     * @return array{aiBaseUrl: string|null, thesysEnabled: bool}
     */
    private function dataTableAiProps(): array
    {
        $aiBackend = class_exists(\Laravel\Ai\Contracts\Agent::class)
            || class_exists(\PrismPHP\Prism::class);
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
            $u?->hasRole('super-admin')
            || $u?->can('bypass-permissions')
            || (config('tenancy.enabled', true) && $u?->canInOrganization('org.members.view')),
            403,
        );
    }

    private function ensureCanViewUser(User $user, Request $request): void
    {
        $this->authorizeViewUsers($request);
        $org = TenantContext::get();
        $canBypass = $request->user()?->hasRole('super-admin') || $request->user()?->can('bypass-permissions');
        if ($org && ! $canBypass) {
            abort_unless($user->organizations()->where('organizations.id', $org->id)->exists(), 404);
        }
    }
}
