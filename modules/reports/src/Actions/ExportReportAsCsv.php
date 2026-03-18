<?php

declare(strict_types=1);

namespace Modules\Reports\Actions;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Modules\Reports\Models\Report;
use Modules\Reports\Models\ReportOutput;
use Modules\Reports\Services\ReportDataSourceRegistry;

final readonly class ExportReportAsCsv
{
    public function __construct(
        private ReportDataSourceRegistry $dataSourceRegistry,
    ) {}

    public function handle(Report $report, Organization $organization, ?User $user = null, bool $isScheduled = false): ReportOutput
    {
        $csv = $this->buildCsv($report, $organization, $user);

        $filename = sprintf('report-outputs/%d/%s-%s.csv', $report->id, $report->id, now()->format('Y-m-d-His'));
        Storage::disk('local')->put($filename, $csv);

        /** @var int $size */
        $size = Storage::disk('local')->size($filename);

        return ReportOutput::query()->create([
            'report_id' => $report->id,
            'format' => 'csv',
            'disk' => 'local',
            'path' => $filename,
            'size_bytes' => $size,
            'is_scheduled' => $isScheduled,
        ]);
    }

    private function buildCsv(Report $report, Organization $organization, ?User $user): string
    {
        /** @var array{root: array<string, mixed>, content: list<array<string, mixed>>} $puckJson */
        $puckJson = $report->puck_json ?? ['root' => [], 'content' => []];

        $allRows = [];
        $columns = [];

        foreach ($puckJson['content'] as $block) {
            /** @var array<string, mixed> $props */
            $props = $block['props'] ?? [];
            if (! isset($props['dataSource'])) {
                continue;
            }

            /** @var string $dataSourceKey */
            $dataSourceKey = $props['dataSource'];
            $resolved = $this->dataSourceRegistry->resolve($dataSourceKey, $organization, $user, $props);
            /** @var list<array<string, mixed>> $data */
            $data = is_array($resolved) ? $resolved : $resolved->all();

            if ($data === []) {
                continue;
            }

            if ($columns === []) {
                $columns = array_keys($data[0]);
            }

            foreach ($data as $row) {
                $allRows[] = $row;
            }
        }

        if ($allRows === []) {
            return '';
        }

        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            return '';
        }

        fputcsv($handle, $columns);
        foreach ($allRows as $row) {
            $values = array_map(function (string $col) use ($row): string {
                $raw = $row[$col] ?? '';

                return is_string($raw) || is_int($raw) || is_float($raw) ? (string) $raw : '';
            }, $columns);
            fputcsv($handle, $values);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv !== false ? $csv : '';
    }
}
