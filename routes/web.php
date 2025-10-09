<?php

declare(strict_types=1);

use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserEmailVerification;
use App\Http\Controllers\UserEmailVerificationNotificationController;
use App\Http\Controllers\UserPasswordController;
use App\Http\Controllers\UserTwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', fn () => Inertia::render('dashboard'))->name('dashboard');
});

Route::middleware('auth')->group(function (): void {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [UserController::class, 'edit'])->name('user.edit');
    Route::patch('settings/profile', [UserController::class, 'update'])->name('user.update');
    Route::delete('settings/profile', [UserController::class, 'destroy'])->name('user.destroy');

    Route::get('settings/password', [UserPasswordController::class, 'edit'])->name('password.edit');

    Route::put('settings/password', [UserPasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('settings/appearance', fn () => Inertia::render('settings/appearance'))->name('appearance.edit');

    Route::get('settings/two-factor', [UserTwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');
});

Route::middleware('guest')->group(function (): void {
    Route::get('register', [UserController::class, 'create'])
        ->name('register');

    Route::post('register', [UserController::class, 'store'])
        ->name('register.store');

    Route::get('login', [SessionController::class, 'create'])
        ->name('login');

    Route::post('login', [SessionController::class, 'store'])
        ->name('login.store');

    Route::get('forgot-password', [UserPasswordController::class, 'forgot'])
        ->name('password.request');

    Route::post('forgot-password', [UserPasswordController::class, 'email'])
        ->name('password.email');

    Route::get('reset-password/{token}', [UserPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [UserPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('verify-email', [UserEmailVerificationNotificationController::class, 'create'])
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', [UserEmailVerification::class, 'update'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [UserEmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::post('logout', [SessionController::class, 'destroy'])
        ->name('logout');
});
