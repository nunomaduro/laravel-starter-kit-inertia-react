<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Dashboards\Http\Controllers\DashboardBuilderController;

Route::middleware(['auth', 'verified', 'tenant', 'feature:dashboards'])->group(function (): void {
    Route::get('dashboards', [DashboardBuilderController::class, 'index'])->name('dashboards.index');
    Route::get('dashboards/create', [DashboardBuilderController::class, 'create'])->name('dashboards.create');
    Route::post('dashboards', [DashboardBuilderController::class, 'store'])->name('dashboards.store')->middleware('throttle:30,1');
    Route::get('dashboards/{dashboard}', [DashboardBuilderController::class, 'show'])->name('dashboards.show');
    Route::get('dashboards/{dashboard}/edit', [DashboardBuilderController::class, 'edit'])->name('dashboards.edit');
    Route::put('dashboards/{dashboard}', [DashboardBuilderController::class, 'update'])->name('dashboards.update')->middleware('throttle:30,1');
    Route::delete('dashboards/{dashboard}', [DashboardBuilderController::class, 'destroy'])->name('dashboards.destroy');
    Route::post('dashboards/{dashboard}/set-default', [DashboardBuilderController::class, 'setDefault'])->name('dashboards.set-default')->middleware('throttle:10,1');
});
