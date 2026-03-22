<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DocumentationCrossReference;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

final class SyncDocumentationManifest extends Command
{
    protected $signature = 'docs:sync
                            {--check : Only check for undocumented items, do not update manifest}
                            {--generate : Generate documentation stubs for undocumented items}';

    protected $description = 'Sync documentation manifest with actual codebase';

    public function __construct(
        private readonly DocumentationCrossReference $crossReference
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $manifestPath = base_path('docs/.manifest.json');

        if (! File::exists($manifestPath)) {
            $this->error('Manifest file not found at docs/.manifest.json');

            return self::FAILURE;
        }

        $manifest = json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);

        $this->info('Scanning codebase...');

        $actions = $this->scanActions();
        $controllers = $this->scanControllers();
        $pages = $this->scanPages();

        $this->info('Discovering relationships...');
        $actionRelationships = $this->crossReference->discoverActionRelationships($actions);
        $controllerRelationships = $this->crossReference->discoverControllerRelationships($controllers);
        $pageRelationships = $this->crossReference->discoverPageRelationships($pages);

        $undocumented = [
            'actions' => [],
            'controllers' => [],
            'pages' => [],
        ];

        foreach (array_keys($actions) as $actionName) {
            if (! isset($manifest['actions'][$actionName])) {
                $manifest['actions'][$actionName] = [
                    'documented' => false,
                    'path' => null,
                    'relationships' => [
                        'usedBy' => [],
                        'usesModels' => [],
                        'relatedRoutes' => [],
                    ],
                ];
            }

            if (! ($manifest['actions'][$actionName]['documented'] ?? false)) {
                $undocumented['actions'][] = $actionName;
            }

            if (isset($actionRelationships[$actionName])) {
                $manifest['actions'][$actionName]['relationships'] = $actionRelationships[$actionName];
            } elseif (! isset($manifest['actions'][$actionName]['relationships'])) {
                $manifest['actions'][$actionName]['relationships'] = [
                    'usedBy' => [],
                    'usesModels' => [],
                    'relatedRoutes' => [],
                ];
            }
        }

        foreach (array_keys($controllers) as $controllerName) {
            if (! isset($manifest['controllers'][$controllerName])) {
                $manifest['controllers'][$controllerName] = [
                    'documented' => false,
                    'path' => null,
                    'relationships' => [
                        'usesActions' => [],
                        'usesFormRequests' => [],
                        'relatedRoutes' => [],
                        'rendersPages' => [],
                    ],
                ];
            }

            if (! ($manifest['controllers'][$controllerName]['documented'] ?? false)) {
                $undocumented['controllers'][] = $controllerName;
            }

            if (isset($controllerRelationships[$controllerName])) {
                $manifest['controllers'][$controllerName]['relationships'] = $controllerRelationships[$controllerName];
            } elseif (! isset($manifest['controllers'][$controllerName]['relationships'])) {
                $manifest['controllers'][$controllerName]['relationships'] = [
                    'usesActions' => [],
                    'usesFormRequests' => [],
                    'relatedRoutes' => [],
                    'rendersPages' => [],
                ];
            }
        }

        foreach (array_keys($pages) as $pagePath) {
            if (! isset($manifest['pages'][$pagePath])) {
                $manifest['pages'][$pagePath] = [
                    'documented' => false,
                    'userGuide' => null,
                    'developerGuide' => null,
                    'relationships' => [
                        'renderedBy' => [],
                        'relatedRoutes' => [],
                    ],
                ];
            }

            if (! ($manifest['pages'][$pagePath]['documented'] ?? false)) {
                $undocumented['pages'][] = $pagePath;
            }

            if (isset($pageRelationships[$pagePath])) {
                $manifest['pages'][$pagePath]['relationships'] = $pageRelationships[$pagePath];
            } elseif (! isset($manifest['pages'][$pagePath]['relationships'])) {
                $manifest['pages'][$pagePath]['relationships'] = [
                    'renderedBy' => [],
                    'relatedRoutes' => [],
                ];
            }
        }

        $manifest['lastGenerated'] = now()->format('Y-m-d');

