<?php

declare(strict_types=1);

namespace Modules\Reports\Jobs;

use App\Models\Organization;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Reports\Actions\ExportReportAsCsv;
use Modules\Reports\Actions\ExportReportAsHtml;
use Modules\Reports\Actions\ExportReportAsPdf;
use Modules\Reports\Enums\OutputFormat;
use Modules\Reports\Models\Report;

final class GenerateScheduledReportJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    public function __construct(
        public readonly int $reportId,
    ) {}

    public function handle(
        ExportReportAsPdf $pdfExporter,
        ExportReportAsHtml $htmlExporter,
        ExportReportAsCsv $csvExporter,
    ): void {
        $report = Report::query()->find($this->reportId);

        if (! $report instanceof Report) {
            return;
        }

        /** @var Organization|null $organization */
        $organization = Organization::query()->find($report->organization_id);

        if ($organization === null) {
            return;
        }

        match ($report->output_format) {
            OutputFormat::Pdf => $pdfExporter->handle($report, $organization, null, true),
            OutputFormat::Html => $htmlExporter->handle($report, $organization, null, true),
            OutputFormat::Csv => $csvExporter->handle($report, $organization, null, true),
        };
    }
}
