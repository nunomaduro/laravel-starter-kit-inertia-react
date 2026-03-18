<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Help\Http\Controllers\HelpCenterController;
use Modules\Help\Http\Controllers\RateHelpArticleController;

Route::prefix('help')->name('help.')->middleware('feature:help')->group(function (): void {
    Route::get('/', [HelpCenterController::class, 'index'])->name('index');
    Route::get('/{helpArticle:slug}', [HelpCenterController::class, 'show'])->name('show');
    Route::post('/{helpArticle:slug}/rate', RateHelpArticleController::class)->name('rate');
});
