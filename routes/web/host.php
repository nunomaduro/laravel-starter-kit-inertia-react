<?php

declare(strict_types=1);

use App\Http\Controllers\Host;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', Host\DashboardController::class)->name('dashboard');

Route::get('/properties', [Host\PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/create', [Host\PropertyController::class, 'create'])->name('properties.create');
Route::post('/properties', [Host\PropertyController::class, 'store'])->name('properties.store');
Route::get('/properties/{property}/edit', [Host\PropertyController::class, 'edit'])->name('properties.edit');
Route::put('/properties/{property}', [Host\PropertyController::class, 'update'])->name('properties.update');
Route::delete('/properties/{property}', [Host\PropertyController::class, 'destroy'])->name('properties.destroy');

Route::get('/properties/{property}/pricing', [Host\PropertyPricingController::class, 'show'])->name('properties.pricing.show');
Route::put('/properties/{property}/pricing', [Host\PropertyPricingController::class, 'update'])->name('properties.pricing.update');

Route::get('/bookings', [Host\BookingController::class, 'index'])->name('bookings.index');
Route::patch('/bookings/{booking}', [Host\BookingController::class, 'update'])->name('bookings.update');
Route::patch('/bookings/{booking}/cancel', Host\CancelBookingController::class)->name('bookings.cancel');

Route::get('/earnings', Host\EarningsController::class)->name('earnings');
Route::get('/calendar', Host\CalendarController::class)->name('calendar');

Route::post('/reviews/{review}/respond', Host\ReviewResponseController::class)->name('reviews.respond');
Route::get('/messages', [Host\MessageController::class, 'index'])->name('messages.index');