        if ($this->option('check')) {
            $this->displayUndocumented($undocumented);

            return empty($undocumented['actions']) && empty($undocumented['controllers']) && empty($undocumented['pages'])
                ? self::SUCCESS
                : self::FAILURE;
        }

        File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        $this->info('Manifest synced successfully!');

        $this->updateIndexFiles($manifest);

        if ($undocumented['actions'] !== [] || $undocumented['controllers'] !== [] || $undocumented['pages'] !== []) {
            $this->displayUndocumented($undocumented);

            if ($this->option('generate')) {
                $this->generateStubs($undocumented);
                $this->markStubsAsDocumented($undocumented, $manifest, $manifestPath);
            } else {
                $this->warn('Run with --generate to create documentation stubs for undocumented items.');
            }
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function scanActions(): array
    {
        $actionsPath = app_path('Actions');
        $actions = [];

        if (! File::isDirectory($actionsPath)) {
            return $actions;
        }

        foreach (File::files($actionsPath) as $file) {
            $filePath = $file->getPathname();
            $className = $this->getClassNameFromFile($filePath);

            if ($className === null) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($className);
                $shortClassName = class_basename($className);

                $actionInfo = [
                    'name' => $shortClassName,
                    'fullName' => $className,
                    'filePath' => $filePath,
                    'handleMethod' => $reflection->hasMethod('handle')
                        ? $this->extractMethodInfo($reflection->getMethod('handle'))
                        : null,
                    'dependencies' => [],
                ];

                if ($reflection->hasMethod('__construct')) {
                    foreach ($reflection->getMethod('__construct')->getParameters() as $param) {
                        $type = $this->getParameterType($param);
                        if ($type !== null) {
                            $actionInfo['dependencies'][] = [
                                'name' => $param->getName(),
                                'type' => $type,
                            ];
                        }
                    }
                }

                $actions[$shortClassName] = $actionInfo;
            } catch (ReflectionException) {
                $actions[class_basename($className)] = ['name' => class_basename($className), 'filePath' => $filePath];
            }
        }

        return $actions;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function scanControllers(): array
    {
        $controllersPath = app_path('Http/Controllers');
        $controllers = [];

        if (! File::isDirectory($controllersPath)) {
            return $controllers;
        }

        foreach (File::files($controllersPath) as $file) {
            $className = $file->getFilenameWithoutExtension();
            $filePath = $file->getPathname();
            $fullClassName = $this->getClassNameFromFile($filePath);

            if ($fullClassName === null) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($fullClassName);
                if ($reflection->isAbstract()) {
                    continue;
                }

                $methods = [];

                foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                    if ($method->isConstructor()) {
                        continue;
                    }

                    $methods[$method->getName()] = $this->extractMethodInfo($method);

                    $methodBody = $this->getMethodBody($filePath, $method->getName());
                    $methods[$method->getName()]['actionsUsed'] = $this->extractActionClasses($methodBody);
                    $methods[$method->getName()]['formRequestsUsed'] = $this->extractFormRequestClasses($methodBody);
                }

                $controllers[$className] = [
                    'name' => $className,
                    'fullName' => $fullClassName,
                    'filePath' => $filePath,
                    'methods' => $methods,
                ];
            } catch (ReflectionException) {
                $controllers[$className] = ['name' => $className, 'filePath' => $filePath];
            }
        }

        return $controllers;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function scanPages(): array
    {
        $pagesPath = resource_path('js/pages');
        $pages = [];

        if (! File::isDirectory($pagesPath)) {
            return $pages;
        }

        foreach (File::allFiles($pagesPath) as $file) {
            if ($file->getExtension() !== 'tsx') {
                continue;
            }

            $relativePath = str_replace($pagesPath.'/', '', $file->getPathname());

            if (str_contains($relativePath, '/_components/')) {
                continue;
            }

            $pagePath = str_replace('.tsx', '', $relativePath);

            $pages[$pagePath] = [
                'path' => $pagePath,
                'filePath' => $file->getPathname(),
            ];
        }

        return $pages;
    }

