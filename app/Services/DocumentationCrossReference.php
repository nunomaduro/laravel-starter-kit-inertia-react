<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use ReflectionException;

final readonly class DocumentationCrossReference
{
    /**
     * Discover all relationships for Actions.
     *
     * @param  array<string, mixed>  $actions
     * @return array<string, array<string, mixed>>
     */
    public function discoverActionRelationships(array $actions): array
    {
        $relationships = [];

        foreach ($actions as $actionName => $actionInfo) {
            $relationships[$actionName] = [
                'usedBy' => $this->findControllersUsingAction($actionName),
                'usesModels' => $this->extractModelsFromAction($actionInfo),
                'relatedRoutes' => $this->findRoutesForAction($actionName),
            ];
        }

        return $relationships;
    }

    /**
     * Discover all relationships for Controllers.
     *
     * @param  array<string, mixed>  $controllers
     * @return array<string, array<string, mixed>>
     */
    public function discoverControllerRelationships(array $controllers): array
    {
        $relationships = [];

        foreach ($controllers as $controllerName => $controllerInfo) {
            $relationships[$controllerName] = [
                'usesActions' => $this->extractActionsFromController($controllerInfo),
                'usesFormRequests' => $this->extractFormRequestsFromController($controllerInfo),
                'relatedRoutes' => $this->findRoutesForController($controllerName),
                'rendersPages' => $this->extractPagesFromController($controllerInfo),
            ];
        }

        return $relationships;
    }

    /**
     * Discover all relationships for Pages.
     *
     * @param  array<string, mixed>  $pages
     * @return array<string, array<string, mixed>>
     */
    public function discoverPageRelationships(array $pages): array
    {
        $relationships = [];

        foreach (array_keys($pages) as $pagePath) {
            $relationships[$pagePath] = [
                'renderedBy' => $this->findControllersRenderingPage($pagePath),
                'relatedRoutes' => $this->findRoutesForPage($pagePath),
            ];
        }

        return $relationships;
    }

    /**
     * Find controllers that use a specific Action.
     *
     * @return array<string>
     */
    private function findControllersUsingAction(string $actionName): array
    {
        $controllers = [];
        $controllersPath = app_path('Http/Controllers');

        if (! File::isDirectory($controllersPath)) {
            return $controllers;
        }

        $files = File::files($controllersPath);

        foreach ($files as $file) {
            $className = $this->getClassNameFromFile($file->getPathname());

            if ($className === null) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($className);
                $content = File::get($file->getPathname());

                // Check if Action is used in controller
                if (str_contains($content, $actionName)) {
                    $shortName = $file->getFilenameWithoutExtension();
                    $controllers[] = $shortName;
                }
            } catch (ReflectionException) {
                // Skip if reflection fails
            }
        }

        return array_unique($controllers);
    }

    /**
     * Extract Model classes from Action.
     *
     * @param  array<string, mixed>  $actionInfo
     * @return array<string>
     */
    private function extractModelsFromAction(array $actionInfo): array
    {
        $models = [];

        if (isset($actionInfo['handleMethod']['parameters'])) {
            foreach ($actionInfo['handleMethod']['parameters'] as $param) {
                $type = $param['type'] ?? null;
                if ($type !== null && str_contains((string) $type, '\\Models\\')) {
                    $modelName = class_basename($type);
                    $models[] = $modelName;
                }
            }
        }

        // Also check file content for Model usage
        if (isset($actionInfo['filePath'])) {
            $content = File::get($actionInfo['filePath']);
            if (preg_match_all('/use\s+App\\\Models\\\\(\w+);/', $content, $matches)) {
                $models = array_merge($models, $matches[1]);
            }
        }

        return array_unique($models);
    }

    /**
     * Find routes that use a specific Action.
     *
     * @return array<string>
     */
    private function findRoutesForAction(string $actionName): array
    {
        $routes = [];
        $allRoutes = Route::getRoutes();

        foreach ($allRoutes as $route) {
            $action = $route->getAction();

            if (isset($action['controller'])) {
                $controller = $action['controller'];
                $controllerClass = explode('@', $controller)[0] ?? null;

                if ($controllerClass !== null) {
                    try {
                        $reflection = new ReflectionClass($controllerClass);
                        $content = File::get($reflection->getFileName());

                        // Check if Action is used in controller method
                        if (str_contains($content, $actionName)) {
                            $routeName = $route->getName();
                            if ($routeName !== null) {
                                $routes[] = $routeName;
                            }
                        }
                    } catch (ReflectionException) {
                        // Skip if reflection fails
                    }
                }
            }
        }

        return array_unique($routes);
    }

    /**
     * Extract Action classes from Controller.
     *
     * @param  array<string, mixed>  $controllerInfo
     * @return array<string>
     */
    private function extractActionsFromController(array $controllerInfo): array
    {
        $actions = [];

        if (isset($controllerInfo['methods'])) {
            foreach ($controllerInfo['methods'] as $method) {
                if (isset($method['actionsUsed'])) {
                    $actions = array_merge($actions, $method['actionsUsed']);
                }
            }
        }

        // Also check file content
        if (isset($controllerInfo['filePath'])) {
            $content = File::get($controllerInfo['filePath']);
            if (preg_match_all('/use\s+App\\\Actions\\\\(\w+);/', $content, $matches)) {
                $actions = array_merge($actions, $matches[1]);
            }
        }

        return array_unique($actions);
    }

    /**
     * Extract Form Request classes from Controller.
     *
     * @param  array<string, mixed>  $controllerInfo
     * @return array<string>
     */
    private function extractFormRequestsFromController(array $controllerInfo): array
    {
        $requests = [];

        if (isset($controllerInfo['methods'])) {
            foreach ($controllerInfo['methods'] as $method) {
                if (isset($method['formRequestsUsed'])) {
                    $requests = array_merge($requests, $method['formRequestsUsed']);
                }
            }
        }

        // Also check file content
        if (isset($controllerInfo['filePath'])) {
            $content = File::get($controllerInfo['filePath']);
            if (preg_match_all('/use\s+App\\\Http\\\Requests\\\\(\w+Request);/', $content, $matches)) {
                $requests = array_merge($requests, $matches[1]);
            }
        }

        return array_unique($requests);
    }

    /**
     * Find routes for a specific Controller.
     *
     * @return array<string>
     */
    private function findRoutesForController(string $controllerName): array
    {
        $routes = [];
        $allRoutes = Route::getRoutes();

        $fullControllerName = 'App\Http\Controllers\\'.$controllerName;

        foreach ($allRoutes as $route) {
            $action = $route->getAction();

            if (isset($action['controller'])) {
                $controller = $action['controller'];
                $controllerClass = explode('@', $controller)[0] ?? null;

                if ($controllerClass === $fullControllerName) {
                    $routeName = $route->getName();
                    if ($routeName !== null) {
                        $routes[] = $routeName;
                    }
                }
            }
        }

        return array_unique($routes);
    }

    /**
     * Extract pages rendered by Controller.
     *
     * @param  array<string, mixed>  $controllerInfo
     * @return array<string>
     */
    private function extractPagesFromController(array $controllerInfo): array
    {
        $pages = [];

        if (isset($controllerInfo['filePath'])) {
            $content = File::get($controllerInfo['filePath']);

            // Match Inertia::render('page/path')
            if (preg_match_all("/Inertia::render\(['\"]([^'\"]+)['\"]/", $content, $matches)) {
                $pages = array_unique($matches[1]);
            }
        }

        return $pages;
    }

    /**
     * Find controllers that render a specific page.
     *
     * @return array<string>
     */
    private function findControllersRenderingPage(string $pagePath): array
    {
        $controllers = [];
        $controllersPath = app_path('Http/Controllers');

        if (! File::isDirectory($controllersPath)) {
            return $controllers;
        }

        $files = File::files($controllersPath);

        foreach ($files as $file) {
            $content = File::get($file->getPathname());

            // Check if page is rendered in controller
            $escapedPagePath = preg_quote($pagePath, '/');
            if (preg_match(sprintf("/Inertia::render\\(['\"]%s['\"]/", $escapedPagePath), $content)) {
                $controllers[] = $file->getFilenameWithoutExtension();
            }
        }

        return array_unique($controllers);
    }

    /**
     * Find routes for a specific page.
     *
     * @return array<string>
     */
    private function findRoutesForPage(string $pagePath): array
    {
        $routes = [];
        $allRoutes = Route::getRoutes();

        foreach ($allRoutes as $route) {
            $action = $route->getAction();

            if (isset($action['controller'])) {
                $controller = $action['controller'];
                $controllerClass = explode('@', $controller)[0] ?? null;

                if ($controllerClass !== null) {
                    try {
                        $reflection = new ReflectionClass($controllerClass);
                        $content = File::get($reflection->getFileName());

                        // Check if page is rendered in controller
                        $escapedPagePath = preg_quote($pagePath, '/');
                        if (preg_match(sprintf("/Inertia::render\\(['\"]%s['\"]/", $escapedPagePath), $content)) {
                            $routeName = $route->getName();
                            if ($routeName !== null) {
                                $routes[] = $routeName;
                            }
                        }
                    } catch (ReflectionException) {
                        // Skip if reflection fails
                    }
                }
            }

            // Also check inline closures
            if (isset($action['uses']) && is_string($action['uses'])) {
                // This is a closure route, check routes file
                $routesFile = base_path('routes/web.php');
                if (File::exists($routesFile)) {
                    $routesContent = File::get($routesFile);
                    $escapedPagePath = preg_quote($pagePath, '/');
                    if (preg_match(sprintf("/Inertia::render\\(['\"]%s['\"]/", $escapedPagePath), $routesContent)) {
                        $routeName = $route->getName();
                        if ($routeName !== null) {
                            $routes[] = $routeName;
                        }
                    }
                }
            }
        }

        return array_unique($routes);
    }

    /**
     * Get class name from file path.
     */
    private function getClassNameFromFile(string $filePath): ?string
    {
        if (! File::exists($filePath)) {
            return null;
        }

        $content = File::get($filePath);

        // Extract namespace
        if (! preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch)) {
            return null;
        }

        $namespace = $namespaceMatch[1];

        // Extract class name
        if (! preg_match('/\b(?:final\s+)?(?:readonly\s+)?class\s+(\w+)/', $content, $classMatch)) {
            return null;
        }

        $className = $classMatch[1];

        return sprintf('%s\%s', $namespace, $className);
    }
}
