<?php

declare(strict_types=1);

namespace Modules\Announcements;

use App\Support\ModuleServiceProvider;
use Machour\DataTable\Http\Controllers\DataTableExportController;
use Machour\DataTable\Http\Controllers\DataTableReorderController;
use Machour\DataTable\Http\Controllers\DataTableToggleController;
use Modules\Announcements\DataTables\AnnouncementDataTable;
use Modules\Announcements\Features\AnnouncementsFeature;
use Modules\Announcements\Models\Announcement;
use Modules\Announcements\Policies\AnnouncementPolicy;

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
        \Illuminate\Support\Facades\Gate::policy(Announcement::class, AnnouncementPolicy::class);

        $this->registerDataTables();
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
}
