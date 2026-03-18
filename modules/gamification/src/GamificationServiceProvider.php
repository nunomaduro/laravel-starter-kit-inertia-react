<?php

declare(strict_types=1);

namespace Modules\Gamification;

use App\Events\User\UserCreated;
use App\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Event;
use Modules\Gamification\Features\GamificationFeature;
use Modules\Gamification\Listeners\GrantGamificationOnUserCreated;

final class GamificationServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'gamification';
    }

    public function featureKey(): string
    {
        return 'gamification';
    }

    /**
     * @return class-string
     */
    public function featureClass(): string
    {
        return GamificationFeature::class;
    }

    protected function bootModule(): void
    {
        Event::listen(UserCreated::class, GrantGamificationOnUserCreated::class);

        $this->registerFilamentWidgets();
    }

    protected function registerFilamentWidgets(): void
    {
        $panels = filament()->getPanels();

        foreach ($panels as $panel) {
            $panel
                ->discoverWidgets(
                    in: __DIR__.'/Filament/Widgets',
                    for: 'Modules\\Gamification\\Filament\\Widgets',
                );
        }
    }
}
