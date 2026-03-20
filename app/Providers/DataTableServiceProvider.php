<?php

declare(strict_types=1);

namespace App\Providers;

use App\DataTables\CategoryDataTable;
use App\DataTables\OrganizationDataTable;
use App\DataTables\UserDataTable;
use Illuminate\Support\ServiceProvider;
use Machour\DataTable\Http\Controllers\DataTableAiController;
use Machour\DataTable\Http\Controllers\DataTableAsyncFilterController;
use Machour\DataTable\Http\Controllers\DataTableCascadingFilterController;
use Machour\DataTable\Http\Controllers\DataTableDetailRowController;
use Machour\DataTable\Http\Controllers\DataTableExportController;
use Machour\DataTable\Http\Controllers\DataTableImportController;
use Machour\DataTable\Http\Controllers\DataTableInlineEditController;
use Machour\DataTable\Http\Controllers\DataTableReorderController;
use Machour\DataTable\Http\Controllers\DataTableSelectAllController;
use Machour\DataTable\Http\Controllers\DataTableToggleController;

final class DataTableServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach ([
            DataTableAiController::class,
            DataTableExportController::class,
            DataTableAsyncFilterController::class,
            DataTableCascadingFilterController::class,
            DataTableInlineEditController::class,
            DataTableToggleController::class,
            DataTableSelectAllController::class,
            DataTableDetailRowController::class,
            DataTableImportController::class,
            DataTableReorderController::class,
        ] as $controller) {
            $controller::register('users', UserDataTable::class);
        }

        DataTableExportController::register('organizations', OrganizationDataTable::class);

        foreach ([
            DataTableExportController::class,
            DataTableAsyncFilterController::class,
            DataTableCascadingFilterController::class,
        ] as $controller) {
            $controller::register('categories', CategoryDataTable::class);
        }
    }
}
