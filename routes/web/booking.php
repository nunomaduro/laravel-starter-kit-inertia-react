<?php

declare(strict_types=1);

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CancelBookingController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\PropertyInquiryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

// Bookings
Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::patch('/bookings/{booking}/cancel', CancelBookingController::class)->name('bookings.cancel');

// Reviews
Route::post('/properties/{property}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

// Wishlist
Route::post('/properties/{property}/wishlist', WishlistController::class)->name('wishlist.toggle');

// Messages
Route::get('/messages', [ConversationController::class, 'index'])->name('conversations.index');
Route::get('/messages/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
Route::post('/messages/{conversation}', [ConversationController::class, 'store'])->name('conversations.store');

// Pre-booking inquiry
Route::post('/properties/{property}/inquire', PropertyInquiryController::class)->name('properties.inquire');
