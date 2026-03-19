<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

final readonly class DashboardMetricsService
{
    /** @param Closure(): Builder<Model> $factory */
    public function weeklyGrowthPercent(Closure $factory): ?int
    {
        $thisWeek = $factory()
            ->whereBetween('created_at', [Date::today()->subDays(6)->startOfDay(), Date::now()])
            ->count();

        $lastWeek = $factory()
            ->whereBetween('created_at', [Date::today()->subDays(13)->startOfDay(), Date::today()->subDays(7)->endOfDay()])
            ->count();

        if ($lastWeek === 0) {
            return $thisWeek > 0 ? 100 : null;
        }

        return (int) round((($thisWeek - $lastWeek) / $lastWeek) * 100);
    }

    /** @return array<int, array{name: string, value: int}> */
    public function weeklySignupStats(): array
    {
        $days = collect(range(6, 0))->map(fn (int $i) => Date::today()->subDays($i));

        $signups = User::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $days->first())
            ->groupBy('date')
            ->pluck('count', 'date');

        return $days->map(fn (\Carbon\CarbonImmutable $day): array => [
            'name' => $day->format('D'),
            'value' => (int) ($signups[$day->toDateString()] ?? 0),
        ])->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function superAdminProps(): array
    {
        return [
            'usersCount' => User::query()->count(),
            'orgsCount' => Organization::query()->count(),
            'usersGrowthPercent' => $this->weeklyGrowthPercent(fn () => User::query()),
            'orgsGrowthPercent' => $this->weeklyGrowthPercent(fn () => Organization::query()),
        ];
    }
}
