<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;

final class CheckRouteNamesCommand extends Command
{
    protected $signature = 'permission:check-routes
                            {--strict : Fail when unnamed app routes exist (default: use config permission.require_named_routes)}';

    protected $description = 'Ensure all application routes have names (for route-based permissions and CI)';

    public function handle(): int
    {
        $unnamed = $this->collectUnnamedApplicationRoutes();

        if ($unnamed === []) {
            $this->info('All application routes have names.');

            return self::SUCCESS;
        }

        $this->warn('Application routes without a name:');
        $this->table(
            ['Method', 'URI', 'Action'],
            array_map(
                fn (Route $r): array => [
                    implode('|', $r->methods()),
                    $r->uri(),
                    $this->actionSummary($r),
                ],
                $unnamed
            )
        );

        $fail = $this->option('strict') || config('permission.require_named_routes', true);
        if ($fail) {
            $this->error(sprintf("Found %d unnamed route(s). Add ->name('...') to each.", count($unnamed)));

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @return list<Route>
     */
    private function collectUnnamedApplicationRoutes(): array
    {
        $unnamed = [];
        foreach (RouteFacade::getRoutes() as $route) {
            if (! $this->isApplicationRoute($route)) {
                continue;
            }

            $name = $route->getName();
            if (is_string($name) && $name !== '') {
                continue;
            }

            $unnamed[] = $route;
        }

        return $unnamed;
    }

    private function isApplicationRoute(Route $route): bool
    {
        $action = $route->getAction();
        $routesPath = str_replace('\\', '/', realpath(base_path('routes')) ?: base_path('routes'));

        if (isset($action['controller']) && is_string($action['controller'])) {
            return str_starts_with($action['controller'], 'App\\');
        }

        if (isset($action['uses']) && $action['uses'] instanceof Closure) {
            if (isset($action['file']) && is_string($action['file'])) {
                $resolved = str_replace('\\', '/', realpath($action['file']) ?: $action['file']);

                return str_starts_with($resolved, $routesPath);
            }

            return false;
        }

        if (isset($action['file']) && is_string($action['file'])) {
            $resolved = str_replace('\\', '/', realpath($action['file']) ?: $action['file']);

            return str_starts_with($resolved, $routesPath);
        }

        return false;
    }

    private function actionSummary(Route $route): string
    {
        $action = $route->getActionName();
        if ($action === 'Closure') {
            return 'Closure';
        }

        if (is_string($action) && str_contains($action, '@')) {
            return $action;
        }

        return $action ?? '—';
    }
}
