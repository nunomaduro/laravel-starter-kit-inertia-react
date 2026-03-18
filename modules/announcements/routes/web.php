<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Announcements\Http\Controllers\AnnouncementsTableController;

Route::middleware(['web', 'auth', 'verified'])->group(function (): void {
    Route::get('announcements', [AnnouncementsTableController::class, 'index'])->name('announcements.table');
});
