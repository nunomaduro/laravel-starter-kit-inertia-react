<?php

declare(strict_types=1);

namespace Modules\Gamification\Providers;

use App\Events\User\UserCreated;
use App\Modules\Support\ModuleManifest;
use App\Modules\Support\ModuleProvider;
use Illuminate\Support\Facades\Event;
use Modules\Gamification\Features\GamificationFeature;
use Modules\Gamification\Listeners\GrantGamificationOnUserCreated;

final class GamificationModuleServiceProvider extends ModuleProvider
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            name: 'gamification',
            version: '1.0.0',
            description: 'Badges, points, levels, and achievements for users.',
            navigation: [
                ['label' => 'Achievements', 'route' => 'achievements.show', 'icon' => 'trophy', 'group' => 'Platform'],
            ],
        );
    }

    protected function featureClass(): ?string
    {
        return GamificationFeature::class;
    }

    protected function bootModule(): void
    {
        Event::listen(UserCreated::class, GrantGamificationOnUserCreated::class);
    }
}
