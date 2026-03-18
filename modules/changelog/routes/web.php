<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Changelog\Http\Controllers\ChangelogController;

Route::get('changelog', [ChangelogController::class, 'index'])
    ->middleware('feature:changelog')
    ->name('changelog.index');
