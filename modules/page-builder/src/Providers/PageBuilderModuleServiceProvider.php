<?php

declare(strict_types=1);

namespace Modules\PageBuilder\Providers;

use App\Modules\Support\ModuleManifest;
use App\Modules\Support\ModuleProvider;
use Illuminate\Support\Facades\Gate;
use Modules\PageBuilder\Features\PageBuilderFeature;
use Modules\PageBuilder\Models\Page;
use Modules\PageBuilder\Models\PageRevision;
use Modules\PageBuilder\Policies\PagePolicy;
use Modules\PageBuilder\Policies\PageRevisionPolicy;

final class PageBuilderModuleServiceProvider extends ModuleProvider
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'page-builder',
            version: '1.0.0',
            description: 'Visual page builder with Puck editor for creating custom pages.',
            models: [
                Page::class,
                PageRevision::class,
            ],
            navigation: [
                ['label' => 'Pages', 'route' => 'pages.index', 'icon' => 'layout', 'group' => 'Content'],
            ],
        );
    }

    protected function featureClass(): ?string
    {
        return PageBuilderFeature::class;
    }

    protected function bootModule(): void
    {
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(PageRevision::class, PageRevisionPolicy::class);
    }
}
