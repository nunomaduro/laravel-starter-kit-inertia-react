<?php

declare(strict_types=1);

namespace Modules\Blog\Providers;

use App\Modules\Support\ModuleManifest;
use App\Modules\Support\ModuleProvider;
use Illuminate\Support\Facades\Gate;
use Machour\DataTable\Http\Controllers\DataTableExportController;
use Machour\DataTable\Http\Controllers\DataTableReorderController;
use Machour\DataTable\Http\Controllers\DataTableToggleController;
use Modules\Blog\DataTables\PostDataTable;
use Modules\Blog\Features\BlogFeature;
use Modules\Blog\Models\Post;
use Modules\Blog\Policies\PostPolicy;

final class BlogModuleServiceProvider extends ModuleProvider
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'blog',
            version: '1.0.0',
            description: 'Blog posts with categories, tags, and SEO support.',
            models: [Post::class],
            navigation: [
                ['label' => 'Blog', 'route' => 'blog.index', 'icon' => 'file-text', 'group' => 'Content'],
            ],
        );
    }

    protected function featureClass(): ?string
    {
        return BlogFeature::class;
    }

    protected function bootModule(): void
    {
        Gate::policy(Post::class, PostPolicy::class);

        $this->registerDataTables();
    }

    private function registerDataTables(): void
    {
        foreach ([
            DataTableExportController::class,
            DataTableToggleController::class,
            DataTableReorderController::class,
        ] as $controller) {
            $controller::register('posts', PostDataTable::class);
        }
    }
}
