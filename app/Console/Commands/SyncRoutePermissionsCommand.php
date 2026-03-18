<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

final class SyncRoutePermissionsCommand extends Command
{
    protected $signature = 'permission:sync-routes
                            {--dry-run : List permissions that would be created or removed without making changes}
                            {--prune : Remove permissions that no longer match any route}
                            {--silent : Suppress output}';

    protected $description = 'Create or update permissions from named application routes';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $silent = $this->option('silent');

        if (! $silent) {
            $this->info('Syncing permissions from routes...');
        }

        $routePermissions = $this->collectRoutePermissions();

        if (! $silent) {
            $this->line(sprintf('Found %d route(s) to sync.', count($routePermissions)));
        }

        if ($routePermissions === []) {
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->displayDryRun($routePermissions);

            if ($this->option('prune')) {
                $orphans = $this->getOrphanedPermissionNames($routePermissions);
                if ($orphans !== [] && ! $silent) {
                    $this->warn('Permissions that would be pruned: '.implode(', ', $orphans));
                }
            }

            return self::SUCCESS;
        }

        $guard = config('permission.default_guard_name', 'web');
        $created = 0;

        foreach ($routePermissions as $routeName) {
            $permission = Permission::query()->firstOrCreate(
                ['name' => $routeName],
                ['guard_name' => $guard]
            );
            if ($permission->wasRecentlyCreated) {
                $created++;
            }
        }

        resolve(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($this->option('prune')) {
            $pruned = $this->pruneOrphanedPermissions($routePermissions, $silent);
            if (! $silent && $pruned > 0) {
                $this->line(sprintf('Pruned %d orphaned permission(s).', $pruned));
            }
        }

        if (! $silent) {
            $this->info('Done.');
            $this->line(sprintf('Created %d new permission(s).', $created));
        }

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function collectRoutePermissions(): array
    {
        $permissions = [];
        $skipPatterns = config('permission.route_skip_patterns', []);

        foreach (RouteFacade::getRoutes() as $route) {
            $name = $route->getName();
            if (! is_string($name)) {
                continue;
            }

            if ($name === '') {
                continue;
            }

            if (! $this->isApplicationRoute($route)) {
                continue;
            }

            if ($this->matchesSkipPatterns($name, $skipPatterns)) {
                continue;
            }

            $permissions[$name] = true;
        }

        return array_keys($permissions);
    }

    private function isApplicationRoute(Route $route): bool
    {
        $action = $route->getAction();

        if (isset($action['controller']) && is_string($action['controller'])) {
            return str_starts_with($action['controller'], 'App\\');
        }

        if (isset($action['uses']) && $action['uses'] instanceof Closure) {
            return true;
        }

        if (isset($action['file'])) {
            $file = (string) $action['file'];

            return str_contains($file, base_path('routes'));
        }

        return false;
    }

    /**
     * @param  array<string>  $skipPatterns
     */
    private function matchesSkipPatterns(string $routeName, array $skipPatterns): bool
    {
        foreach ($skipPatterns as $pattern) {
            if (str_contains($pattern, '*')) {
                $regex = '/^'.str_replace('\*', '.*', preg_quote($pattern, '/')).'$/';
                if (preg_match($regex, $routeName) === 1) {
                    return true;
                }
            } elseif ($routeName === $pattern) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $currentRouteNames
     * @return list<string>
     */
    private function getOrphanedPermissionNames(array $currentRouteNames): array
    {
        $set = array_flip($currentRouteNames);
        $orphans = [];

        foreach (Permission::query()->pluck('name') as $name) {
            if (! isset($set[$name]) && $this->looksLikeRoutePermission($name)) {
                $orphans[] = $name;
            }
        }

        return $orphans;
    }

    private function looksLikeRoutePermission(string $name): bool
    {
        return str_contains($name, '.') || str_starts_with($name, 'api.');
    }

    /**
     * @param  list<string>  $currentRouteNames
     */
    private function pruneOrphanedPermissions(array $currentRouteNames, bool $silent): int
    {
        $orphans = $this->getOrphanedPermissionNames($currentRouteNames);
        $count = 0;

        foreach ($orphans as $name) {
            Permission::query()->where('name', $name)->delete();
            $count++;
            if (! $silent) {
                $this->warn('Pruned: '.$name);
            }
        }

        if ($count > 0) {
            resolve(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        return $count;
    }

    /**
     * @param  list<string>  $routePermissions
     */
    private function displayDryRun(array $routePermissions): void
    {
        if ($this->option('silent')) {
            return;
        }

        $this->warn('Dry run — no changes made.');
        $this->table(['Permission (route name)'], array_map(fn (string $p): array => [$p], $routePermissions));
    }
}
