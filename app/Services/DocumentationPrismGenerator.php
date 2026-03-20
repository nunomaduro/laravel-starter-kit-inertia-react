<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use Throwable;

use function Laravel\Ai\agent;

final readonly class DocumentationPrismGenerator
{
    public function __construct(
        private PrismService $prism,
        private DocumentationPromptGenerator $promptGenerator,
        private DocumentationTemplateSelector $templateSelector,
    ) {}

    /**
     * Generate Action documentation and write to file.
     *
     * @param  array<string, mixed>  $actionInfo
     * @param  array<string, mixed>  $relationships
     * @return string|null Written file path or null on failure
     */
    public function generateActionDoc(string $actionName, array $actionInfo, array $relationships): ?string
    {
        $templatePath = $this->templateSelector->getTemplatePath(
            $this->templateSelector->selectActionTemplate($actionInfo)
        );

        if (! File::exists($templatePath)) {
            return null;
        }

        $prompt = $this->promptGenerator->buildMinimalActionPrompt($actionInfo, $relationships, $templatePath);
        $markdown = $this->callAi($prompt);

        if ($markdown === null || $markdown === '') {
            return null;
        }

        $outputPath = base_path('docs/developer/backend/actions/'.mb_strtolower($actionName).'.md');
        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $this->normalizeMarkdown($markdown));

        return $outputPath;
    }

    /**
     * Generate Controller documentation and write to file.
     *
     * @param  array<string, mixed>  $controllerInfo
     * @param  array<string, mixed>  $relationships
     * @return string|null Written file path or null on failure
     */
    public function generateControllerDoc(string $controllerName, array $controllerInfo, array $relationships): ?string
    {
        $templatePath = $this->templateSelector->getTemplatePath(
            $this->templateSelector->selectControllerTemplate($controllerInfo)
        );

        if (! File::exists($templatePath)) {
            return null;
        }

        $prompt = $this->promptGenerator->buildMinimalControllerPrompt($controllerInfo, $relationships, $templatePath);
        $markdown = $this->callAi($prompt);

        if ($markdown === null || $markdown === '') {
            return null;
        }

        $outputPath = base_path('docs/developer/backend/controllers/'.mb_strtolower($controllerName).'.md');
        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $this->normalizeMarkdown($markdown));

        return $outputPath;
    }

    /**
     * Generate Page documentation and write to file.
     *
     * @param  array<string, mixed>  $pageInfo
     * @param  array<string, mixed>  $relationships
     * @return string|null Written file path or null on failure
     */
    public function generatePageDoc(string $pagePath, array $pageInfo, array $relationships): ?string
    {
        $templatePath = $this->templateSelector->getTemplatePath(
            $this->templateSelector->selectPageTemplate($pageInfo)
        );

        if (! File::exists($templatePath)) {
            return null;
        }

        $prompt = $this->promptGenerator->buildMinimalPagePrompt($pageInfo, $relationships, $templatePath);
        $markdown = $this->callAi($prompt);

        if ($markdown === null || $markdown === '') {
            return null;
        }

        $outputPath = base_path(sprintf('docs/developer/frontend/pages/%s.md', $pagePath));
        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $this->normalizeMarkdown($markdown));

        return $outputPath;
    }

    public function isAvailable(): bool
    {
        return $this->prism->isAvailable();
    }

    private function callAi(string $prompt): ?string
    {
        try {
            return agent(instructions: 'You are a technical documentation writer. Generate only markdown content, no explanations.')
                ->prompt($prompt)
                ->text;
        } catch (Throwable) {
            return null;
        }
    }

    private function normalizeMarkdown(string $markdown): string
    {
        $markdown = mb_trim($markdown);

        if (preg_match('/^```(?:markdown|md)?\s*\n(.*)\n```\s*$/s', $markdown, $m)) {
            return mb_trim($m[1]);
        }

        return $markdown;
    }
}
