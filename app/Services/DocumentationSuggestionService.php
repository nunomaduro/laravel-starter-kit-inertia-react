<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;

final readonly class DocumentationSuggestionService
{
    /**
     * Analyze code and suggest documentation needs.
     *
     * @param  array<string, mixed>  $componentInfo
     * @return array<string, mixed>
     */
    public function suggestDocumentation(array $componentInfo, string $componentType): array
    {
        $suggestions = [
            'userGuide' => false,
            'developerGuide' => true, // Always needed
            'examples' => [],
            'faqs' => [],
            'relatedDocs' => [],
        ];

        return match ($componentType) {
            'action' => array_merge($suggestions, $this->suggestActionDocumentation($componentInfo)),
            'controller' => array_merge($suggestions, $this->suggestControllerDocumentation($componentInfo)),
            'page' => array_merge($suggestions, $this->suggestPageDocumentation($componentInfo)),
            default => $suggestions,
        };
    }

    /**
     * Generate AI suggestion prompt.
     *
     * @param  array<string, mixed>  $suggestions
     */
    public function generateSuggestionPrompt(
        array $suggestions,
        string $componentName,
        string $componentType
    ): string {
        $prompt = "Analyze the following {$componentType} and suggest documentation improvements:\n\n";
        $prompt .= "Component: {$componentName}\n\n";

        $prompt .= "Current Suggestions:\n";
        if ($suggestions['userGuide']) {
            $prompt .= "- User guide documentation needed\n";
        }

        if ($suggestions['developerGuide']) {
            $prompt .= "- Developer guide documentation needed\n";
        }

        if (! empty($suggestions['examples'])) {
            $prompt .= '- Examples needed: '.implode(', ', $suggestions['examples'])."\n";
        }

        if (! empty($suggestions['faqs'])) {
            $prompt .= '- Potential FAQs: '.implode(', ', $suggestions['faqs'])."\n";
        }

        if (! empty($suggestions['relatedDocs'])) {
            $prompt .= '- Related documentation: '.implode(', ', $suggestions['relatedDocs'])."\n";
        }

        return $prompt."\nProvide specific suggestions for improving documentation for this component.";
    }

    /**
     * Suggest documentation for Actions.
     *
     * @param  array<string, mixed>  $actionInfo
     * @return array<string, mixed>
     */
    private function suggestActionDocumentation(array $actionInfo): array
    {
        $suggestions = [
            'examples' => [],
            'faqs' => [],
        ];

        // Check complexity
        $paramCount = count($actionInfo['handleMethod']['parameters'] ?? []);
        $dependencyCount = count($actionInfo['dependencies'] ?? []);

        // Suggest examples if complex
        if ($paramCount > 2 || $dependencyCount > 0) {
            $suggestions['examples'][] = 'Usage example from Controller';
            $suggestions['examples'][] = 'Usage example from Job/Command';
        }

        // Check for error handling
        if (isset($actionInfo['phpDoc']['class']['parsed']['throws'])) {
            $suggestions['faqs'][] = 'What exceptions can this action throw?';
        }

        // Check relationships
        $relationships = $actionInfo['relationships'] ?? [];
        if (! empty($relationships['usedBy'])) {
            $suggestions['relatedDocs'][] = 'Related Controllers: '.implode(', ', $relationships['usedBy']);
        }

        return $suggestions;
    }

    /**
     * Suggest documentation for Controllers.
     *
     * @param  array<string, mixed>  $controllerInfo
     * @return array<string, mixed>
     */
    private function suggestControllerDocumentation(array $controllerInfo): array
    {
        $suggestions = [
            'userGuide' => $this->isUserFacing($controllerInfo),
            'examples' => [],
            'faqs' => [],
        ];

        $methodCount = count($controllerInfo['methods'] ?? []);

        // Suggest API documentation if many methods
        if ($methodCount >= 5) {
            $suggestions['examples'][] = 'API endpoint examples';
        }

        // Check for user-facing features
        $relationships = $controllerInfo['relationships'] ?? [];
        if (! empty($relationships['rendersPages'])) {
            $suggestions['userGuide'] = true;
            $suggestions['examples'][] = 'User workflow examples';
        }

        return $suggestions;
    }

    /**
     * Suggest documentation for Pages.
     *
     * @param  array<string, mixed>  $pageInfo
     * @return array<string, mixed>
     */
    private function suggestPageDocumentation(array $pageInfo): array
    {
        $suggestions = [
            'userGuide' => true, // Pages are usually user-facing
            'examples' => [],
            'faqs' => [],
        ];

        $propsCount = count($pageInfo['tsDoc']['props'] ?? []);

        // Suggest prop documentation if many props
        if ($propsCount >= 3) {
            $suggestions['examples'][] = 'Props usage examples';
        }

        // Check for complex interactions
        if (isset($pageInfo['filePath'])) {
            $content = File::get($pageInfo['filePath']);
            if (str_contains($content, 'useForm') || str_contains($content, 'useState')) {
                $suggestions['examples'][] = 'Form handling examples';
            }
        }

        return $suggestions;
    }

    /**
     * Check if controller is user-facing.
     *
     * @param  array<string, mixed>  $controllerInfo
     */
    private function isUserFacing(array $controllerInfo): bool
    {
        $relationships = $controllerInfo['relationships'] ?? [];

        // If it renders pages, it's user-facing
        if (! empty($relationships['rendersPages'])) {
            return true;
        }

        // Check route names for user-facing patterns
        $routes = $relationships['relatedRoutes'] ?? [];
        foreach ($routes as $route) {
            if (str_contains((string) $route, 'settings') ||
                str_contains((string) $route, 'profile') ||
                str_contains((string) $route, 'dashboard')) {
                return true;
            }
        }

        return false;
    }
}
