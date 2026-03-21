<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Route;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * AI tool that lists application routes.
 *
 * Read-only — does not modify any files or execute any code.
 */
final class ListRoutesAiTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'List application routes with their methods, URIs, names, and controllers';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'filter' => $schema->string()->description('Filter routes by URI prefix or name (e.g., "api", "hr", "billing")'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $filter = $request->get('filter');
        $routes = collect(Route::getRoutes()->getRoutes());

        if ($filter) {
            $routes = $routes->filter(fn ($route): bool => str_contains($route->uri(), $filter)
                || str_contains($route->getName() ?? '', $filter));
        }

        $routes = $routes->take(50);

        if ($routes->isEmpty()) {
            return 'No routes found matching the filter.';
        }

        $output = "# Routes\n\n";
        $output .= "| Method | URI | Name | Controller |\n";
        $output .= "|--------|-----|------|------------|\n";

        foreach ($routes as $route) {
            $methods = implode('|', $route->methods());
            $uri = $route->uri();
            $name = $route->getName() ?? '-';
            $action = $route->getActionName();
            $action = str_replace('App\\Http\\Controllers\\', '', $action);

            $output .= "| {$methods} | /{$uri} | {$name} | {$action} |\n";
        }

        return $output;
    }
}
