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
use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\Changelog\ChangelogController;
use App\Http\Controllers\ContactSubmissionController;
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Dev\ComponentShowcaseController;
use App\Http\Controllers\Dev\PageGalleryController;
use App\Http\Controllers\EnterpriseInquiryController;
use App\Http\Controllers\HelpCenter\HelpCenterController;
use App\Http\Controllers\HelpCenter\RateHelpArticleController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\Internal\CaddyAskController;
use App\Http\Controllers\InvitationAcceptController;
use App\Http\Controllers\Notifications\ClearAllNotificationsController;
use App\Http\Controllers\Notifications\DeleteNotificationController;
use App\Http\Controllers\Notifications\IndexNotificationsController;
use App\Http\Controllers\Notifications\MarkAllNotificationsReadController;
use App\Http\Controllers\Notifications\MarkNotificationReadController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationInvitationController;
use App\Http\Controllers\OrganizationMemberController;
use App\Http\Controllers\OrganizationSwitchController;
use App\Http\Controllers\OrgThemeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PageViewController;
use App\Http\Controllers\PersonalDataExportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\Settings\AchievementsController;
use App\Http\Controllers\Settings\AuditLogController;
use App\Http\Controllers\Settings\BrandingController;
use App\Http\Controllers\Settings\NotificationPreferencesController;
use App\Http\Controllers\Settings\OrgBrandingUserControlsController;
use App\Http\Controllers\Settings\OrgDomainsController;
use App\Http\Controllers\Settings\OrgFeaturesController;
use App\Http\Controllers\Settings\OrgRolesController;
use App\Http\Controllers\Settings\OrgSlugController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\TermsAcceptController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserEmailResetNotificationController;
use App\Http\Controllers\UserEmailVerificationController;
use App\Http\Controllers\UserEmailVerificationNotificationController;
use App\Http\Controllers\UserPasswordController;
use App\Http\Controllers\UserPreferencesController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UsersTableController;
use App\Http\Controllers\UserTwoFactorAuthenticationController;
use App\Http\Middleware\EnsureNotInstalled;
use App\Http\Middleware\InternalRequestMiddleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Honeypot\ProtectAgainstSpam;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

// Web installer — accessible before auth; EnsureNotInstalled redirects to /admin once done
Route::middleware(EnsureNotInstalled::class)->group(function (): void {
    Route::get('install', [InstallController::class, 'show'])->name('install');
    Route::post('install', [InstallController::class, 'store'])->name('install.store');
    Route::post('install/express', [InstallController::class, 'express'])->name('install.express');
    Route::post('install/test-connection', [InstallController::class, 'testConnection'])->name('install.test-connection');
});

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

Route::get('up', function (): JsonResponse {
    $checks = ['app' => true];
    try {
        DB::connection()->getPdo();
        $checks['database'] = true;
    } catch (Throwable) {
        $checks['database'] = false;
    }

    $ok = ! in_array(false, $checks, true);

    return response()->json(['status' => $ok ? 'ok' : 'degraded', 'checks' => $checks], $ok ? 200 : 503);
})->name('up');

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

Route::get('invitations/{token}', [InvitationAcceptController::class, 'show'])->name('invitations.show');
Route::post('invitations/{token}/accept', [InvitationAcceptController::class, 'store'])->name('invitations.accept')->middleware('auth');

Route::get('cookie-consent/accept', CookieConsentController::class)
    ->middleware('feature:cookie_consent')
    ->name('cookie-consent.accept');

Route::get('legal/terms', fn () => Inertia::render('legal/terms'))->name('legal.terms');
Route::get('legal/privacy', fn () => Inertia::render('legal/privacy'))->name('legal.privacy');

Route::prefix('blog')->name('blog.')->middleware('feature:blog')->group(function (): void {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/{post:slug}', [BlogController::class, 'show'])->name('show');
});

Route::get('changelog', [ChangelogController::class, 'index'])
    ->middleware('feature:changelog')
    ->name('changelog.index');

Route::prefix('help')->name('help.')->middleware('feature:help')->group(function (): void {
    Route::get('/', [HelpCenterController::class, 'index'])->name('index');
    Route::get('/{helpArticle:slug}', [HelpCenterController::class, 'show'])->name('show');
    Route::post('/{helpArticle:slug}/rate', RateHelpArticleController::class)->name('rate');
});

Route::get('pricing', [PricingController::class, 'index'])->name('pricing');

Route::get('contact', [ContactSubmissionController::class, 'create'])
    ->middleware('feature:contact')
    ->name('contact.create');
Route::post('contact', [ContactSubmissionController::class, 'store'])
    ->middleware(['feature:contact', ProtectAgainstSpam::class])
    ->name('contact.store');

Route::get('enterprise', [EnterpriseInquiryController::class, 'create'])
    ->name('enterprise-inquiries.create');
