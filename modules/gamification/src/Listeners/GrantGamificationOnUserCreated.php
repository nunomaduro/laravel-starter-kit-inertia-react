<?php

declare(strict_types=1);

namespace Modules\Gamification\Listeners;

use App\Events\User\UserCreated;
use App\Support\FeatureHelper;
use Modules\Gamification\Features\GamificationFeature;
use Throwable;

final class GrantGamificationOnUserCreated
{
    public function handle(UserCreated $event): void
    {
        $user = $event->user;

        if (! FeatureHelper::isActiveForClass(GamificationFeature::class, $user)) {
            return;
        }

        try {
            $user->addPoints(10, reason: 'Signed up');
        } catch (Throwable) {
            // Fail silently so registration is never blocked
        }
    }
}
