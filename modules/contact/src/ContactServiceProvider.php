<?php

declare(strict_types=1);

namespace Modules\Contact;

use App\Support\ModuleServiceProvider;
use Modules\Contact\Features\ContactFeature;

final class ContactServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'contact';
    }

    public function featureKey(): string
    {
        return 'contact';
    }

    /**
     * @return class-string
     */
    public function featureClass(): string
    {
        return ContactFeature::class;
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
                    for: 'Modules\\Contact\\Filament\\Resources',
                );
        }
    }
}