Route::post('enterprise', [EnterpriseInquiryController::class, 'store'])
    ->middleware(ProtectAgainstSpam::class)
    ->name('enterprise-inquiries.store');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('terms/accept', [TermsAcceptController::class, 'show'])->name('terms.accept');
    Route::post('terms/accept', [TermsAcceptController::class, 'store'])->name('terms.accept.store');

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('chat', fn () => Inertia::render('chat/index'))->name('chat');

    Route::get('users', [UsersTableController::class, 'index'])->name('users.table');
    Route::post('users/bulk-soft-delete', [UsersTableController::class, 'bulkSoftDelete'])->name('users.bulk-soft-delete');
    Route::post('users/{user}/duplicate', [UsersTableController::class, 'duplicate'])->name('users.duplicate');
    Route::get('users/{user}', [UsersTableController::class, 'show'])->name('users.show');

    Route::middleware('tenancy.enabled')->group(function (): void {
        Route::post('organizations/switch', OrganizationSwitchController::class)
            ->middleware('throttle:20,1')
            ->name('organizations.switch');
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

    Route::middleware(['tenant', 'permission:org.settings.manage'])->group(function (): void {
        Route::get('settings/branding', [BrandingController::class, 'edit'])->name('settings.branding.edit');
        Route::put('settings/branding', [BrandingController::class, 'update'])->name('settings.branding.update');
        Route::post('settings/branding/user-controls', OrgBrandingUserControlsController::class)->name('settings.branding.user-controls');
        Route::get('settings/audit-log', AuditLogController::class)->name('settings.audit-log');

        Route::get('settings/features', [OrgFeaturesController::class, 'show'])->name('settings.features.show');
        Route::post('settings/features', [OrgFeaturesController::class, 'update'])->name('settings.features.update');

        Route::get('settings/roles', [OrgRolesController::class, 'index'])->name('settings.roles.index');
        Route::post('settings/roles', [OrgRolesController::class, 'store'])->name('settings.roles.store');
        Route::delete('settings/roles/{role}', [OrgRolesController::class, 'destroy'])->name('settings.roles.destroy');

        Route::get('settings/general', [OrgSlugController::class, 'show'])->name('settings.general.show');
        Route::patch('settings/general/slug', [OrgSlugController::class, 'update'])->name('settings.general.slug.update');
        Route::get('settings/domains', [OrgDomainsController::class, 'show'])->name('settings.domains.show');
        Route::post('settings/domains', [OrgDomainsController::class, 'store'])->name('settings.domains.store');
        Route::delete('settings/domains/{domain}', [OrgDomainsController::class, 'destroy'])->name('settings.domains.destroy');
        Route::post('settings/domains/{domain}/verify', [OrgDomainsController::class, 'verify'])->name('settings.domains.verify');
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
});

Route::get('/api/slug-availability', SlugAvailabilityController::class)
    ->middleware('auth')
    ->name('api.slug-availability');

Route::get('/internal/caddy/ask', CaddyAskController::class)
    ->middleware(InternalRequestMiddleware::class)
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('internal.caddy.ask');

Route::post('webhooks/stripe', StripeWebhookController::class)->name('webhooks.stripe')->withoutMiddleware([ValidateCsrfToken::class]);
Route::post('webhooks/paddle', PaddleWebhookController::class)->name('webhooks.paddle')->withoutMiddleware([ValidateCsrfToken::class]);

Route::middleware(['auth', 'feature:onboarding'])->group(function (): void {
    Route::get('onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
});

Route::middleware('auth')->group(function (): void {
    Route::personalDataExports('personal-data-exports');

    Route::delete('user', [UserController::class, 'destroy'])->name('user.destroy');

    Route::patch('user/preferences', [UserPreferencesController::class, 'update'])->name('user.preferences.update');

    Route::post('org/theme', [OrgThemeController::class, 'save'])->name('org.theme.save');
    Route::delete('org/theme', [OrgThemeController::class, 'reset'])->name('org.theme.reset');
    Route::post('org/theme/analyze-logo', [OrgThemeController::class, 'analyzeLogo'])->name('org.theme.analyze-logo');

    Route::redirect('settings', '/settings/profile')->name('settings');
    Route::get('settings/profile', [UserProfileController::class, 'edit'])->name('user-profile.edit');
    Route::patch('settings/profile', [UserProfileController::class, 'update'])->name('user-profile.update');

    Route::get('settings/password', [UserPasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [UserPasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('settings/appearance', fn () => Inertia::render('appearance/update'))
        ->middleware('feature:appearance_settings')
        ->name('appearance.edit');

    Route::get('settings/personal-data-export', fn () => Inertia::render('settings/personal-data-export'))
        ->middleware('feature:personal_data_export')
        ->name('personal-data-export.edit');
    Route::post('settings/personal-data-export', PersonalDataExportController::class)
        ->middleware(['feature:personal_data_export', 'throttle:3,1'])
        ->name('personal-data-export.store');

    Route::get('settings/two-factor', [UserTwoFactorAuthenticationController::class, 'show'])
        ->middleware('feature:two_factor_auth')
        ->name('two-factor.show');

    Route::get('settings/achievements', [AchievementsController::class, 'show'])
        ->middleware('feature:gamification')
        ->name('achievements.show');

    Route::get('settings/notifications', [NotificationPreferencesController::class, 'show'])->name('settings.notifications.show');
    Route::patch('settings/notifications', [NotificationPreferencesController::class, 'update'])->name('settings.notifications.update');

    Route::prefix('notifications')->name('notifications.')->group(function (): void {
        Route::get('/', IndexNotificationsController::class)->name('index');
        Route::post('{notification}/read', MarkNotificationReadController::class)->name('read');
        Route::post('read-all', MarkAllNotificationsReadController::class)->name('read-all');
        Route::delete('{notification}', DeleteNotificationController::class)->name('delete');
        Route::delete('/', ClearAllNotificationsController::class)->name('clear');
    });
});

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
