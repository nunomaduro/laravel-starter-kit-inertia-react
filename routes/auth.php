<?php

declare(strict_types=1);

use App\Http\Controllers\SessionController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserEmailResetNotificationController;
use App\Http\Controllers\UserEmailVerificationController;
use App\Http\Controllers\UserEmailVerificationNotificationController;
use App\Http\Controllers\UserPasswordController;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function (): void {
    Route::get('register', [UserController::class, 'create'])
        ->middleware('registration.enabled')
        ->name('register');
    Route::post('register', [UserController::class, 'store'])
        ->middleware(['registration.enabled', ProtectAgainstSpam::class, 'throttle:registration'])
        ->name('register.store');

    Route::get('reset-password/{token}', [UserPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [UserPasswordController::class, 'store'])
        ->middleware('throttle:password-reset-submit')
        ->name('password.store');

    Route::get('forgot-password', [UserEmailResetNotificationController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [UserEmailResetNotificationController::class, 'store'])
        ->middleware('throttle:password-reset-request')
        ->name('password.email');

    Route::get('login', [SessionController::class, 'create'])
        ->name('login');
    Route::post('login', [SessionController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');
});

Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('auth.social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('auth.social.callback');

Route::middleware('auth')->group(function (): void {
    Route::get('verify-email', [UserEmailVerificationNotificationController::class, 'create'])
        ->name('verification.notice');
    Route::post('email/verification-notification', [UserEmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::get('verify-email/{id}/{hash}', [UserEmailVerificationController::class, 'update'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('logout', [SessionController::class, 'destroy'])
        ->name('logout');
});
