<?php

declare(strict_types=1);

namespace Modules\Blog;

use App\Support\ModuleServiceProvider;
use Machour\DataTable\Http\Controllers\DataTableExportController;
use Machour\DataTable\Http\Controllers\DataTableReorderController;
use Machour\DataTable\Http\Controllers\DataTableToggleController;
use Modules\Blog\DataTables\PostDataTable;
use Modules\Blog\Features\BlogFeature;

final class BlogServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'blog';
    }

    public function featureKey(): string
    {
        return 'blog';
    }

    /**
     * @return class-string
     */
    public function featureClass(): string
    {
        return BlogFeature::class;
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
            $controller::register('posts', PostDataTable::class);
        }
    }

    protected function registerFilamentResources(): void
    {
        $panels = filament()->getPanels();

        foreach ($panels as $panel) {
            $panel
                ->discoverResources(
                    in: __DIR__.'/Filament/Resources',
                    for: 'Modules\\Blog\\Filament\\Resources',
                );
        }
    }
}
