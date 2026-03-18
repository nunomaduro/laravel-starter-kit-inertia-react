<?php

declare(strict_types=1);

namespace Modules\Reports\Actions;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Modules\Reports\Models\Report;
use Modules\Reports\Models\ReportOutput;
use Modules\Reports\Services\ReportDataSourceRegistry;

final readonly class ExportReportAsHtml
{
    public function __construct(
        private ReportDataSourceRegistry $dataSourceRegistry,
    ) {}

    public function handle(Report $report, Organization $organization, ?User $user = null, bool $isScheduled = false): ReportOutput
    {
        $html = $this->renderHtml($report, $organization, $user);

        $filename = sprintf('report-outputs/%d/%s-%s.html', $report->id, $report->id, now()->format('Y-m-d-His'));
        Storage::disk('local')->put($filename, $html);

        /** @var int $size */
        $size = Storage::disk('local')->size($filename);

        return ReportOutput::query()->create([
            'report_id' => $report->id,
            'format' => 'html',
            'disk' => 'local',
            'path' => $filename,
            'size_bytes' => $size,
            'is_scheduled' => $isScheduled,
        ]);
    }

    public function renderHtml(Report $report, Organization $organization, ?User $user = null): string
    {
        /** @var array{root: array<string, mixed>, content: list<array<string, mixed>>} $puckJson */
        $puckJson = $report->puck_json ?? ['root' => [], 'content' => []];

        $resolvedContent = $this->resolveDataSources($puckJson['content'], $organization, $user);

        return $this->buildHtmlDocument($report->name, $resolvedContent);
    }

    /**
     * @param  list<array<string, mixed>>  $content
     * @return list<array<string, mixed>>
     */
    private function resolveDataSources(array $content, Organization $organization, ?User $user): array
    {
        return array_map(function (array $item) use ($organization, $user): array {
            /** @var array<string, mixed> $props */
            $props = $item['props'] ?? [];
            if (! isset($props['dataSource'])) {
                return $item;
            }

            /** @var string $dataSourceKey */
            $dataSourceKey = $props['dataSource'];
            $resolved = $this->dataSourceRegistry->resolve($dataSourceKey, $organization, $user, $props);
            $item['props'] = array_merge($props, ['data' => is_array($resolved) ? $resolved : $resolved->all()]);

            return $item;
        }, $content);
    }

    /**
     * @param  list<array<string, mixed>>  $content
     */
    private function buildHtmlDocument(string $title, array $content): string
    {
        $body = '';
        foreach ($content as $block) {
            $type = $block['type'] ?? 'unknown';
            /** @var array<string, mixed> $props */
            $props = $block['props'] ?? [];

            $text = $this->stringProp($props, 'text');
            $level = $this->intProp($props, 'level', 2);
            $label = $this->stringProp($props, 'label');
            $value = $this->stringProp($props, 'value');
            $chartType = $this->stringProp($props, 'chartType', 'bar');

            $body .= match ($type) {
                'Heading' => sprintf('<h%d>%s</h%d>', $level, e($text), $level),
                'Text' => sprintf('<p>%s</p>', e($text)),
                'ReportTable' => $this->renderTableHtml($props),
                'KpiCard' => sprintf(
                    '<div class="kpi"><strong>%s</strong><br><span class="value">%s</span></div>',
                    e($label),
                    e($value),
                ),
                'Summary' => sprintf('<div class="summary">%s</div>', e($text)),
                'Chart' => sprintf('<div class="chart-placeholder">[Chart: %s]</div>', e($chartType)),
                default => sprintf('<div>%s</div>', e($text)),
            };
        }

        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$title}</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 960px; margin: 2rem auto; padding: 0 1rem; color: #1a1a1a; }
                h1, h2, h3 { margin-top: 1.5rem; }
                table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
                th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; }
                th { background: #f5f5f5; font-weight: 600; }
                tr:nth-child(even) { background: #fafafa; }
                .kpi { display: inline-block; padding: 1rem; margin: 0.5rem; border: 1px solid #e0e0e0; border-radius: 8px; min-width: 150px; }
                .kpi .value { font-size: 1.5rem; font-weight: 700; }
                .summary { padding: 1rem; background: #f9f9f9; border-radius: 8px; margin: 1rem 0; }
                .chart-placeholder { padding: 2rem; background: #f0f0f0; border-radius: 8px; text-align: center; color: #666; }
            </style>
        </head>
        <body>
            <h1>{$title}</h1>
            <p style="color: #666; font-size: 0.875rem;">Generated on {$this->now()}</p>
            {$body}
        </body>
        </html>
        HTML;
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function renderTableHtml(array $props): string
    {
        /** @var list<array<string, mixed>> $data */
        $data = $props['data'] ?? [];
        if ($data === []) {
            return '<p><em>No data available.</em></p>';
        }

        $columns = array_keys($data[0]);
        $header = implode('', array_map(fn (string $col): string => '<th>'.e(ucwords(str_replace('_', ' ', $col))).'</th>', $columns));
        $rows = implode('', array_map(function (array $row) use ($columns): string {
            $cells = implode('', array_map(function (string $col) use ($row): string {
                $cellValue = $this->stringProp($row, $col);

                return '<td>'.e($cellValue).'</td>';
            }, $columns));

            return "<tr>{$cells}</tr>";
        }, $data));

        return "<table><thead><tr>{$header}</tr></thead><tbody>{$rows}</tbody></table>";
    }

    private function now(): string
    {
        return now()->format('F j, Y \a\t g:i A');
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function stringProp(array $props, string $key, string $default = ''): string
    {
        $value = $props[$key] ?? $default;

        return is_string($value) || is_int($value) || is_float($value) ? (string) $value : $default;
    }

    /**
     * @param  array<string, mixed>  $props
     */
    private function intProp(array $props, string $key, int $default = 0): int
    {
        $value = $props[$key] ?? $default;

        return is_int($value) ? $value : (is_numeric($value) ? (int) $value : $default);
    }
}
