<?php

declare(strict_types=1);

namespace Modules\PageBuilder;

use App\Support\ModuleServiceProvider;
use Modules\PageBuilder\Features\PageBuilderFeature;

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
}
