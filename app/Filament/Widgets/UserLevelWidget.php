<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Features\GamificationFeature;
use App\Support\FeatureHelper;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class UserLevelWidget extends BaseStatsOverviewWidget
{
    protected static ?int $sort = 1;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $user = auth()->user();
        if (! $user instanceof \App\Models\User) {
            return [];
        }

        if (! FeatureHelper::isActiveForClass(GamificationFeature::class, $user)) {
            return [];
        }

        $level = max(1, $user->getLevel());
        $points = $user->getPoints();
        $achievementsCount = $user->getUserAchievements()->count();

        return [
            Stat::make('Level', (string) $level),
            Stat::make('XP', (string) $points),
            Stat::make('Achievements', (string) $achievementsCount),
        ];
    }
}
