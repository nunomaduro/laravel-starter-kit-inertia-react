<?php

declare(strict_types=1);

namespace Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Reports\Enums\OutputFormat;
use Modules\Reports\Http\Requests\StoreReportRequest;
use Modules\Reports\Http\Requests\UpdateReportRequest;
use Modules\Reports\Models\Report;
use Modules\Reports\Services\ReportDataSourceRegistry;

final class ReportController extends Controller
{
    public function __construct(
        private readonly ReportDataSourceRegistry $dataSourceRegistry,
    ) {}

    public function index(): Response
    {
        $reports = Report::query()
            ->latest('updated_at')
            ->get(['id', 'name', 'output_format', 'schedule', 'updated_at']);

        return Inertia::render('reports/index', [
            'reports' => $reports,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('reports/edit', [
            'report' => null,
            'puckJson' => ['root' => (object) [], 'content' => []],
            'dataSources' => $this->dataSourceRegistry->options(),
        ]);
    }

    public function store(StoreReportRequest $request): RedirectResponse
    {
        /** @var array{name: string, puck_json?: array<string, mixed>|null, schedule?: string|null, output_format: string} $validated */
        $validated = $request->validated();

        $report = new Report;
        $report->name = $validated['name'];
        $report->puck_json = $validated['puck_json'] ?? ['root' => (object) [], 'content' => []];
        $report->schedule = $validated['schedule'] ?? null;
        $report->output_format = OutputFormat::from($validated['output_format']);
        $report->save();

        return to_route('reports.edit', $report)->with('flash', ['status' => 'success', 'message' => 'Report created.']);
    }

    public function show(Report $report): Response
    {
        $organization = TenantContext::get();
        abort_unless($organization !== null, 404);

        /** @var array{root: array<string, mixed>, content: list<array<string, mixed>>} $puckJson */
        $puckJson = $report->puck_json ?? ['root' => (object) [], 'content' => []];
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

        return Inertia::render('reports/show', [
            'report' => [
                'id' => $report->id,
                'name' => $report->name,
                'puck_json' => $puckJson,
                'output_format' => $report->output_format->value,
            ],
        ]);
    }

    public function edit(Report $report): Response
    {
        return Inertia::render('reports/edit', [
            'report' => $report->only(['id', 'name', 'puck_json', 'schedule', 'output_format']),
            'puckJson' => $report->puck_json ?? ['root' => (object) [], 'content' => []],
            'dataSources' => $this->dataSourceRegistry->options(),
        ]);
    }

    public function update(UpdateReportRequest $request, Report $report): RedirectResponse
    {
        /** @var array{name: string, puck_json?: array<string, mixed>|null, schedule?: string|null, output_format: string} $validated */
        $validated = $request->validated();

        $report->update([
            'name' => $validated['name'],
            'puck_json' => $validated['puck_json'] ?? $report->puck_json,
            'schedule' => $validated['schedule'] ?? null,
            'output_format' => OutputFormat::from($validated['output_format']),
        ]);

        return to_route('reports.edit', $report)->with('flash', ['status' => 'success', 'message' => 'Report updated.']);
    }

    public function destroy(Report $report): RedirectResponse
    {
        $report->delete();

        return to_route('reports.index')->with('flash', ['status' => 'success', 'message' => 'Report deleted.']);
    }
}
