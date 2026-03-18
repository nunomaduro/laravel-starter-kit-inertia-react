<?php

declare(strict_types=1);

namespace Modules\Changelog;

use App\Support\ModuleServiceProvider;
use Modules\Changelog\Features\ChangelogFeature;

final class ChangelogServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'changelog';
    }

    public function featureKey(): string
    {
        return 'changelog';
    }

    /**
     * @return class-string
     */
    public function featureClass(): string
    {
        return ChangelogFeature::class;
    }
}
