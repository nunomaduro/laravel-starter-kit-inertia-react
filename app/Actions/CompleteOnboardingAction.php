<?php

declare(strict_types=1);

namespace App\Actions;

use App\Features\GamificationFeature;
use App\Models\User;
use App\Support\FeatureHelper;
use LevelUp\Experience\Models\Achievement;
use Throwable;

final readonly class CompleteOnboardingAction
{
    public function handle(User $user): void
    {
        $user->update(['onboarding_completed' => true]);

        if (! FeatureHelper::isActiveForClass(GamificationFeature::class, $user)) {
            return;
        }

        $achievement = Achievement::query()->where('name', 'Profile Completed')->first();
        if ($achievement === null) {
            return;
        }
        if ($user->allAchievements()->where('achievements.id', $achievement->id)->exists()) {
            return;
        }

        try {
            $user->grantAchievement($achievement);
        } catch (Throwable) {
            // Fail silently so onboarding completion is never blocked
        }
    }
}
