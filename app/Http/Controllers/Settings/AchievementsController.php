<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class AchievementsController extends Controller
{
    public function show(#[CurrentUser] User $user): Response
    {
        $level = max(1, $user->getLevel());
        $points = $user->getPoints();
        $nextLevelAt = $user->experience()->exists() ? $user->nextLevelAt(showAsPercentage: true) : 0;
        $achievements = $user->getUserAchievements()->map(fn ($a): array => [
            'id' => $a->id,
            'name' => $a->name,
            'description' => $a->description,
            'image' => $a->image,
            'is_secret' => (bool) $a->is_secret,
            'progress' => $a->pivot->progress ?? null,
            'unlocked_at' => isset($a->pivot->created_at) ? $a->pivot->created_at->toIso8601String() : null,
        ])->values()->all();

        return Inertia::render('settings/achievements', [
            'level' => $level,
            'points' => $points,
            'next_level_percentage' => $nextLevelAt,
            'achievements' => $achievements,
        ]);
    }
}
