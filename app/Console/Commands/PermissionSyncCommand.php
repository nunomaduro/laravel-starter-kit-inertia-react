<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Organization;
use App\Services\Organization\OrganizationRoleService;
use Illuminate\Console\Command;
use Override;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'permission:sync')]
final class PermissionSyncCommand extends Command
{
    #[Override]
    protected $signature = 'permission:sync
                            {--dry-run : Preview changes without applying}
                            {--org-only : Sync only organization permissions}
                            {--silent : Suppress output}';

    #[Override]
    protected $description = 'Sync org permissions from organization-permissions.json and assign to org roles';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN - No changes will be made');
        }

        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $config = $this->loadOrganizationPermissions();
        $targetNames = $this->collectPermissionNames($config);
        $current = Permission::query()->where('name', 'like', 'org.%')->pluck('name')->all();
        $toCreate = array_diff($targetNames, $current);

        $silent = (bool) $this->option('silent');

        foreach ($toCreate as $name) {
            if (! $silent) {
                $this->line('+ Creating: '.$name);
            }

            if (! $dryRun) {
                Permission::create(['name' => $name, 'guard_name' => 'web']);
            }
        }

        $roleService = resolve(OrganizationRoleService::class);

        foreach (Organization::query()->get() as $org) {
            if (! $silent) {
                $this->line('Syncing permissions for org: '.$org->name);
            }

            if (! $dryRun) {
                $roleService->syncRolePermissions($org);
            }
        }

        if (! $silent) {
            $this->info('Permission sync completed.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function loadOrganizationPermissions(): array
    {
        $path = database_path('seeders/data/organization-permissions.json');
        throw_unless(file_exists($path), RuntimeException::class, 'organization-permissions.json not found');

        $content = file_get_contents($path);
        throw_if($content === false, RuntimeException::class, 'Failed to read organization-permissions.json');

        return json_decode($content, true) ?? [];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string>
     */
    private function collectPermissionNames(array $config): array
    {
        $names = [];
        foreach ($config['organization_permissions'] ?? [] as $category) {
            foreach ($category['permissions'] ?? [] as $perm) {
                $name = $perm['name'] ?? null;
                if ($name) {
                    $names[] = $name;
                }
            }
        }

        return array_values(array_unique($names));
    }
}
