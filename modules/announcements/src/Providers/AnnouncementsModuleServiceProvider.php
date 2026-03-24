<?php

declare(strict_types=1);

namespace Modules\Announcements\Providers;

use App\Modules\Support\ModuleManifest;
use App\Modules\Support\ModuleProvider;
use Illuminate\Support\Facades\Gate;
use Machour\DataTable\Http\Controllers\DataTableExportController;
use Machour\DataTable\Http\Controllers\DataTableReorderController;
use Machour\DataTable\Http\Controllers\DataTableToggleController;
use Modules\Announcements\DataTables\AnnouncementDataTable;
use Modules\Announcements\Features\AnnouncementsFeature;
use Modules\Announcements\Models\Announcement;
use Modules\Announcements\Policies\AnnouncementPolicy;

final class AnnouncementsModuleServiceProvider extends ModuleProvider
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'announcements',
            version: '1.0.0',
            description: 'In-app announcement banners with audience targeting, scheduling, and DataTable management.',
            models: [Announcement::class],
            navigation: [
                ['label' => 'Announcements', 'route' => 'announcements.table', 'icon' => 'megaphone', 'group' => 'Platform'],
            ],
        );
    }

    protected function featureClass(): ?string
    {
        return AnnouncementsFeature::class;
    }

    protected function bootModule(): void
    {
        Gate::policy(Announcement::class, AnnouncementPolicy::class);

        $this->registerDataTables();
    }

    private function registerDataTables(): void
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
