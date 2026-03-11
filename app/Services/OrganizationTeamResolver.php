<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\PermissionsTeamResolver;

/**
 * Resolves the current organization (team) ID for Spatie Permission.
 * Uses explicitly set team id (e.g. in tests or CreateOrganizationAction), otherwise TenantContext::id(), or 0 for global.
 */
final class OrganizationTeamResolver implements PermissionsTeamResolver
{
    private int|string|null $overrideTeamId = null;

    public function getPermissionsTeamId(): int|string
    {
        if ($this->overrideTeamId !== null) {
            return $this->overrideTeamId;
        }

        return TenantContext::id() ?? 0;
    }

    public function setPermissionsTeamId(int|string|Model|null $id): void
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        $this->overrideTeamId = $id;
    }
}
