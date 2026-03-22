<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\StripeWebhookController;

Route::post('webhooks/stripe', StripeWebhookController::class)
    ->name('webhooks.stripe')
    ->withoutMiddleware([ValidateCsrfToken::class]);
