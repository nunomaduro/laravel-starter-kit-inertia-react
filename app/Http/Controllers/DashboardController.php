<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use App\Models\Organization;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $isSuperAdmin = $user->hasRole('super-admin');

        $props = [];

        if ($isSuperAdmin) {
            $props['usersCount'] = User::query()->count();
            $props['orgsCount'] = Organization::query()->count();
            $props['contactSubmissionsCount'] = ContactSubmission::query()->count();
            $props['usersGrowthPercent'] = $this->weeklyGrowthPercent(fn () => User::query());
            $props['orgsGrowthPercent'] = $this->weeklyGrowthPercent(fn () => Organization::query());
        }

        $props['weeklyStats'] = Inertia::defer(fn () => $this->weeklyStats());

        return Inertia::render('dashboard', $props);
    }

    /** @param Closure(): Builder<Model> $factory */
    private function weeklyGrowthPercent(Closure $factory): ?int
    {
        $thisWeek = $factory()
            ->whereBetween('created_at', [Carbon::today()->subDays(6)->startOfDay(), Carbon::now()])
            ->count();

        $lastWeek = $factory()
            ->whereBetween('created_at', [Carbon::today()->subDays(13)->startOfDay(), Carbon::today()->subDays(7)->endOfDay()])
            ->count();

        if ($lastWeek === 0) {
            return $thisWeek > 0 ? 100 : null;
        }

        return (int) round((($thisWeek - $lastWeek) / $lastWeek) * 100);
    }

    /** @return array<int, array{name: string, value: int}> */
    private function weeklyStats(): array
    {
        $days = collect(range(6, 0))->map(fn (int $i) => Carbon::today()->subDays($i));

        $signups = User::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $days->first())
            ->groupBy('date')
            ->pluck('count', 'date');

        return $days->map(fn (Carbon $day) => [
            'name' => $day->format('D'),
            'value' => (int) ($signups[$day->toDateString()] ?? 0),
        ])->values()->all();
    }
}
