<?php

declare(strict_types=1);

use AlizHarb\ActivityLog\Http\Middleware\ActivityLogContextMiddleware;
use App\Http\Middleware\AdditionalSecurityHeaders;
use App\Http\Middleware\ApplyOrganizationSettings;
use App\Http\Middleware\AutoPermissionMiddleware;
use App\Http\Middleware\EnforceIpWhitelist;
use App\Http\Middleware\EnsureCountryAllowed;
use App\Http\Middleware\EnsureFeatureActive;
use App\Http\Middleware\EnsureOnboardingComplete;
use App\Http\Middleware\EnsureRegistrationEnabled;
use App\Http\Middleware\EnsureScrambleApiDocsVisible;
use App\Http\Middleware\EnsureTenancyEnabled;
use App\Http\Middleware\EnsureTenantContext;
use App\Http\Middleware\EnsureTermsAccepted;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ResolveDomainMiddleware;
use App\Http\Middleware\ServeFavicon;
use App\Http\Middleware\SetTenantContext;
use App\Http\Middleware\ThrottleTwoFactorManagement;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Spatie\Csp\AddCspHeaders;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Spatie\ResponseCache\Middlewares\CacheResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);
        $middleware->statefulApi();
        $middleware->validateCsrfTokens(except: ['webhooks/*', 'lemon-squeezy/*']);

        $middleware->alias([
            'feature' => EnsureFeatureActive::class,
            'registration.enabled' => EnsureRegistrationEnabled::class,
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'auto.permission' => AutoPermissionMiddleware::class,
            'ip.whitelist' => EnforceIpWhitelist::class,
            'tenant' => EnsureTenantContext::class,
            'tenancy.enabled' => EnsureTenancyEnabled::class,
            'billing.country' => EnsureCountryAllowed::class,
        ]);

        $webAppend = [
            EnsureScrambleApiDocsVisible::class,
            AddCspHeaders::class,
            AdditionalSecurityHeaders::class,
            ActivityLogContextMiddleware::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            SetTenantContext::class,
            ApplyOrganizationSettings::class,
            AddLinkHeadersForPreloadedAssets::class,
            CacheResponse::class,
            AutoPermissionMiddleware::class,
            ThrottleTwoFactorManagement::class,
            EnsureOnboardingComplete::class,
            EnsureTermsAccepted::class,
        ];

        $middleware->web(
            append: $webAppend,
            prepend: [
                ServeFavicon::class,
                ResolveDomainMiddleware::class,
            ],
        );

        $middleware->api(append: [
            AddCspHeaders::class,
            AdditionalSecurityHeaders::class,
            SetTenantContext::class,
            ApplyOrganizationSettings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (MartinPetricko\LaravelDatabaseMail\Exceptions\DatabaseMailException $e): void {
            MartinPetricko\LaravelDatabaseMail\Facades\LaravelDatabaseMail::logException($e);
        });
    })->create();
