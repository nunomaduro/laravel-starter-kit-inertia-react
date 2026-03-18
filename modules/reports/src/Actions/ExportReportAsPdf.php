<?php

declare(strict_types=1);

namespace Modules\Reports\Actions;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Modules\Reports\Models\Report;
use Modules\Reports\Models\ReportOutput;
use Spatie\LaravelPdf\Facades\Pdf;

final readonly class ExportReportAsPdf
{
    public function __construct(
        private ExportReportAsHtml $htmlExporter,
    ) {}

    public function handle(Report $report, Organization $organization, ?User $user = null, bool $isScheduled = false): ReportOutput
    {
        $html = $this->htmlExporter->renderHtml($report, $organization, $user);

        $filename = sprintf('report-outputs/%d/%s-%s.pdf', $report->id, $report->id, now()->format('Y-m-d-His'));
        $fullPath = Storage::disk('local')->path($filename);

        Storage::disk('local')->makeDirectory(dirname($filename));

        Pdf::html($html)
            ->margins(15, 15, 15, 15)
            ->save($fullPath);

        /** @var int $size */
        $size = Storage::disk('local')->size($filename);

        return ReportOutput::query()->create([
            'report_id' => $report->id,
            'format' => 'pdf',
            'disk' => 'local',
            'path' => $filename,
            'size_bytes' => $size,
            'is_scheduled' => $isScheduled,
        ]);
    }
}
