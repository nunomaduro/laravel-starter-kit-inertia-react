<?php

declare(strict_types=1);

namespace Modules\Dashboards\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Dashboards\Http\Requests\StoreDashboardRequest;
use Modules\Dashboards\Http\Requests\UpdateDashboardRequest;
use Modules\Dashboards\Models\Dashboard;
use Modules\Dashboards\Services\DashboardDataSourceRegistry;

final class DashboardBuilderController extends Controller
{
    public function __construct(
        private readonly DashboardDataSourceRegistry $dataSourceRegistry,
    ) {
        $this->authorizeResource(Dashboard::class, 'dashboard');
    }

    public function index(): Response
    {
        $dashboards = Dashboard::query()
            ->latest('updated_at')
            ->get(['id', 'name', 'is_default', 'refresh_interval', 'updated_at']);

        return Inertia::render('dashboards/index', [
            'dashboards' => $dashboards,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('dashboards/edit', [
            'dashboard' => null,
            'puckJson' => ['root' => (object) [], 'content' => []],
            'dataSources' => $this->dataSourceRegistry->options(),
        ]);
    }

    public function store(StoreDashboardRequest $request): RedirectResponse
    {
        /** @var array{name: string, puck_json?: array<string, mixed>|null, is_default?: bool, refresh_interval?: int|null} $validated */
        $validated = $request->validated();

        $isDefault = (bool) ($validated['is_default'] ?? false);

        DB::transaction(function () use ($validated, $isDefault, &$dashboard): void {
            if ($isDefault) {
                Dashboard::query()->where('is_default', true)->update(['is_default' => false]);
            }

            $dashboard = new Dashboard;
            $dashboard->name = $validated['name'];
            $dashboard->puck_json = $validated['puck_json'] ?? ['root' => (object) [], 'content' => []];
            $dashboard->is_default = $isDefault;
            $dashboard->refresh_interval = $validated['refresh_interval'] ?? null;
            $dashboard->save();
        });

        /** @var Dashboard $dashboard */
        return to_route('dashboards.edit', $dashboard)->with('flash', ['status' => 'success', 'message' => 'Dashboard created.']);
    }

    public function show(Dashboard $dashboard): Response
    {
        $organization = TenantContext::get();
        abort_unless($organization !== null, 404);

        /** @var array{root: array<string, mixed>, content: list<array<string, mixed>>} $puckJson */
        $puckJson = $dashboard->puck_json ?? ['root' => (object) [], 'content' => []];
        $content = $puckJson['content'];
        $user = request()->user();

        $content = array_map(function (array $item) use ($user, $organization): array {
            /** @var array<string, mixed> $props */
            $props = $item['props'] ?? [];
            if (! isset($props['dataSource'])) {
                return $item;
            }

            /** @var string $dataSourceKey */
            $dataSourceKey = $props['dataSource'];
            $resolved = $this->dataSourceRegistry->resolve(
                $dataSourceKey,
                $organization,
                $user instanceof User ? $user : null,
                $props,
            );
            $item['props'] = array_merge($props, ['data' => is_array($resolved) ? $resolved : $resolved->all()]);

            return $item;
        }, $content);

        $puckJson['content'] = $content;

        return Inertia::render('dashboards/show', [
            'dashboard' => [
                'id' => $dashboard->id,
                'name' => $dashboard->name,
                'puck_json' => $puckJson,
                'is_default' => $dashboard->is_default,
                'refresh_interval' => $dashboard->refresh_interval,
            ],
        ]);
    }

    public function edit(Dashboard $dashboard): Response
    {
        return Inertia::render('dashboards/edit', [
            'dashboard' => $dashboard->only(['id', 'name', 'puck_json', 'is_default', 'refresh_interval']),
            'puckJson' => $dashboard->puck_json ?? ['root' => (object) [], 'content' => []],
            'dataSources' => $this->dataSourceRegistry->options(),
        ]);
    }

    public function update(UpdateDashboardRequest $request, Dashboard $dashboard): RedirectResponse
    {
        /** @var array{name: string, puck_json?: array<string, mixed>|null, is_default?: bool, refresh_interval?: int|null} $validated */
        $validated = $request->validated();

        $isDefault = (bool) ($validated['is_default'] ?? $dashboard->is_default);

        DB::transaction(function () use ($validated, $isDefault, $dashboard): void {
            if ($isDefault && ! $dashboard->is_default) {
                Dashboard::query()->where('is_default', true)->update(['is_default' => false]);
            }

            $dashboard->update([
                'name' => $validated['name'],
                'puck_json' => $validated['puck_json'] ?? $dashboard->puck_json,
                'is_default' => $isDefault,
                'refresh_interval' => $validated['refresh_interval'] ?? null,
            ]);
        });

        return to_route('dashboards.edit', $dashboard)->with('flash', ['status' => 'success', 'message' => 'Dashboard updated.']);
    }

    public function destroy(Dashboard $dashboard): RedirectResponse
    {
        $dashboard->delete();

        return to_route('dashboards.index')->with('flash', ['status' => 'success', 'message' => 'Dashboard deleted.']);
    }

    public function setDefault(Dashboard $dashboard): RedirectResponse
    {
        $this->authorize('setDefault', $dashboard);

        DB::transaction(function () use ($dashboard): void {
            Dashboard::query()->where('is_default', true)->update(['is_default' => false]);
            $dashboard->update(['is_default' => true]);
        });

        return to_route('dashboards.index')->with('flash', ['status' => 'success', 'message' => "{$dashboard->name} set as default."]);
    }
}
