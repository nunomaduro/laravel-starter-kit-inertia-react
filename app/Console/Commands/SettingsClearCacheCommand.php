<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Organization;
use App\Services\OrganizationSettingsService;
use Illuminate\Console\Command;

final class SettingsClearCacheCommand extends Command
{
    protected $signature = 'settings:clear-cache
                            {--org= : Clear cache for a specific organization ID only}';

    protected $description = 'Clear the organization settings cache';

    public function handle(OrganizationSettingsService $service): int
    {
        $orgId = $this->option('org');

        if ($orgId !== null) {
            $organization = Organization::query()->find((int) $orgId);

            if (! $organization instanceof Organization) {
                $this->components->error(sprintf('Organization #%s not found.', $orgId));

                return self::FAILURE;
            }

            $service->clearCache($organization);
            $this->components->info(sprintf('Cleared settings cache for organization #%s.', $orgId));

            return self::SUCCESS;
        }

        $count = 0;

        Organization::query()
            ->select('id')
            ->chunk(100, function ($organizations) use ($service, &$count): void {
                foreach ($organizations as $organization) {
                    $service->clearCache($organization);
                    $count++;
                }
            });

        $this->components->info(sprintf('Cleared settings cache for %d organization(s).', $count));

        return self::SUCCESS;
    }
}
