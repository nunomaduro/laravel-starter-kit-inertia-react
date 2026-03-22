<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\BillingDashboardController;
use Modules\Billing\Http\Controllers\CreditController;
use Modules\Billing\Http\Controllers\InvoiceController;
use Modules\Billing\Http\Controllers\PricingController;

/*
 * Public pricing page (no auth required).
 */
Route::get('pricing', [PricingController::class, 'index'])->name('pricing');

/*
 * Authenticated + verified billing routes under the tenant middleware.
 */
Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::middleware('tenant')->group(function (): void {
        Route::get('billing', [BillingDashboardController::class, 'index'])->name('billing.index');
        Route::get('billing/credits', [CreditController::class, 'index'])->name('billing.credits.index');
        Route::post('billing/credits/purchase', [CreditController::class, 'purchase'])->name('billing.credits.purchase');
        Route::post('billing/credits/checkout/lemon-squeezy', [CreditController::class, 'checkoutLemonSqueezy'])->name('billing.credits.checkout.lemon-squeezy');
        Route::get('billing/invoices', [InvoiceController::class, 'index'])->name('billing.invoices.index');
        Route::get('billing/invoices/{invoice}', [InvoiceController::class, 'download'])->name('billing.invoices.download');
    });
});
