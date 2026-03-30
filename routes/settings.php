<?php

declare(strict_types=1);

use App\Http\Controllers\Notifications\ClearAllNotificationsController;
use App\Http\Controllers\Notifications\DeleteNotificationController;
use App\Http\Controllers\Notifications\IndexNotificationsController;
use App\Http\Controllers\Notifications\MarkAllNotificationsReadController;
use App\Http\Controllers\Notifications\MarkNotificationReadController;
use App\Http\Controllers\OrgThemeController;
use App\Http\Controllers\PersonalDataExportController;
use App\Http\Controllers\Settings\AuditLogController;
use App\Http\Controllers\Settings\BrandingController;
use App\Http\Controllers\Settings\EmailTemplatesController;
use App\Http\Controllers\Settings\NotificationPreferencesController;
use App\Http\Controllers\Settings\OrgBrandingUserControlsController;
use App\Http\Controllers\Settings\OrgDomainsController;
use App\Http\Controllers\Settings\OrgFeaturesController;
use App\Http\Controllers\Settings\OrgRolesController;
use App\Http\Controllers\Settings\OrgSlugController;
use App\Http\Controllers\Settings\WebhooksController;
use App\Http\Controllers\UserPasswordController;
use App\Http\Controllers\UserPreferencesController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserTwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Settings & Preferences Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function (): void {
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

Route::middleware(['auth', 'verified', 'tenant', 'permission:org.settings.manage'])->group(function (): void {
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

    Route::get('settings/webhooks', [WebhooksController::class, 'index'])->name('settings.webhooks.index');
    Route::get('settings/webhooks/create', [WebhooksController::class, 'create'])->name('settings.webhooks.create');
    Route::post('settings/webhooks', [WebhooksController::class, 'store'])->name('settings.webhooks.store');
    Route::get('settings/webhooks/{webhook}/edit', [WebhooksController::class, 'edit'])->name('settings.webhooks.edit');
    Route::put('settings/webhooks/{webhook}', [WebhooksController::class, 'update'])->name('settings.webhooks.update');
    Route::delete('settings/webhooks/{webhook}', [WebhooksController::class, 'destroy'])->name('settings.webhooks.destroy');
    Route::post('settings/webhooks/{webhook}/test', [WebhooksController::class, 'testPing'])->name('settings.webhooks.test');
    Route::post('settings/webhooks/{webhook}/reset-circuit', [WebhooksController::class, 'resetCircuit'])->name('settings.webhooks.reset-circuit');
    Route::post('settings/webhooks/{webhook}/regenerate-secret', [WebhooksController::class, 'regenerateSecret'])->name('settings.webhooks.regenerate-secret');

    Route::get('settings/email-templates', [EmailTemplatesController::class, 'index'])->name('settings.email-templates.index');
    Route::get('settings/email-templates/{event}/edit', [EmailTemplatesController::class, 'edit'])->name('settings.email-templates.edit');
    Route::put('settings/email-templates/{event}', [EmailTemplatesController::class, 'update'])->name('settings.email-templates.update');
    Route::post('settings/email-templates/{event}/preview', [EmailTemplatesController::class, 'preview'])->name('settings.email-templates.preview');
    Route::delete('settings/email-templates/{event}', [EmailTemplatesController::class, 'reset'])->name('settings.email-templates.reset');
});
