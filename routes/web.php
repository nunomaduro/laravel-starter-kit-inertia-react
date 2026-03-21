<?php

declare(strict_types=1);

/*
 * All routes must have ->name() for RBAC (permission:sync-routes). Run:
 *   php artisan permission:sync-routes
 * after adding or changing routes.
 */

use App\Http\Controllers\Api\SlugAvailabilityController;
use App\Http\Controllers\Billing\BillingDashboardController;
use App\Http\Controllers\Billing\CreditController;
use App\Http\Controllers\Billing\InvoiceController;
use App\Http\Controllers\Billing\PaddleWebhookController;
use App\Http\Controllers\Billing\PricingController;
use App\Http\Controllers\Billing\StripeWebhookController;
use App\Http\Controllers\CategoriesTableController;
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Dev\ComponentShowcaseController;
use App\Http\Controllers\Dev\PageGalleryController;
use App\Http\Controllers\EnterpriseInquiryController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Internal\CaddyAskController;
use App\Http\Controllers\InvitationAcceptController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationInvitationController;
use App\Http\Controllers\OrganizationMemberController;
use App\Http\Controllers\OrganizationsTableController;
use App\Http\Controllers\OrganizationSwitchController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PageViewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TermsAcceptController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsersTableController;
use App\Http\Middleware\InternalRequestMiddleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Honeypot\ProtectAgainstSpam;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

if (app()->environment(['local', 'staging'])) {
    Route::get('dev/components', ComponentShowcaseController::class)
        ->middleware(['auth', 'feature:component_showcase'])
        ->name('dev.components');

    Route::get('dev/pages', PageGalleryController::class)
        ->middleware(['auth'])
        ->name('dev.pages');
}

Route::get('/favicon.ico', function (): BinaryFileResponse|RedirectResponse {
    $path = public_path('favicon.ico');

    if (File::exists($path)) {
        return response()->file($path, ['Content-Type' => 'image/x-icon']);
    }

    return redirect('/favicon.svg', 302);
})->name('favicon');

Route::get('robots.txt', function (): Response {
    $base = mb_rtrim(config('app.url'), '/');

    return response("User-agent: *\nDisallow:\n\nSitemap: {$base}/sitemap.xml\n", 200, [
        'Content-Type' => 'text/plain',
    ]);
})->name('robots');

Route::get('up/ready', [HealthController::class, 'ready'])->name('up.ready');
Route::get('up', [HealthController::class, 'up'])->name('up');

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

Route::get('invitations/{token}', [InvitationAcceptController::class, 'show'])->name('invitations.show');
Route::post('invitations/{token}/accept', [InvitationAcceptController::class, 'store'])->name('invitations.accept')->middleware('auth');

Route::get('cookie-consent/accept', CookieConsentController::class)
    ->middleware('feature:cookie_consent')
    ->name('cookie-consent.accept');

Route::get('legal/terms', fn () => Inertia::render('legal/terms'))->name('legal.terms');
Route::get('legal/privacy', fn () => Inertia::render('legal/privacy'))->name('legal.privacy');

Route::get('pricing', [PricingController::class, 'index'])->name('pricing');

Route::get('enterprise', [EnterpriseInquiryController::class, 'create'])
    ->name('enterprise-inquiries.create');
