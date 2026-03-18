<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionType;

final class ReviewDocumentation extends Command
{
    protected $signature = 'docs:review
                            {--component= : Review specific component (action, controller, page)}
                            {--name= : Name of specific component to review}';

    protected $description = 'Review documentation quality and completeness';

    public function handle(): int
    {
        $manifestPath = base_path('docs/.manifest.json');

        if (! File::exists($manifestPath)) {
            $this->error('Manifest file not found at docs/.manifest.json');

            return self::FAILURE;
        }

        $manifest = json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);

        $this->info('Reviewing documentation quality...');

        $issues = [];

        if ($this->option('component') === null || $this->option('component') === 'action') {
            $issues = array_merge($issues, $this->reviewActions($manifest['actions'] ?? []));
        }

        if ($this->option('component') === null || $this->option('component') === 'controller') {
            $issues = array_merge($issues, $this->reviewControllers($manifest['controllers'] ?? []));
        }

        if ($this->option('component') === null || $this->option('component') === 'page') {
            $issues = array_merge($issues, $this->reviewPages($manifest['pages'] ?? []));
        }

        if ($issues === []) {
            $this->info('✓ All documentation is up to date and complete!');

            return self::SUCCESS;
        }

        $this->warn('Found '.count($issues).' issue(s):');
        foreach ($issues as $issue) {
            $this->line('  - '.$issue);
        }

        return self::FAILURE;
    }

    /**
     * @param  array<string, mixed>  $actions
     * @return array<string>
     */
    private function reviewActions(array $actions): array
    {
        $issues = [];

        foreach ($actions as $actionName => $actionInfo) {
            if (! ($actionInfo['documented'] ?? false)) {
                continue;
            }

            $docPath = $actionInfo['path'] ?? null;
            $resolvedDocPath = $docPath !== null ? $this->resolveDocPath($docPath, 'action') : null;
            if ($docPath === null || $resolvedDocPath === null || ! File::exists($resolvedDocPath)) {
                $issues[] = sprintf('Action %s: Documentation file not found', $actionName);

                continue;
            }

            // Check if code file exists
            $codePath = app_path(sprintf('Actions/%s.php', $actionName));
            if (! File::exists($codePath)) {
                $issues[] = sprintf('Action %s: Code file not found', $actionName);

                continue;
            }

            // Verify method signatures match
            $issues = array_merge($issues, $this->verifyActionSignature($actionName, $codePath, $resolvedDocPath));
        }

        return $issues;
    }

    /**
     * @param  array<string, mixed>  $controllers
     * @return array<string>
     */
    private function reviewControllers(array $controllers): array
    {
        $issues = [];

        foreach ($controllers as $controllerName => $controllerInfo) {
            if (! ($controllerInfo['documented'] ?? false)) {
                continue;
            }

            $docPath = $controllerInfo['path'] ?? null;
            $resolvedDocPath = $docPath !== null ? $this->resolveDocPath($docPath, 'controller') : null;
            if ($docPath === null || $resolvedDocPath === null || ! File::exists($resolvedDocPath)) {
                $issues[] = sprintf('Controller %s: Documentation file not found', $controllerName);

                continue;
            }

            // Check if code file exists
            $codePath = app_path(sprintf('Http/Controllers/%s.php', $controllerName));
            if (! File::exists($codePath)) {
                $issues[] = sprintf('Controller %s: Code file not found', $controllerName);

                continue;
            }

            // Verify methods match
            $issues = array_merge($issues, $this->verifyControllerMethods($controllerName, $codePath, $resolvedDocPath));
        }

        return $issues;
    }

    /**
     * @param  array<string, mixed>  $pages
     * @return array<string>
     */
    private function reviewPages(array $pages): array
    {
        $issues = [];

        foreach ($pages as $pagePath => $pageInfo) {
            if (! ($pageInfo['documented'] ?? false)) {
                continue;
            }

            $docPath = $pageInfo['developerGuide'] ?? null;
            $resolvedDocPath = $docPath !== null ? $this->resolveDocPath($docPath, 'page') : null;
            if ($docPath === null || $resolvedDocPath === null || ! File::exists($resolvedDocPath)) {
                $issues[] = sprintf('Page %s: Documentation file not found', $pagePath);

                continue;
            }

            // Check if code file exists
            $codePath = resource_path(sprintf('js/pages/%s.tsx', $pagePath));
            if (! File::exists($codePath)) {
                $issues[] = sprintf('Page %s: Code file not found', $pagePath);

                continue;
            }
        }

        return $issues;
    }

    /**
     * @return array<string>
     */
    private function verifyActionSignature(string $actionName, string $codePath, string $docPath): array
    {
        $issues = [];

        try {
            $className = $this->getClassNameFromFile($codePath);
            if ($className === null) {
                return $issues;
            }

            $reflection = new ReflectionClass($className);
            if (! $reflection->hasMethod('handle')) {
                return $issues;
            }

            $method = $reflection->getMethod('handle');
            $returnType = $method->getReturnType() instanceof ReflectionType ? (string) $method->getReturnType() : 'mixed';

            $docContent = File::get($docPath);

            // Check if return type is documented
            if (! str_contains($docContent, $returnType) && $returnType !== 'mixed') {
                $issues[] = sprintf('Action %s: Return type mismatch (code: %s)', $actionName, $returnType);
            }

            // Check parameter count
            $paramCount = count($method->getParameters());
            $docParamCount = mb_substr_count($docContent, '| Parameter |');

            if ($docParamCount < $paramCount) {
                $issues[] = sprintf('Action %s: Missing parameter documentation (%d params, %d documented)', $actionName, $paramCount, $docParamCount);
            }
        } catch (ReflectionException) {
            $issues[] = sprintf('Action %s: Could not verify signature', $actionName);
        }

        return $issues;
    }

    /**
     * @return array<string>
     */
    private function verifyControllerMethods(string $controllerName, string $codePath, string $docPath): array
    {
        $issues = [];

        try {
            $className = $this->getClassNameFromFile($codePath);
            if ($className === null) {
                return $issues;
            }

            $reflection = new ReflectionClass($className);
            $publicMethods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
            $methodCount = count($publicMethods);

            $docContent = File::get($docPath);

            // Count methods in documentation
            $docMethodCount = mb_substr_count($docContent, '| Method |');

            if ($docMethodCount < $methodCount) {
                $issues[] = sprintf('Controller %s: Missing method documentation (%d methods, %d documented)', $controllerName, $methodCount, $docMethodCount);
            }
        } catch (ReflectionException) {
            $issues[] = sprintf('Controller %s: Could not verify methods', $controllerName);
        }

        return $issues;
    }

    /**
     * @param  'action'|'controller'|'page'  $type
     */
    private function resolveDocPath(string $path, string $type): ?string
    {
        $path = mb_ltrim($path, './');

        if (str_starts_with($path, 'docs/')) {
            return base_path($path);
        }

        $sectionBase = match ($type) {
            'action' => 'docs/developer/backend/actions',
            'controller' => 'docs/developer/backend/controllers',
            'page' => 'docs/developer/frontend/pages',
            default => null,
        };

        if ($sectionBase === null) {
            return null;
        }

        return base_path($sectionBase.'/'.$path);
    }

    private function getClassNameFromFile(string $filePath): ?string
    {
        if (! File::exists($filePath)) {
            return null;
        }

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
}
