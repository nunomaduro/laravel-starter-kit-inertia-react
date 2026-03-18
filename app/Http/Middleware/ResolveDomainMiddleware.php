<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Models\OrganizationDomain;
use App\Models\SlugRedirect;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves tenant from: (1) exact verified organization_domains.domain match,
 * (2) subdomain of config tenancy.domain (e.g. acme.app.com -> org slug "acme").
 * Also handles slug redirects with 301 responses when an org slug has changed.
 */
final class ResolveDomainMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (TenantContext::check()) {
            return $next($request);
        }

        $host = $request->getHost();

        $domain = OrganizationDomain::query()
            ->where('domain', $host)
            ->where('is_verified', true)
            ->with('organization')
            ->first();

        if ($domain && $domain->organization) {
            TenantContext::set($domain->organization);

            if ($domain->type === 'custom' && ! $request->secure()) {
                return redirect()->secure($request->getRequestUri());
            }

            return $next($request);
        }

        $baseDomain = config('tenancy.domain');
        if ($baseDomain && config('tenancy.subdomain_resolution', true)) {
            $slugRedirectResponse = $this->checkSlugRedirect($host, $baseDomain, $request);
            if ($slugRedirectResponse instanceof RedirectResponse) {
                return $slugRedirectResponse;
            }

            $organization = $this->resolveOrganizationFromSubdomain($host, $baseDomain);
            if ($organization instanceof Organization) {
                TenantContext::set($organization);

                return $next($request);
            }
        }

        return $next($request);
    }

    private function checkSlugRedirect(string $host, string $baseDomain, Request $request): ?RedirectResponse
    {
        $prefix = $this->extractSubdomainPrefix($host, $baseDomain);

        if ($prefix === null) {
            return null;
        }

        $slugRedirect = SlugRedirect::query()
            ->where('old_slug', $prefix)
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $slugRedirect) {
            return null;
        }

        $newHost = $slugRedirect->redirects_to_slug.'.'.$baseDomain;
        $scheme = $request->getScheme();
        $port = $request->getPort();
        $defaultPort = $scheme === 'https' ? 443 : 80;
        $portSuffix = ($port && $port !== $defaultPort) ? ':'.$port : '';
        $newUrl = $scheme.'://'.$newHost.$portSuffix.$request->getRequestUri();

        return redirect()->away($newUrl, 301);
    }

    private function extractSubdomainPrefix(string $host, string $baseDomain): ?string
    {
        $baseDomain = mb_strtolower($baseDomain);
        $host = mb_strtolower($host);
        $host = preg_replace('/:\d+$/', '', $host) ?? $host;

        if (! str_ends_with($host, '.'.$baseDomain) || $host === $baseDomain) {
            return null;
        }

        $suffix = '.'.$baseDomain;
        $prefix = mb_substr($host, 0, -mb_strlen($suffix));

        if ($prefix === '' || str_contains($prefix, '.')) {
            return null;
        }

        if (! preg_match('/^[a-z0-9\-]+$/', $prefix)) {
            return null;
        }

        return $prefix;
    }

    private function resolveOrganizationFromSubdomain(string $host, string $baseDomain): ?Organization
    {
        $prefix = $this->extractSubdomainPrefix($host, $baseDomain);

        if ($prefix === null) {
            return null;
        }

        return Organization::query()->where('slug', $prefix)->first();
    }
}
