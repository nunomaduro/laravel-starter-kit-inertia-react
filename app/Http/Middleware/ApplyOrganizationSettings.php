<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Providers\SettingsOverlayServiceProvider;
use App\Services\OrganizationSettingsService;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies per-organization settings overrides to config().
 *
 * Must run AFTER SetTenantContext so TenantContext::get() is populated.
 */
final readonly class ApplyOrganizationSettings
{
    public function __construct(private OrganizationSettingsService $service) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $organization = TenantContext::get();

        if ($organization instanceof \App\Models\Organization) {
            $this->service->applyForOrganization(
                $organization,
                SettingsOverlayServiceProvider::orgOverridableKeys(),
            );
        }

        return $next($request);
    }
}
