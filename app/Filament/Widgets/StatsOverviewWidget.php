<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $total = User::query()->count();
        $newThisMonth = User::query()->where('created_at', '>=', now()->startOfMonth())->count();
        $verified = User::query()->whereNotNull('email_verified_at')->count();

        return [
            Stat::make('Total Users', (string) $total)
                ->description('All registered users')
                ->descriptionIcon(Heroicon::OutlinedUsers)
                ->color('primary')
                ->url('/users'),

            Stat::make('New This Month', (string) $newThisMonth)
                ->description('Joined since '.now()->startOfMonth()->format('M 1'))
                ->descriptionIcon(Heroicon::OutlinedUserPlus)
                ->color('success'),

            Stat::make('Verified', (string) $verified)
                ->description(round($total > 0 ? ($verified / $total) * 100 : 0, 1).'% email verified')
                ->descriptionIcon(Heroicon::OutlinedCheckBadge)
                ->color($verified === $total ? 'success' : 'warning'),
        ];
    }
}