Route::post('enterprise', [EnterpriseInquiryController::class, 'store'])
    ->middleware(ProtectAgainstSpam::class)
    ->name('enterprise-inquiries.store');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('terms/accept', [TermsAcceptController::class, 'show'])->name('terms.accept');
    Route::post('terms/accept', [TermsAcceptController::class, 'store'])->name('terms.accept.store');

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('showcase', fn () => Inertia::render('showcase/index'))->name('showcase');

    Route::get('chat', fn () => Inertia::render('chat/index'))->name('chat');

    Route::get('categories', [CategoriesTableController::class, 'index'])->name('categories.table');
    Route::get('users', [UsersTableController::class, 'index'])->name('users.table');
    Route::post('users/bulk-soft-delete', [UsersTableController::class, 'bulkSoftDelete'])->name('users.bulk-soft-delete');
    Route::patch('users/batch-update', [UsersTableController::class, 'batchUpdate'])->name('users.batch-update');
    Route::post('users/{user}/duplicate', [UsersTableController::class, 'duplicate'])->name('users.duplicate');
    Route::get('users/{user}', [UsersTableController::class, 'show'])->name('users.show');
    Route::post('users/{id}/restore', [UsersTableController::class, 'restore'])->name('users.restore');
    Route::delete('users/{id}/force-delete', [UsersTableController::class, 'forceDelete'])->name('users.force-delete');

    Route::middleware('tenancy.enabled')->group(function (): void {
        Route::post('organizations/switch', OrganizationSwitchController::class)
            ->middleware('throttle:20,1')
            ->name('organizations.switch');
        Route::get('organizations/list', [OrganizationsTableController::class, 'index'])->name('organizations.list');
        Route::resource('organizations', OrganizationController::class)->except(['edit']);
        Route::get('organizations/{organization}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
        Route::get('organizations/{organization}/members', [OrganizationMemberController::class, 'index'])->name('organizations.members.index');
        Route::put('organizations/{organization}/members/{member}', [OrganizationMemberController::class, 'update'])->name('organizations.members.update')->scopeBindings();
        Route::delete('organizations/{organization}/members/{member}', [OrganizationMemberController::class, 'destroy'])->name('organizations.members.destroy')->scopeBindings();
        Route::post('organizations/{organization}/invitations', [OrganizationInvitationController::class, 'store'])->name('organizations.invitations.store');
        Route::delete('organizations/{organization}/invitations/{invitation}', [OrganizationInvitationController::class, 'destroy'])->name('organizations.invitations.destroy')->scopeBindings();
        Route::put('organizations/{organization}/invitations/{invitation}/resend', [OrganizationInvitationController::class, 'update'])->name('organizations.invitations.resend')->scopeBindings();
    });

    Route::get('search', SearchController::class)->middleware('tenant')->name('search');

    Route::middleware('tenant')->group(function (): void {
        Route::get('billing', [BillingDashboardController::class, 'index'])->name('billing.index');
        Route::get('billing/credits', [CreditController::class, 'index'])->name('billing.credits.index');
        Route::post('billing/credits/purchase', [CreditController::class, 'purchase'])->name('billing.credits.purchase');
        Route::post('billing/credits/checkout/lemon-squeezy', [CreditController::class, 'checkoutLemonSqueezy'])->name('billing.credits.checkout.lemon-squeezy');
        Route::get('billing/invoices', [InvoiceController::class, 'index'])->name('billing.invoices.index');
        Route::get('billing/invoices/{invoice}', [InvoiceController::class, 'download'])->name('billing.invoices.download');
    });

    Route::middleware('tenant')->group(function (): void {
        Route::get('pages', [PageController::class, 'index'])->name('pages.index');
        Route::get('pages/create', [PageController::class, 'create'])->name('pages.create');
        Route::post('pages', [PageController::class, 'store'])->name('pages.store')->middleware('throttle:30,1');
        Route::get('pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
        Route::put('pages/{page}', [PageController::class, 'update'])->name('pages.update')->middleware('throttle:30,1');
        Route::get('pages/{page}/preview', [PageController::class, 'preview'])->name('pages.preview');
        Route::post('pages/{page}/duplicate', [PageController::class, 'duplicate'])->name('pages.duplicate');
        Route::delete('pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');
        Route::get('p/{slug}', [PageViewController::class, 'show'])->name('pages.show')->middleware('throttle:120,1');
    });

    Route::get('profile/export-pdf', App\Http\Controllers\ProfileExportPdfController::class)
        ->middleware('feature:profile_pdf_export')
        ->name('profile.export-pdf');

    // HR Module
    Route::prefix('hr')->name('hr.')->group(function (): void {
        Route::resource('employees', App\Http\Controllers\Hr\EmployeeController::class);
        Route::resource('leave-requests', App\Http\Controllers\Hr\LeaveRequestController::class);
    });

    // CRM Module
    Route::prefix('crm')->name('crm.')->group(function (): void {
        Route::resource('contacts', App\Http\Controllers\Crm\ContactController::class);
        Route::resource('deals', App\Http\Controllers\Crm\DealController::class);
    });
});

Route::get('/api/slug-availability', SlugAvailabilityController::class)
    ->middleware(['auth', 'throttle:10,1'])
    ->name('api.slug-availability');

Route::get('/internal/caddy/ask', CaddyAskController::class)
    ->middleware(InternalRequestMiddleware::class)
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('internal.caddy.ask');

Route::post('webhooks/stripe', StripeWebhookController::class)->name('webhooks.stripe')->withoutMiddleware([ValidateCsrfToken::class]);
Route::post('webhooks/paddle', PaddleWebhookController::class)->name('webhooks.paddle')->withoutMiddleware([ValidateCsrfToken::class]);

Route::webhooks('webhooks/spatie', 'default');

Route::middleware(['auth', 'feature:onboarding'])->group(function (): void {
    Route::get('onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('personal-data-exports/{zipFilename}', [Spatie\PersonalDataExport\Http\Controllers\PersonalDataExportController::class, 'export'])
        ->name('personal-data-exports');

    Route::delete('user', [UserController::class, 'destroy'])->name('user.destroy');
});
