<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Gamification\Http\Controllers\AchievementsController;

Route::middleware('auth')->group(function (): void {
    Route::get('settings/achievements', [AchievementsController::class, 'show'])
        ->middleware('feature:gamification')
        ->name('achievements.show');
});
