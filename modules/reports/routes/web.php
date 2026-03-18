<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\ReportController;

Route::middleware(['auth', 'verified', 'tenant', 'feature:reports'])->group(function (): void {
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('reports', [ReportController::class, 'store'])->name('reports.store')->middleware('throttle:30,1');
    Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('reports/{report}/edit', [ReportController::class, 'edit'])->name('reports.edit');
    Route::put('reports/{report}', [ReportController::class, 'update'])->name('reports.update')->middleware('throttle:30,1');
    Route::delete('reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
    Route::post('reports/{report}/export', [ReportController::class, 'export'])->name('reports.export')->middleware('throttle:10,1');
    Route::get('reports/{report}/outputs/{output}/download', [ReportController::class, 'downloadOutput'])->name('reports.outputs.download');
});
