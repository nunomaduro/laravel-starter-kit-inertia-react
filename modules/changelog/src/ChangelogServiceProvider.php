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

    protected function bootModule(): void
    {
        $this->registerFilamentResources();
    }

    protected function registerFilamentResources(): void
    {
        $panels = filament()->getPanels();

        foreach ($panels as $panel) {
            $panel
                ->discoverResources(
                    in: __DIR__.'/Filament/Resources',
                    for: 'Modules\\Changelog\\Filament\\Resources',
                );
        }
    }
}
