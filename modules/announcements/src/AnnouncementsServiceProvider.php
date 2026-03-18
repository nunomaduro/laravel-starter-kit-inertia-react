<?php

declare(strict_types=1);

namespace Modules\Announcements;

use App\Support\ModuleServiceProvider;
use Machour\DataTable\Http\Controllers\DataTableExportController;
use Machour\DataTable\Http\Controllers\DataTableReorderController;
use Machour\DataTable\Http\Controllers\DataTableToggleController;
use Modules\Announcements\DataTables\AnnouncementDataTable;
use Modules\Announcements\Features\AnnouncementsFeature;

final class AnnouncementsServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'announcements';
    }

    public function featureKey(): string
    {
        return 'announcements';
    }

    /**
     * @return class-string
     */
    public function featureClass(): string
    {
        return AnnouncementsFeature::class;
    }

    protected function bootModule(): void
    {
        $this->registerDataTables();
        $this->registerFilamentResources();
    }

    protected function registerDataTables(): void
    {
        foreach ([
            DataTableExportController::class,
            DataTableToggleController::class,
            DataTableReorderController::class,
        ] as $controller) {
            $controller::register('announcements', AnnouncementDataTable::class);
        }
    }

    protected function registerFilamentResources(): void
    {
        $panels = filament()->getPanels();

        foreach ($panels as $panel) {
            $panel
                ->discoverResources(
                    in: __DIR__.'/Filament/Resources',
                    for: 'Modules\\Announcements\\Filament\\Resources',
                );
        }
    }
}
