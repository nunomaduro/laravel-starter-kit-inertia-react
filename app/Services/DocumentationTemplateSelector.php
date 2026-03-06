<?php

declare(strict_types=1);

namespace App\Services;

final readonly class DocumentationTemplateSelector
{
    /**
     * Select appropriate template for an Action.
     *
     * @param  array<string, mixed>  $actionInfo
     */
    public function selectActionTemplate(array $actionInfo): string
    {
        $complexity = $this->calculateActionComplexity($actionInfo);

        if ($complexity < 3) {
            return 'action-simple';
        }

        if ($complexity >= 5) {
            return 'action-detailed';
        }

        return 'action';
    }

    /**
     * Select appropriate template for a Controller.
     *
     * @param  array<string, mixed>  $controllerInfo
     */
    public function selectControllerTemplate(array $controllerInfo): string
    {
        $methodCount = count($controllerInfo['methods'] ?? []);
        $routeCount = count($controllerInfo['relationships']['relatedRoutes'] ?? []);

        if ($routeCount >= 5 || $methodCount >= 5) {
            return 'controller-api';
        }

        return 'controller';
    }

    /**
     * Select appropriate template for a Page.
     *
     * @param  array<string, mixed>  $pageInfo
     */
    public function selectPageTemplate(array $pageInfo): string
    {
        $propsCount = count($pageInfo['tsDoc']['props'] ?? []);

        if ($propsCount >= 5) {
            return 'page-detailed';
        }

        return 'page';
    }

    /**
     * Get template path for a template name.
     */
    public function getTemplatePath(string $templateName): string
    {
        $basePath = base_path('docs/.templates');
        $path = sprintf('%s/%s.md', $basePath, $templateName);

        return file_exists($path) ? $path : $basePath.'/action.md';
    }

    /**
     * Calculate complexity score for an Action.
     *
     * @param  array<string, mixed>  $actionInfo
     */
    private function calculateActionComplexity(array $actionInfo): int
    {
        $complexity = 0;

        $complexity += count($actionInfo['dependencies'] ?? []);
        $complexity += count($actionInfo['handleMethod']['parameters'] ?? []);

        $relationships = $actionInfo['relationships'] ?? [];
        if (! empty($relationships['usedBy'])) {
            $complexity += 1;
        }

        if (! empty($relationships['usesModels'])) {
            $complexity += 1;
        }

        if (isset($actionInfo['phpDoc']['class']['parsed']['throws'])) {
            $complexity += count($actionInfo['phpDoc']['class']['parsed']['throws']);
        }

        return $complexity;
    }
}
