<?php

declare(strict_types=1);

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
Route::patch('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');

Route::get('/listings', [Admin\ListingController::class, 'index'])->name('listings.index');
Route::patch('/listings/{property}', [Admin\ListingController::class, 'update'])->name('listings.update');

Route::get('/reviews', [Admin\ReviewController::class, 'index'])->name('reviews.index');
Route::delete('/reviews/{review}', [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');
