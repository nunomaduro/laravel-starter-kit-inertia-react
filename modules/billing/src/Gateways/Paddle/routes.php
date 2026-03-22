<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\PaddleWebhookController;

Route::post('webhooks/paddle', PaddleWebhookController::class)
    ->name('webhooks.paddle')
    ->withoutMiddleware([ValidateCsrfToken::class]);