    /**
     * @param  array<string, array<string>>  $undocumented
     */
    private function displayUndocumented(array $undocumented): void
    {
        $total = count($undocumented['actions']) + count($undocumented['controllers']) + count($undocumented['pages']);

        if ($total === 0) {
            $this->info('All items are documented!');

            return;
        }

        $this->warn(sprintf('Found %d undocumented item(s):', $total));

        foreach (['actions', 'controllers', 'pages'] as $type) {
            if (! empty($undocumented[$type])) {
                $this->line('  '.ucfirst($type).':');
                foreach ($undocumented[$type] as $item) {
                    $this->line('    - '.$item);
                }
            }
        }
    }

    /**
     * @param  array<string, array<string>>  $undocumented
     */
    private function generateStubs(array $undocumented): void
    {
        $this->info('Generating documentation stubs...');

        foreach ($undocumented['actions'] as $action) {
            $this->generateStubFromTemplate('action', $action, 'docs/developer/backend/actions/', ['{ActionName}' => $action]);
        }

        foreach ($undocumented['controllers'] as $controller) {
            $this->generateStubFromTemplate('controller', $controller, 'docs/developer/backend/controllers/', ['{ControllerName}' => $controller]);
        }

        foreach ($undocumented['pages'] as $page) {
            $this->generateStubFromTemplate('page', basename($page), sprintf('docs/developer/frontend/pages/%s', dirname($page) === '.' ? '' : dirname($page).'/'), ['{PageName}' => basename($page), '{path}' => $page]);
        }

        $this->info('Documentation stubs generated!');
    }

    /**
     * @param  array<string, string>  $replacements
     */
    private function generateStubFromTemplate(string $templateName, string $itemName, string $outputDir, array $replacements): void
    {
        $templatePath = base_path("docs/.templates/{$templateName}.md");

        if (! File::exists($templatePath)) {
            $this->warn("Template not found: {$templatePath}");

            return;
        }

        $content = str_replace(array_keys($replacements), array_values($replacements), File::get($templatePath));
        $outputPath = base_path($outputDir.mb_strtolower($itemName).'.md');

        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $content);

