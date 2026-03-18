<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Contact\Http\Controllers\ContactSubmissionController;
use Spatie\Honeypot\ProtectAgainstSpam;

Route::get('contact', [ContactSubmissionController::class, 'create'])
    ->middleware('feature:contact')
    ->name('contact.create');
Route::post('contact', [ContactSubmissionController::class, 'store'])
    ->middleware(['feature:contact', ProtectAgainstSpam::class])
    ->name('contact.store');
