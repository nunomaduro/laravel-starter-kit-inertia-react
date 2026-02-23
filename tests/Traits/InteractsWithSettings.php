<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\Organization;
use App\Services\OrganizationSettingsService;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Settings;

trait InteractsWithSettings
{
    /**
     * Override a Spatie settings property in the database.
     *
     * @param  class-string<Settings>  $settingsClass
     */
    protected function fakeSettings(string $settingsClass, array $overrides): void
    {
        $group = $settingsClass::group();

        foreach ($overrides as $name => $value) {
            DB::table('settings')
                ->where('group', $group)
                ->where('name', $name)
                ->update(['payload' => json_encode($value, JSON_THROW_ON_ERROR)]);
        }

        // Clear Spatie's resolved instance so the next resolve picks up new values
        app()->forgetInstance($settingsClass);
    }

    /**
     * Set an organization-level settings override.
     */
    protected function setOrgOverride(
        Organization $organization,
        string $group,
        string $name,
        mixed $value,
        bool $encrypt = false,
    ): void {
        resolve(OrganizationSettingsService::class)->setOverride(
            $organization,
            $group,
            $name,
            $value,
            $encrypt,
        );
    }

    /**
     * Remove all organization-level settings overrides.
     */
    protected function clearOrgOverrides(Organization $organization): void
    {
        DB::table('organization_settings')
            ->where('organization_id', $organization->id)
            ->delete();

        resolve(OrganizationSettingsService::class)->clearCache($organization);
    }
}