        $this->line("  Generated: {$outputPath}");
    }

    /**
     * @param  array<string, array<string>>  $undocumented
     * @param  array<string, mixed>  $manifest
     */
    private function markStubsAsDocumented(array $undocumented, array &$manifest, string $manifestPath): void
    {
        foreach ($undocumented['actions'] ?? [] as $actionName) {
            $manifest['actions'][$actionName]['documented'] = true;
            $manifest['actions'][$actionName]['path'] = './'.mb_strtolower($actionName).'.md';
        }

        foreach ($undocumented['controllers'] ?? [] as $controllerName) {
            $manifest['controllers'][$controllerName]['documented'] = true;
            $manifest['controllers'][$controllerName]['path'] = './'.mb_strtolower($controllerName).'.md';
        }

        foreach ($undocumented['pages'] ?? [] as $pagePath) {
            $manifest['pages'][$pagePath]['documented'] = true;
            $manifest['pages'][$pagePath]['developerGuide'] = sprintf('./%s.md', $pagePath);
        }

        File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        $this->updateIndexFiles($manifest);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractMethodInfo(ReflectionMethod $method): array
    {
        $parameters = [];
        foreach ($method->getParameters() as $param) {
            $paramInfo = [
                'name' => $param->getName(),
                'type' => $this->getParameterType($param),
                'isOptional' => $param->isOptional(),
            ];

            if ($param->isDefaultValueAvailable()) {
                try {
                    $paramInfo['default'] = $param->getDefaultValue();
                } catch (ReflectionException) {
                    $paramInfo['default'] = null;
                }
            }

            $parameters[] = $paramInfo;
        }

        return [
            'name' => $method->getName(),
            'parameters' => $parameters,
            'returnType' => $this->getReturnType($method),
        ];
    }

    private function getParameterType(ReflectionParameter $param): ?string
    {
        if (! $param->hasType()) {
            return null;
        }

        $type = $param->getType();

        return $type instanceof ReflectionNamedType ? $type->getName() : (string) $type;
    }

    private function getReturnType(ReflectionMethod $method): ?string
    {
        if (! $method->hasReturnType()) {
            return null;
        }

        $returnType = $method->getReturnType();

        return $returnType instanceof ReflectionNamedType ? $returnType->getName() : (string) $returnType;
    }

    private function getClassNameFromFile(string $filePath): ?string
    {
        $content = File::get($filePath);

        if (! preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch)) {
            return null;
        }

        if (! preg_match('/\b(?:final\s+)?(?:readonly\s+)?class\s+(\w+)/', $content, $classMatch)) {
            return null;
        }

        return sprintf('%s\%s', $namespaceMatch[1], $classMatch[1]);
    }

    private function getMethodBody(string $filePath, string $methodName): string
    {
        $content = File::get($filePath);
        $pattern = '/function\s+'.$methodName.'\s*\([^)]*\)\s*\{([^{}]*(?:\{[^{}]*\}[^{}]*)*)\}/s';

        return preg_match($pattern, $content, $matches) ? ($matches[1] ?? '') : '';
    }

    /**
     * @return array<string>
     */
    private function extractActionClasses(string $methodBody): array
    {
        $actions = [];

        if (preg_match_all('/(?:app\(|new\s+)([A-Z]\w+Action)::class/', $methodBody, $matches)) {
            $actions = array_unique($matches[1]);
        }

        if (preg_match_all('/([A-Z]\w+Action)\s+\$/', $methodBody, $matches)) {
            $actions = array_merge($actions, $matches[1]);
        }

        return array_unique($actions);
    }

    /**
     * @return array<string>
     */
    private function extractFormRequestClasses(string $methodBody): array
    {
        if (preg_match_all('/([A-Z]\w+Request)\s+\$request/', $methodBody, $matches)) {
            return array_unique($matches[1]);
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $manifest
     */
    private function updateIndexFiles(array $manifest): void
    {
        $this->updateActionsIndex($manifest['actions'] ?? []);
        $this->updateControllersIndex($manifest['controllers'] ?? []);
        $this->updatePagesIndex($manifest['pages'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $items
     */
    private function updateActionsIndex(array $items): void
    {
        $this->updateIndex(
            base_path('docs/developer/backend/actions/README.md'),
            'Available Actions',
            ['Action', 'Documented'],
            $items,
            fn (string $name, array $info) => [
                isset($info['path']) ? "[{$name}]({$info['path']})" : $name,
                ($info['documented'] ?? false) ? 'Yes' : 'No',
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $items
     */
    private function updateControllersIndex(array $items): void
    {
        $this->updateIndex(
            base_path('docs/developer/backend/controllers/README.md'),
            'Available Controllers',
            ['Controller', 'Documented'],
            $items,
            fn (string $name, array $info) => [
                isset($info['path']) ? "[{$name}]({$info['path']})" : $name,
                ($info['documented'] ?? false) ? 'Yes' : 'No',
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $items
     */
    private function updatePagesIndex(array $items): void
    {
        $this->updateIndex(
            base_path('docs/developer/frontend/pages/README.md'),
            'Available Pages',
            ['Page', 'Documented'],
            $items,
            fn (string $name, array $info) => [
                isset($info['developerGuide']) ? "[{$name}]({$info['developerGuide']})" : $name,
                ($info['documented'] ?? false) ? 'Yes' : 'No',
            ]
        );
    }

    /**
     * @param  array<string>  $headers
     * @param  array<string, mixed>  $items
     */
    private function updateIndex(string $indexPath, string $sectionTitle, array $headers, array $items, callable $rowMapper): void
    {
        if (! File::exists($indexPath)) {
            return;
        }

        $content = File::get($indexPath);

        $table = '| '.implode(' | ', $headers)." |\n";
        $table .= '|'.str_repeat('------|', count($headers))."\n";

        foreach ($items as $name => $info) {
            if (is_string($info)) {
                $name = $info;
                $info = [];
            }

            $row = $rowMapper($name, $info);
            $table .= '| '.implode(' | ', $row)." |\n";
        }

        $pattern = "/## {$sectionTitle}\n\n(.*?)(?=\n##|\n>|$)/s";

        if (preg_match($pattern, $content, $matches)) {
            $content = str_replace($matches[0], "## {$sectionTitle}\n\n{$table}\n", $content);
        } else {
            $content .= "\n\n## {$sectionTitle}\n\n{$table}\n";
        }

        File::put($indexPath, $content);
    }
}
