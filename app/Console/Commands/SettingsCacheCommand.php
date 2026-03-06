<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Organization;
use App\Providers\SettingsOverlayServiceProvider;
use App\Services\OrganizationSettingsService;
use Illuminate\Console\Command;
use Override;

final class SettingsCacheCommand extends Command
{
    #[Override]
    protected $signature = 'settings:cache';

    #[Override]
    protected $description = 'Warm the organization settings cache for all organizations';

    public function handle(OrganizationSettingsService $service): int
    {
        $orgOverridableKeys = SettingsOverlayServiceProvider::orgOverridableKeys();

        if ($orgOverridableKeys === []) {
            $this->components->info('No org-overridable settings defined.');

            return self::SUCCESS;
        }

        $count = 0;

        Organization::query()
            ->select('id')
            ->chunk(100, function ($organizations) use ($service, &$count): void {
                foreach ($organizations as $organization) {
                    $service->clearCache($organization);
                    $service->getOverridesForOrganization($organization);
                    $count++;
                }
            });

        $this->components->info(sprintf('Cached settings for %d organization(s).', $count));

        return self::SUCCESS;
    }
}
