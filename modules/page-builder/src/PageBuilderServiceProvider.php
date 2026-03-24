<?php

declare(strict_types=1);

namespace Modules\PageBuilder;

use App\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\PageBuilder\Features\PageBuilderFeature;
use Modules\PageBuilder\Models\Page;
use Modules\PageBuilder\Models\PageRevision;
use Modules\PageBuilder\Policies\PagePolicy;
use Modules\PageBuilder\Policies\PageRevisionPolicy;

final class PageBuilderServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'page-builder';
    }

    public function featureKey(): string
    {
        return 'page_builder';
    }

    /**
     * @return class-string
     */
    public function featureClass(): string
    {
        return PageBuilderFeature::class;
    }

    protected function bootModule(): void
    {
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(PageRevision::class, PageRevisionPolicy::class);
    }
}
