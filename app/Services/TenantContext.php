<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Throwable;

/**
 * Service for managing the current tenant (organization) context.
 *
 * OCTANE COMPATIBILITY:
 * Register the flush listener when using Laravel Octane:
 * Octane::on('request-received', fn () => TenantContext::flush());
 */
final class TenantContext
{
    private static ?Organization $current = null;

    public static function set(?Organization $organization): void
    {
        self::$current = $organization;

        if (self::hasSession()) {
            if ($organization instanceof Organization) {
                session(['current_organization_id' => $organization->id]);
            } else {
                session()->forget('current_organization_id');
            }
        }
    }

    public static function get(): ?Organization
    {
        return self::$current;
    }

    public static function organization(): ?Organization
    {
        return self::$current;
    }

    public static function id(): ?int
    {
        return self::$current?->id;
    }

    public static function check(): bool
    {
        return self::$current instanceof Organization;
    }

    public static function forget(): void
    {
        self::$current = null;
        if (self::hasSession()) {
            session()->forget('current_organization_id');
        }
    }

    public static function flush(): void
    {
        self::$current = null;
    }

    public static function initFromSession(): void
    {
        if (! self::hasSession()) {
            return;
        }

        $organizationId = session('current_organization_id');

        if ($organizationId && ! self::$current && auth()->check()) {
            $user = auth()->user();
            if (! $user instanceof User) {
                return;
            }

            if (! $user->belongsToOrganization((int) $organizationId)) {
                session()->forget('current_organization_id');

                return;
            }

            $organization = Organization::query()->find($organizationId);
            self::$current = $organization;
        }
    }

    public static function initForUser(User $user): void
    {
        if (self::hasSession()) {
            $sessionOrgId = session('current_organization_id');
            if (is_numeric($sessionOrgId) && $user->belongsToOrganization((int) $sessionOrgId)) {
                $organization = Organization::query()->find($sessionOrgId);
                if ($organization instanceof Organization) {
                    self::$current = $organization;

                    return;
                }
            }
        }

        $defaultOrg = $user->defaultOrganization();
        if ($defaultOrg instanceof Organization) {
            self::set($defaultOrg);
        }
    }

    private static function hasSession(): bool
    {
        try {
            return app()->bound('session') && session()->isStarted();
        } catch (Throwable) {
            return false;
        }
    }
}
