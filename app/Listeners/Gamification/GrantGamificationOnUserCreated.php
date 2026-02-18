<?php

declare(strict_types=1);

namespace App\Listeners\Gamification;

use App\Events\User\UserCreated;
use App\Features\GamificationFeature;
use App\Support\FeatureHelper;
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
