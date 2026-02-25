<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use Maatwebsite\Excel\Excel;
use pxlrbt\FilamentExcel\Actions\ExportAction;
use pxlrbt\FilamentExcel\Actions\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

trait HasStandardExports
{
    protected static function makeExportHeaderAction(string $filename, bool $queued = false, int $chunkSize = 0): ExportAction
    {
        $xlsx = ExcelExport::make()
            ->fromTable()
            ->withFilename($filename.'-'.now()->format('Y-m-d'));

        $csv = ExcelExport::make()
            ->fromTable()
            ->withFilename($filename.'-'.now()->format('Y-m-d').'-csv')
            ->withWriterType(Excel::CSV);

        if ($queued) {
            $xlsx->queue();
            $csv->queue();
        }

        if ($chunkSize > 0) {
            $xlsx->withChunkSize($chunkSize);
            $csv->withChunkSize($chunkSize);
        }

        return ExportAction::make()
            ->exports([$xlsx, $csv]);
    }

    protected static function makeExportBulkAction(): ExportBulkAction
    {
        return ExportBulkAction::make();
    }
}
