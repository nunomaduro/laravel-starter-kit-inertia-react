<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use ReflectionException;
use ReflectionType;

final class GenerateApiDocumentation extends Command
{
    protected $signature = 'docs:api
                            {--format=markdown : Output format (markdown, openapi)}';

    protected $description = 'Generate API documentation from routes and controllers';

    public function handle(): int
    {
        $this->info('Generating API documentation...');

        $routes = $this->collectRoutes();
        $format = $this->option('format');

        if ($format === 'openapi') {
            $content = $this->generateOpenApi($routes);
            $outputPath = base_path('docs/developer/api-reference/openapi.json');
        } else {
            $content = $this->generateMarkdown($routes);
            $outputPath = base_path('docs/developer/api-reference/routes.md');
        }

        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $content);

        $this->info('API documentation generated: '.$outputPath);

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function collectRoutes(): array
    {
        $routes = [];
        $allRoutes = Route::getRoutes();

        foreach ($allRoutes as $route) {
            $action = $route->getAction();
            $uri = $route->uri();
            $methods = $route->methods();
            $name = $route->getName();
            $middleware = $route->gatherMiddleware();

            $routeInfo = [
                'uri' => $uri,
                'methods' => array_diff($methods, ['HEAD', 'OPTIONS']),
                'name' => $name,
                'middleware' => $middleware,
                'controller' => null,
                'method' => null,
                'parameters' => [],
            ];

            if (isset($action['controller'])) {
                $controller = $action['controller'];
                if (str_contains($controller, '@')) {
                    [$controllerClass, $methodName] = explode('@', $controller);
                    $routeInfo['controller'] = $controllerClass;
                    $routeInfo['method'] = $methodName;
                    $routeInfo['parameters'] = $this->extractRouteParameters($controllerClass, $methodName);
                }
            }

            $routeInfo['uriParameters'] = $this->extractUriParameters($uri);

            $routes[] = $routeInfo;
        }

        $grouped = [];
        foreach ($routes as $route) {
            $controller = $route['controller'] ?? 'Closure';
            $grouped[$controller][] = $route;
        }

        return $grouped;
    }

    /**
     * @param  array<string, array<string, mixed>>  $routes
     */
    private function generateMarkdown(array $routes): string
    {
        $content = "# API Reference\n\n";
        $content .= "This document lists all available routes in the application.\n\n";
        $content .= '**Last Updated**: '.now()->format('Y-m-d H:i:s')."\n\n";

        foreach ($routes as $controller => $controllerRoutes) {
            $controllerName = class_basename($controller);
            $content .= "## {$controllerName}\n\n";

            if ($controller !== 'Closure') {
                $content .= "**Controller**: `{$controller}`\n\n";
            }

            $content .= "| Method | URI | Route Name | Middleware |\n";
            $content .= "|--------|-----|------------|------------|\n";

            foreach ($controllerRoutes as $route) {
                $methods = implode(', ', $route['methods']);
                $uri = $route['uri'];
                $name = $route['name'] ?? '-';
                $middleware = empty($route['middleware']) ? '-' : implode(', ', array_slice($route['middleware'], 0, 3));

                $content .= "| {$methods} | `{$uri}` | {$name} | {$middleware} |\n";
            }

            $content .= "\n";

            // Add method details
            foreach ($controllerRoutes as $route) {
                if ($route['method'] !== null) {
                    $content .= "### {$route['method']}\n\n";
                    $content .= "**Route**: `{$route['name']}`\n\n";
                    $content .= "**URI**: `{$route['uri']}`\n\n";
                    $content .= '**Methods**: '.implode(', ', $route['methods'])."\n\n";

                    if (! empty($route['uriParameters'])) {
                        $content .= "**Parameters**:\n";
                        foreach ($route['uriParameters'] as $param) {
                            $content .= "- `{$param}`\n";
                        }

                        $content .= "\n";
                    }

                    if (! empty($route['middleware'])) {
                        $content .= '**Middleware**: '.implode(', ', $route['middleware'])."\n\n";
                    }

                    if (! empty($route['parameters'])) {
                        $content .= "**Method Parameters**:\n";
                        foreach ($route['parameters'] as $param) {
                            $content .= "- `{$param['name']}`: `{$param['type']}`\n";
                        }

                        $content .= "\n";
                    }
                }
            }

            $content .= "\n";
        }

        return $content;
    }

    /**
     * @param  array<string, array<string, mixed>>  $routes
     */
    private function generateOpenApi(array $routes): string
    {
        $openApi = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => config('app.name').' API',
                'version' => '1.0.0',
                'description' => 'API documentation generated automatically',
            ],
            'paths' => [],
        ];

        foreach ($routes as $controllerRoutes) {
            foreach ($controllerRoutes as $route) {
                $path = '/'.$route['uri'];
                $path = preg_replace('/\{(\w+)\}/', '{$1}', $path);

                if (! isset($openApi['paths'][$path])) {
                    $openApi['paths'][$path] = [];
                }

                foreach ($route['methods'] as $method) {
                    $methodLower = mb_strtolower((string) $method);

                    $openApi['paths'][$path][$methodLower] = [
                        'summary' => $route['name'] ?? $path,
                        'operationId' => $route['name'] ?? str_replace(['/', '{', '}'], ['_', '', ''], $path),
                        'tags' => $route['controller'] ? [class_basename($route['controller'])] : ['Routes'],
                        'parameters' => $this->generateOpenApiParameters($route),
                    ];
                }
            }
        }

        return json_encode($openApi, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param  array<string, mixed>  $route
     * @return array<string, mixed>
     */
    private function generateOpenApiParameters(array $route): array
    {
        $parameters = [];

        foreach ($route['uriParameters'] as $param) {
            $parameters[] = [
                'name' => $param,
                'in' => 'path',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                ],
            ];
        }

        return $parameters;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractRouteParameters(string $controllerClass, string $methodName): array
    {
        try {
            $reflection = new ReflectionClass($controllerClass);
            if (! $reflection->hasMethod($methodName)) {
                return [];
            }

            $method = $reflection->getMethod($methodName);
            $parameters = [];

            foreach ($method->getParameters() as $param) {
                $type = $param->getType() instanceof ReflectionType ? (string) $param->getType() : 'mixed';

                $parameters[] = [
                    'name' => $param->getName(),
                    'type' => $type,
                    'required' => ! $param->isOptional(),
                ];
            }

            return $parameters;
        } catch (ReflectionException) {
            return [];
        }
    }

    /**
     * @return array<string>
     */
    private function extractUriParameters(string $uri): array
    {
        if (preg_match_all('/\{(\w+)\}/', $uri, $matches)) {
            return $matches[1];
        }

        return [];
    }
}
