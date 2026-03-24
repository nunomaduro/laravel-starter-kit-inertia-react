<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\RecordAuditLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreOrgDomainRequest;
use App\Jobs\VerifyOrganizationDomain;
use App\Models\OrganizationDomain;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

final class OrgDomainsController extends Controller
{
    public function show(): Response
    {
        $organization = TenantContext::get();

        $domains = $organization instanceof \App\Models\Organization
            ? OrganizationDomain::query()
                ->where('organization_id', $organization->id)
                ->where('type', 'custom')->latest()
                ->get()
                ->map(fn (OrganizationDomain $d): array => [
                    'id' => $d->id,
                    'domain' => $d->domain,
                    'type' => $d->type,
                    'status' => $d->status,
                    'is_verified' => $d->is_verified,
                    'is_primary' => $d->is_primary,
                    'cname_target' => $d->cname_target,
                    'failure_reason' => $d->failure_reason,
                    'dns_check_attempts' => $d->dns_check_attempts,
                    'ssl_expires_at' => $d->ssl_expires_at?->toIso8601String(),
                    'verified_at' => $d->verified_at?->toIso8601String(),
                ])
            : collect();

        return Inertia::render('settings/domains', [
            'organization' => [
                'id' => $organization?->id,
                'name' => $organization?->name,
                'slug' => $organization?->slug,
            ],
            'domains' => $domains,
            'baseDomain' => config('tenancy.domain'),
        ]);
    }

    public function store(StoreOrgDomainRequest $request, RecordAuditLog $auditLog): RedirectResponse
    {
        $organization = TenantContext::get();

        abort_unless($organization, 404);

        $validated = $request->validated();

        $cnameTarget = $organization->slug.'.'.config('tenancy.domain');

        $domain = OrganizationDomain::query()->create([
            'organization_id' => $organization->id,
            'domain' => mb_strtolower((string) $validated['domain']),
            'type' => 'custom',
            'status' => 'pending_dns',
            'is_verified' => false,
            'is_primary' => false,
            'cname_target' => $cnameTarget,
            'verification_token' => Str::random(32),
        ]);

        dispatch(new VerifyOrganizationDomain($domain))->delay(now()->addMinutes(5));

        $auditLog->handle(
            action: 'domain.added',
            subjectType: 'organization_domain',
            subjectId: $domain->id,
            newValue: ['domain' => $domain->domain],
            organizationId: $organization->id,
        );

        return back()->with('success', 'Custom domain added. Please configure your DNS.');
    }

    public function destroy(OrganizationDomain $domain, RecordAuditLog $auditLog): RedirectResponse
    {
        $organization = TenantContext::get();

        abort_if(! $organization || $domain->organization_id !== $organization->id, 403);

        $auditLog->handle(
            action: 'domain.removed',
            subjectType: 'organization_domain',
            subjectId: $domain->id,
            oldValue: ['domain' => $domain->domain],
            organizationId: $organization->id,
        );

        $domain->delete();

        return back()->with('success', 'Domain removed.');
    }

    public function verify(OrganizationDomain $domain): RedirectResponse
    {
        $organization = TenantContext::get();

        abort_if(! $organization || $domain->organization_id !== $organization->id, 403);

        dispatch(new VerifyOrganizationDomain($domain));

        return back()->with('success', 'DNS verification started.');
    }
}
