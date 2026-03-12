<?php

declare(strict_types=1);

/*
 * Web installer routes. Loaded only when APP_ENV is local or testing (see bootstrap/app.php).
 * When setup is complete, EnsureNotInstalled redirects /install to home.
 */

use App\Http\Controllers\InstallController;
use App\Http\Middleware\EnsureNotInstalled;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'install.env', 'throttle:install'])->group(function (): void {
    Route::get('install/complete', [InstallController::class, 'complete'])->name('install.complete');
    Route::middleware(EnsureNotInstalled::class)->group(function (): void {
        Route::get('install', [InstallController::class, 'show'])->name('install');
        Route::post('install', [InstallController::class, 'store'])->name('install.store');
        Route::post('install/express', [InstallController::class, 'express'])->name('install.express');
        Route::get('install/express/status', [InstallController::class, 'expressStatus'])->name('install.express.status');
        Route::post('install/test-connection', [InstallController::class, 'testConnection'])->name('install.test-connection');
        Route::match(['GET', 'POST'], 'install/ai-models', [InstallController::class, 'aiModels'])->name('install.ai-models');
        Route::post('install/migrate/run', [InstallController::class, 'migrateRun'])->name('install.migrate.run');
        Route::get('install/migrate/status', [InstallController::class, 'migrateStatus'])->name('install.migrate.status');
    });
});
