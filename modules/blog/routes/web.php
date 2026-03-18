<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\BlogController;
use Modules\Blog\Http\Controllers\PostsTableController;

Route::prefix('blog')->name('blog.')->middleware('feature:blog')->group(function (): void {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/{post:slug}', [BlogController::class, 'show'])->name('show');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('posts', [PostsTableController::class, 'index'])->name('posts.table');
});
