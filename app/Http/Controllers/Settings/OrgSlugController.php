<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\RecordAuditLog;
use App\Http\Controllers\Controller;
use App\Models\SlugRedirect;
use App\Rules\SlugAvailable;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class OrgSlugController extends Controller
{
    public function show(): Response
    {
        $organization = TenantContext::get();

        return Inertia::render('settings/general', [
            'organization' => [
                'id' => $organization?->id,
                'name' => $organization?->name,
                'slug' => $organization?->slug,
            ],
            'baseDomain' => config('tenancy.domain'),
        ]);
    }

    public function update(Request $request, RecordAuditLog $auditLog): RedirectResponse
    {
        $organization = TenantContext::get();

        abort_unless($organization, 404);

        $validated = $request->validate([
            'slug' => ['required', 'string', new SlugAvailable($organization->id)],
            'confirmed' => ['required', 'accepted'],
        ]);

        $oldSlug = $organization->slug;
        $newSlug = $validated['slug'];

        if ($oldSlug === $newSlug) {
            return back()->with('success', 'No changes made.');
        }

        SlugRedirect::query()->updateOrCreate(
            ['old_slug' => $oldSlug],
            [
                'organization_id' => $organization->id,
                'redirects_to_slug' => $newSlug,
                'expires_at' => null,
            ]
        );

        // Update any existing redirects pointing to old slug
        SlugRedirect::query()
            ->where('redirects_to_slug', $oldSlug)
            ->update(['redirects_to_slug' => $newSlug]);

        $organization->slug = $newSlug;
        $organization->save();

        $auditLog->handle(
            action: 'slug.changed',
            subjectType: 'organization',
            subjectId: $organization->id,
            oldValue: ['slug' => $oldSlug],
            newValue: ['slug' => $newSlug],
            organizationId: $organization->id,
        );

        return back()->with('success', 'Workspace URL updated successfully.');
    }
}
