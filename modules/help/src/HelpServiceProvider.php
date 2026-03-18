<?php

declare(strict_types=1);

namespace Modules\Help;

use App\Support\ModuleServiceProvider;
use Modules\Help\Features\HelpFeature;

final class HelpServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'help';
    }

    public function featureKey(): string
    {
        return 'help';
    }

    /**
     * @return class-string
     */
    public function featureClass(): string
    {
        return HelpFeature::class;
    }
}
