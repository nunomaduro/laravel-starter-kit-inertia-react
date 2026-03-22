<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\PageBuilder\Http\Controllers\PageController;
use Modules\PageBuilder\Http\Controllers\PageViewController;

Route::middleware(['auth', 'verified', 'tenant', 'feature:page_builder'])->group(function (): void {
    Route::get('pages', [PageController::class, 'index'])->name('pages.index');
    Route::get('pages/create', [PageController::class, 'create'])->name('pages.create');
    Route::post('pages', [PageController::class, 'store'])->name('pages.store')->middleware('throttle:30,1');
    Route::get('pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
    Route::put('pages/{page}', [PageController::class, 'update'])->name('pages.update')->middleware('throttle:30,1');
    Route::get('pages/{page}/preview', [PageController::class, 'preview'])->name('pages.preview');
    Route::post('pages/{page}/duplicate', [PageController::class, 'duplicate'])->name('pages.duplicate');
    Route::delete('pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');
    Route::get('p/{slug}', [PageViewController::class, 'show'])->name('pages.show')->middleware('throttle:120,1');
});
