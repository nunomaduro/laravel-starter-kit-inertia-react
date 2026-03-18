<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DocumentationCrossReference;
use App\Services\DocumentationPrismGenerator;
use App\Services\DocumentationPromptGenerator;
use App\Services\DocumentationTemplateSelector;
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
                            {--generate : Generate documentation stubs for undocumented items}
                            {--ai : Use AI (Prism) to generate full documentation}
                            {--auto : Call Prism to generate and write docs (requires --ai); without --auto only writes prompts to docs/.ai-prompts/}';

    protected $description = 'Sync documentation manifest with actual codebase';

    public function __construct(
        private readonly DocumentationCrossReference $crossReference,
        private readonly DocumentationTemplateSelector $templateSelector,
        private readonly DocumentationPromptGenerator $promptGenerator,
        private readonly DocumentationPrismGenerator $prismGenerator
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

        if (isset($undocumented['actions']) && $undocumented['actions'] !== [] || isset($undocumented['controllers']) && $undocumented['controllers'] !== [] || isset($undocumented['pages']) && $undocumented['pages'] !== []) {
            $this->displayUndocumented($undocumented);

            if ($this->option('generate')) {
                if ($this->option('ai')) {
                    $usePrism = $this->option('auto') && $this->prismGenerator->isAvailable();
                    if ($usePrism) {
                        $this->generateWithPrism($undocumented, $actions, $controllers, $pages, $actionRelationships, $controllerRelationships, $pageRelationships, $manifest, $manifestPath);
                    } else {
                        $this->generateWithAI($undocumented, $actions, $controllers, $pages, $actionRelationships, $controllerRelationships, $pageRelationships);
                        if ($this->option('auto') && ! $this->prismGenerator->isAvailable()) {
                            $this->warn('Prism/OpenRouter not configured. Set OPENROUTER_API_KEY for --auto. Prompts written to docs/.ai-prompts/');
                        }
                    }
                } else {
                    $this->generateStubs($undocumented);
                    $this->markStubsAsDocumented($undocumented, $manifest, $manifestPath);
                }
            } else {
                $this->warn('Run with --generate to create documentation stubs for undocumented items.');
                if ($this->option('ai')) {
                    $this->warn('Use --generate --ai to generate prompts; add --auto to call Prism and write docs.');
                }
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

        $files = File::files($actionsPath);

        foreach ($files as $file) {
            $className = $file->getFilenameWithoutExtension();
            $filePath = $file->getPathname();

            $phpDoc = $this->extractPHPDoc($filePath);
            $className = $this->getClassNameFromFile($filePath);

            if ($className === null) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($className);
                $handleMethod = $reflection->hasMethod('handle') ? $reflection->getMethod('handle') : null;

                $shortClassName = class_basename($className);

                $actionInfo = [
                    'name' => $shortClassName,
                    'fullName' => $className,
                    'filePath' => $filePath,
                    'phpDoc' => $phpDoc,
                    'handleMethod' => $handleMethod ? $this->extractMethodInfo($handleMethod) : null,
                    'dependencies' => [],
                ];

                if ($reflection->hasMethod('__construct')) {
                    $constructor = $reflection->getMethod('__construct');
                    foreach ($constructor->getParameters() as $param) {
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
                $actions[$shortClassName] = ['name' => $shortClassName, 'filePath' => $filePath];
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

        $files = File::files($controllersPath);

        foreach ($files as $file) {
            $className = $file->getFilenameWithoutExtension();
            $filePath = $file->getPathname();

            $phpDoc = $this->extractPHPDoc($filePath);
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
                    'phpDoc' => $phpDoc,
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

        $files = File::allFiles($pagesPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'tsx') {
                $relativePath = str_replace($pagesPath.'/', '', $file->getPathname());
                if (str_contains($relativePath, '/_components/')) {
                    continue;
                }

                $pagePath = str_replace('.tsx', '', $relativePath);
                $filePath = $file->getPathname();

                $tsDoc = $this->extractTSDoc($filePath);

                $pages[$pagePath] = [
                    'path' => $pagePath,
                    'filePath' => $filePath,
                    'tsDoc' => $tsDoc,
                ];
            }
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
            $this->info('✓ All items are documented!');

            return;
        }

        $this->warn(sprintf('Found %d undocumented item(s):', $total));

        if (! empty($undocumented['actions'])) {
            $this->line('  Actions:');
            foreach ($undocumented['actions'] as $action) {
                $this->line('    - '.$action);
            }
        }

        if (! empty($undocumented['controllers'])) {
            $this->line('  Controllers:');
            foreach ($undocumented['controllers'] as $controller) {
                $this->line('    - '.$controller);
            }
        }

        if (! empty($undocumented['pages'])) {
            $this->line('  Pages:');
            foreach ($undocumented['pages'] as $page) {
                $this->line('    - '.$page);
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
            $this->generateActionStub($action);
        }

        foreach ($undocumented['controllers'] as $controller) {
            $this->generateControllerStub($controller);
        }

        foreach ($undocumented['pages'] as $page) {
            $this->generatePageStub($page);
        }

        $this->info('Documentation stubs generated!');
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

    private function generateActionStub(string $action): void
    {
        $templatePath = base_path('docs/.templates/action.md');
        $outputPath = base_path('docs/developer/backend/actions/'.mb_strtolower($action).'.md');

        if (! File::exists($templatePath)) {
            $this->warn('Template not found: '.$templatePath);

            return;
        }

        $template = File::get($templatePath);
        $content = str_replace('{ActionName}', $action, $template);

        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $content);

        $this->line('  ✓ Generated: '.$outputPath);
    }

    private function generateControllerStub(string $controller): void
    {
        $templatePath = base_path('docs/.templates/controller.md');
        $outputPath = base_path('docs/developer/backend/controllers/'.mb_strtolower($controller).'.md');

        if (! File::exists($templatePath)) {
            $this->warn('Template not found: '.$templatePath);

            return;
        }

        $template = File::get($templatePath);
        $content = str_replace('{ControllerName}', $controller, $template);

        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $content);

        $this->line('  ✓ Generated: '.$outputPath);
    }

    private function generatePageStub(string $page): void
    {
        $templatePath = base_path('docs/.templates/page.md');
        $pageName = basename($page);
        $outputPath = base_path(sprintf('docs/developer/frontend/pages/%s.md', $page));

        if (! File::exists($templatePath)) {
            $this->warn('Template not found: '.$templatePath);

            return;
        }

        $template = File::get($templatePath);
        $content = str_replace(['{PageName}', '{path}'], [$pageName, $page], $template);

        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $content);

        $this->line('  ✓ Generated: '.$outputPath);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractPHPDoc(string $filePath): array
    {
        if (! File::exists($filePath)) {
            return [];
        }

        $className = $this->getClassNameFromFile($filePath);

        if ($className === null) {
            return [];
        }

        try {
            $reflection = new ReflectionClass($className);
        } catch (ReflectionException) {
            return [];
        }

        $docBlock = $reflection->getDocComment() ?: '';

        $result = [
            'class' => [
                'docBlock' => $docBlock,
                'parsed' => $this->parseDocBlock($docBlock),
            ],
            'methods' => [],
        ];

        if ($reflection->hasMethod('handle')) {
            $method = $reflection->getMethod('handle');
            $result['methods']['handle'] = $this->extractMethodInfo($method);
        }

        if ($reflection->hasMethod('__construct')) {
            $constructor = $reflection->getMethod('__construct');
            $result['methods']['__construct'] = $this->extractMethodInfo($constructor);
        }

        if (str_contains($className, 'Controller')) {
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (! $method->isConstructor()) {
                    $result['methods'][$method->getName()] = $this->extractMethodInfo($method);
                }
            }
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractTSDoc(string $filePath): array
    {
        if (! File::exists($filePath)) {
            return [];
        }

        $content = File::get($filePath);
        $result = [
            'component' => null,
            'props' => [],
            'description' => null,
        ];

        if (preg_match('/\/\*\*([^*]|(?:\*(?!\/)))*\*\//s', $content, $matches)) {
            $jsDoc = $matches[0];
            $result['description'] = $this->extractJSDocDescription($jsDoc);
        }

        if (preg_match('/interface\s+(\w+)\s*\{([^}]+)\}/s', $content, $matches)) {
            $interfaceName = $matches[1];
            $interfaceBody = $matches[2];
            $result['props'] = $this->extractPropsFromInterface($interfaceBody);
        }

        if (preg_match('/export\s+default\s+function\s+(\w+)/', $content, $matches)) {
            $result['component'] = $matches[1];
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseDocBlock(string $docBlock): array
    {
        if ($docBlock === '' || $docBlock === '0') {
            return [];
        }

        $result = [
            'description' => '',
            'params' => [],
            'return' => null,
            'throws' => [],
            'see' => [],
        ];

        $docBlock = preg_replace('/^\/\*\*|\*\/$/', '', $docBlock);
        $lines = explode("\n", (string) $docBlock);

        $description = [];
        $inDescription = true;

        foreach ($lines as $line) {
            $line = mb_trim($line);
            $line = preg_replace('/^\*\s*/', '', $line);

            if (empty($line)) {
                continue;
            }

            if (preg_match('/@param\s+([^\s]+)\s+\$(\w+)\s*(.*)/', $line, $matches)) {
                $inDescription = false;
                $result['params'][$matches[2]] = [
                    'type' => $matches[1],
                    'name' => $matches[2],
                    'description' => mb_trim($matches[3]),
                ];

                continue;
            }

            if (preg_match('/@return\s+([^\s]+)\s*(.*)/', $line, $matches)) {
                $inDescription = false;
                $result['return'] = [
                    'type' => $matches[1],
                    'description' => mb_trim($matches[2]),
                ];

                continue;
            }

            if (preg_match('/@throws\s+([^\s]+)\s*(.*)/', $line, $matches)) {
                $inDescription = false;
                $result['throws'][] = [
                    'type' => $matches[1],
                    'description' => mb_trim($matches[2]),
                ];

                continue;
            }

            if (preg_match('/@see\s+(.+)/', $line, $matches)) {
                $inDescription = false;
                $result['see'][] = mb_trim($matches[1]);

                continue;
            }

            if ($inDescription && ! str_starts_with($line, '@')) {
                $description[] = $line;
            }
        }

        $result['description'] = mb_trim(implode(' ', $description));

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractMethodInfo(ReflectionMethod $method): array
    {
        $docBlock = $method->getDocComment() ?: '';
        $parsed = $this->parseDocBlock($docBlock);

        $parameters = [];
        foreach ($method->getParameters() as $param) {
            $paramInfo = [
                'name' => $param->getName(),
                'type' => $this->getParameterType($param),
                'hasDefault' => $param->isDefaultValueAvailable(),
                'isOptional' => $param->isOptional(),
            ];

            if ($param->isDefaultValueAvailable()) {
                try {
                    $paramInfo['default'] = $param->getDefaultValue();
                } catch (ReflectionException) {
                    $paramInfo['default'] = null;
                }
            }

            if (isset($parsed['params'][$param->getName()])) {
                $paramInfo = array_merge($paramInfo, $parsed['params'][$param->getName()]);
            }

            $parameters[] = $paramInfo;
        }

        return [
            'name' => $method->getName(),
            'docBlock' => $docBlock,
            'parsed' => $parsed,
            'parameters' => $parameters,
            'returnType' => $this->getReturnType($method),
            'isPublic' => $method->isPublic(),
        ];
    }

    private function getParameterType(ReflectionParameter $param): ?string
    {
        if ($param->hasType()) {
            $type = $param->getType();

            return $type instanceof ReflectionNamedType
                ? $type->getName()
                : (string) $type;
        }

        return null;
    }

    private function getReturnType(ReflectionMethod $method): ?string
    {
        if ($method->hasReturnType()) {
            $returnType = $method->getReturnType();

            return $returnType instanceof ReflectionNamedType
                ? $returnType->getName()
                : (string) $returnType;
        }

        return null;
    }

    private function getClassNameFromFile(string $filePath): ?string
    {
        $content = File::get($filePath);

        if (! preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch)) {
            return null;
        }

        $namespace = $namespaceMatch[1];

        if (! preg_match('/\b(?:final\s+)?(?:readonly\s+)?class\s+(\w+)/', $content, $classMatch)) {
            return null;
        }

        $className = $classMatch[1];

        return sprintf('%s\%s', $namespace, $className);
    }

    private function extractJSDocDescription(string $jsDoc): ?string
    {
        $lines = explode("\n", $jsDoc);
        $description = [];

        foreach ($lines as $line) {
            $line = mb_trim($line);
            $line = preg_replace('/^\/\*\*|\*\/$|\*\s*/', '', $line);
            if (empty($line)) {
                continue;
            }

            if (str_starts_with((string) $line, '@')) {
                continue;
            }

            $description[] = $line;
        }

        return $description === [] ? null : mb_trim(implode(' ', $description));
    }

    /**
     * @return array<string, mixed>
     */
    private function extractPropsFromInterface(string $interfaceBody): array
    {
        $props = [];
        $lines = explode("\n", $interfaceBody);

        foreach ($lines as $line) {
            $line = mb_trim($line);
            if ($line === '') {
                continue;
            }

            if ($line === '0') {
                continue;
            }

            if (str_starts_with($line, '//')) {
                continue;
            }

            if (preg_match('/(\w+)\??\s*:\s*([^;]+);?/', $line, $matches)) {
                $props[] = [
                    'name' => $matches[1],
                    'type' => mb_trim($matches[2]),
                    'optional' => str_contains($line, '?'),
                ];
            }
        }

        return $props;
    }

    private function getMethodBody(string $filePath, string $methodName): string
    {
        $content = File::get($filePath);

        $pattern = '/function\s+'.$methodName.'\s*\([^)]*\)\s*\{([^{}]*(?:\{[^{}]*\}[^{}]*)*)\}/s';

        if (preg_match($pattern, $content, $matches)) {
            return $matches[1] ?? '';
        }

        return '';
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
        $this->info('Updating index files...');

        $this->updateActionsIndex($manifest['actions'] ?? []);
        $this->updateControllersIndex($manifest['controllers'] ?? []);
        $this->updatePagesIndex($manifest['pages'] ?? []);

        $this->info('Index files updated!');
    }

    /**
     * @param  array<string, mixed>  $actions
     */
    private function updateActionsIndex(array $actions): void
    {
        $indexPath = base_path('docs/developer/backend/actions/README.md');

        if (! File::exists($indexPath)) {
            return;
        }

        $content = File::get($indexPath);

        $table = "| Action | Purpose | Documented |\n";
        $table .= "|--------|---------|------------|\n";

        foreach ($actions as $actionName => $actionInfo) {
            if (is_string($actionInfo)) {
                $actionName = $actionInfo;
                $actionInfo = [];
            }

            $documented = $actionInfo['documented'] ?? false;
            $path = $actionInfo['path'] ?? null;

            $purpose = 'N/A';
            if (isset($actionInfo['phpDoc']['class']['parsed']['description'])) {
                $purpose = $actionInfo['phpDoc']['class']['parsed']['description'];
            } elseif (isset($actionInfo['parsed']['description'])) {
                $purpose = $actionInfo['parsed']['description'];
            }

            if (mb_strlen((string) $purpose) > 60) {
                $purpose = mb_substr((string) $purpose, 0, 57).'...';
            }

            $status = $documented ? '✅' : '❌';
            $link = $path ? sprintf('[%s](%s)', $actionName, $path) : $actionName;

            $table .= "| {$link} | {$purpose} | {$status} |\n";
        }

        if (preg_match('/## Available Actions\n\n(.*?)(?=\n##|\n>|$)/s', $content, $matches)) {
            $newSection = "## Available Actions\n\n{$table}\n";
            $content = str_replace($matches[0], $newSection, $content);
        } else {
            $content .= "\n\n## Available Actions\n\n{$table}\n";
        }

        File::put($indexPath, $content);
    }

    /**
     * @param  array<string, mixed>  $controllers
     */
    private function updateControllersIndex(array $controllers): void
    {
        $indexPath = base_path('docs/developer/backend/controllers/README.md');

        if (! File::exists($indexPath)) {
            File::ensureDirectoryExists(dirname($indexPath));
            File::put($indexPath, "# Controllers\n\n");
        }

        $content = File::get($indexPath);

        $table = "| Controller | Purpose | Documented |\n";
        $table .= "|------------|---------|------------|\n";

        foreach ($controllers as $controllerName => $controllerInfo) {
            if (is_string($controllerInfo)) {
                $controllerName = $controllerInfo;
                $controllerInfo = [];
            }

            $documented = $controllerInfo['documented'] ?? false;
            $path = $controllerInfo['path'] ?? null;

            $purpose = 'N/A';
            if (isset($controllerInfo['phpDoc']['class']['parsed']['description'])) {
                $purpose = $controllerInfo['phpDoc']['class']['parsed']['description'];
            } elseif (isset($controllerInfo['parsed']['description'])) {
                $purpose = $controllerInfo['parsed']['description'];
            }

            if (mb_strlen((string) $purpose) > 60) {
                $purpose = mb_substr((string) $purpose, 0, 57).'...';
            }

            $status = $documented ? '✅' : '❌';
            $link = $path ? sprintf('[%s](%s)', $controllerName, $path) : $controllerName;

            $table .= "| {$link} | {$purpose} | {$status} |\n";
        }

        if (preg_match('/## Available Controllers\n\n(.*?)(?=\n##|\n>|$)/s', $content, $matches)) {
            $newSection = "## Available Controllers\n\n{$table}\n";
            $content = str_replace($matches[0], $newSection, $content);
        } else {
            $content .= "\n\n## Available Controllers\n\n{$table}\n";
        }

        File::put($indexPath, $content);
    }

    /**
     * @param  array<string, mixed>  $pages
     */
    private function updatePagesIndex(array $pages): void
    {
        $indexPath = base_path('docs/developer/frontend/pages/README.md');

        if (! File::exists($indexPath)) {
            return;
        }

        $content = File::get($indexPath);

        $table = "| Page | Route | Documented |\n";
        $table .= "|------|-------|------------|\n";

        foreach ($pages as $pagePath => $pageInfo) {
            $documented = $pageInfo['documented'] ?? false;
            $developerGuide = $pageInfo['developerGuide'] ?? null;

            $routes = $pageInfo['relationships']['relatedRoutes'] ?? [];
            $routeDisplay = empty($routes) ? 'N/A' : implode(', ', array_slice($routes, 0, 2));

            $status = $documented ? '✅' : '❌';
            $link = $developerGuide ? sprintf('[%s](%s)', $pagePath, $developerGuide) : $pagePath;

            $table .= "| {$link} | {$routeDisplay} | {$status} |\n";
        }

        if (preg_match('/## Available Pages\n\n(.*?)(?=\n##|\n>|$)/s', $content, $matches)) {
            $newSection = "## Available Pages\n\n{$table}\n";
            $content = str_replace($matches[0], $newSection, $content);
        } else {
            $content .= "\n\n## Available Pages\n\n{$table}\n";
        }

        File::put($indexPath, $content);
    }

    /**
     * @param  array<string, array<string>>  $undocumented
     * @param  array<string, mixed>  $actions
     * @param  array<string, mixed>  $controllers
     * @param  array<string, mixed>  $pages
     * @param  array<string, mixed>  $actionRelationships
     * @param  array<string, mixed>  $controllerRelationships
     * @param  array<string, mixed>  $pageRelationships
     * @param  array<string, mixed>  $manifest
     */
    private function generateWithPrism(
        array $undocumented,
        array $actions,
        array $controllers,
        array $pages,
        array $actionRelationships,
        array $controllerRelationships,
        array $pageRelationships,
        array &$manifest,
        string $manifestPath
    ): void {
        $this->info('Generating documentation with Prism (OpenRouter)...');

        foreach ($undocumented['actions'] as $actionName) {
            if (! isset($actions[$actionName])) {
                continue;
            }

            $written = $this->prismGenerator->generateActionDoc(
                $actionName,
                $actions[$actionName],
                $actionRelationships[$actionName] ?? []
            );

            if ($written !== null) {
                $relativePath = str_replace(base_path().'/', '', $written);
                $manifest['actions'][$actionName]['documented'] = true;
                $manifest['actions'][$actionName]['path'] = $relativePath;
                $this->line('  ✓ Generated: '.$relativePath);
            } else {
                $this->warn('  ✗ Failed: '.$actionName);
            }
        }

        foreach ($undocumented['controllers'] as $controllerName) {
            if (! isset($controllers[$controllerName])) {
                continue;
            }

            $written = $this->prismGenerator->generateControllerDoc(
                $controllerName,
                $controllers[$controllerName],
                $controllerRelationships[$controllerName] ?? []
            );

            if ($written !== null) {
                $relativePath = str_replace(base_path().'/', '', $written);
                $manifest['controllers'][$controllerName]['documented'] = true;
                $manifest['controllers'][$controllerName]['path'] = $relativePath;
                $this->line('  ✓ Generated: '.$relativePath);
            } else {
                $this->warn('  ✗ Failed: '.$controllerName);
            }
        }

        foreach ($undocumented['pages'] as $pagePath) {
            if (! isset($pages[$pagePath])) {
                continue;
            }

            $written = $this->prismGenerator->generatePageDoc(
                $pagePath,
                $pages[$pagePath],
                $pageRelationships[$pagePath] ?? []
            );

            if ($written !== null) {
                $relativePath = str_replace(base_path().'/', '', $written);
                $manifest['pages'][$pagePath]['documented'] = true;
                $manifest['pages'][$pagePath]['developerGuide'] = $relativePath;
                $this->line('  ✓ Generated: '.$relativePath);
            } else {
                $this->warn('  ✗ Failed: '.$pagePath);
            }
        }

        File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        $this->updateIndexFiles($manifest);
        $this->info('Documentation generated with Prism.');
    }

    /**
     * @param  array<string, array<string>>  $undocumented
     * @param  array<string, mixed>  $actions
     * @param  array<string, mixed>  $controllers
     * @param  array<string, mixed>  $pages
     * @param  array<string, mixed>  $actionRelationships
     * @param  array<string, mixed>  $controllerRelationships
     * @param  array<string, mixed>  $pageRelationships
     */
    private function generateWithAI(
        array $undocumented,
        array $actions,
        array $controllers,
        array $pages,
        array $actionRelationships,
        array $controllerRelationships,
        array $pageRelationships
    ): void {
        $this->info('Generating AI prompts for documentation...');
        $this->warn('Note: This generates prompts. Use an AI agent to process these prompts and generate documentation.');

        $promptsDir = base_path('docs/.ai-prompts');
        File::ensureDirectoryExists($promptsDir);

        foreach ($undocumented['actions'] as $actionName) {
            if (! isset($actions[$actionName])) {
                continue;
            }

            $actionInfo = $actions[$actionName];
            $relationships = $actionRelationships[$actionName] ?? [];
            $templateName = $this->templateSelector->selectActionTemplate($actionInfo);
            $templatePath = $this->templateSelector->getTemplatePath($templateName);

            $prompt = $this->promptGenerator->generateActionPrompt($actionInfo, $relationships, $templatePath);

            $promptFile = sprintf('%s/action-%s.txt', $promptsDir, $actionName);
            File::put($promptFile, $prompt);

            $this->line('  ✓ Generated prompt: '.$promptFile);
            $this->line('    Use this prompt with an AI agent to generate documentation for '.$actionName);
        }

        foreach ($undocumented['controllers'] as $controllerName) {
            if (! isset($controllers[$controllerName])) {
                continue;
            }

            $controllerInfo = $controllers[$controllerName];
            $relationships = $controllerRelationships[$controllerName] ?? [];
            $templateName = $this->templateSelector->selectControllerTemplate($controllerInfo);
            $templatePath = $this->templateSelector->getTemplatePath($templateName);

            $prompt = $this->promptGenerator->generateControllerPrompt($controllerInfo, $relationships, $templatePath);

            $promptFile = sprintf('%s/controller-%s.txt', $promptsDir, $controllerName);
            File::put($promptFile, $prompt);

            $this->line('  ✓ Generated prompt: '.$promptFile);
        }

        foreach ($undocumented['pages'] as $pagePath) {
            if (! isset($pages[$pagePath])) {
                continue;
            }

            $pageInfo = $pages[$pagePath];
            $relationships = $pageRelationships[$pagePath] ?? [];
            $templateName = $this->templateSelector->selectPageTemplate($pageInfo);
            $templatePath = $this->templateSelector->getTemplatePath($templateName);

            $prompt = $this->promptGenerator->generatePagePrompt($pageInfo, $relationships, $templatePath);

            $safePageName = str_replace('/', '-', $pagePath);
            $promptFile = sprintf('%s/page-%s.txt', $promptsDir, $safePageName);
            File::put($promptFile, $prompt);

            $this->line('  ✓ Generated prompt: '.$promptFile);
        }

        $this->info('AI prompts generated!');
        $this->info('Prompts saved to: '.$promptsDir);
        $this->warn('Use an AI agent to process these prompts and generate the actual documentation files.');
    }
}
