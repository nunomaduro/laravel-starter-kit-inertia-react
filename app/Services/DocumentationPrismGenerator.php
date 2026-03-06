<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use Prism\Prism\Text\Response as TextResponse;
use Throwable;

final readonly class DocumentationPrismGenerator
{
    public function __construct(
        private PrismService $prism,
        private DocumentationPromptGenerator $promptGenerator,
        private DocumentationTemplateSelector $templateSelector
    ) {}

    /**
     * Generate Action documentation using Prism and write to file.
     *
     * @param  array<string, mixed>  $actionInfo
     * @param  array<string, mixed>  $relationships
     * @return string|null Written file path or null on failure
     */
    public function generateActionDoc(
        string $actionName,
        array $actionInfo,
        array $relationships
    ): ?string {
        $templateName = $this->templateSelector->selectActionTemplate($actionInfo);
        $templatePath = $this->templateSelector->getTemplatePath($templateName);

        if (! File::exists($templatePath)) {
            return null;
        }

        $prompt = $this->promptGenerator->buildMinimalActionPrompt($actionInfo, $relationships, $templatePath);
        $markdown = $this->callPrism($prompt);

        if ($markdown === null || $markdown === '') {
            return null;
        }

        $outputPath = base_path('docs/developer/backend/actions/'.mb_strtolower($actionName).'.md');
        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $this->normalizeMarkdown($markdown));

        return $outputPath;
    }

    /**
     * Generate Controller documentation using Prism and write to file.
     *
     * @param  array<string, mixed>  $controllerInfo
     * @param  array<string, mixed>  $relationships
     * @return string|null Written file path or null on failure
     */
    public function generateControllerDoc(
        string $controllerName,
        array $controllerInfo,
        array $relationships
    ): ?string {
        $templateName = $this->templateSelector->selectControllerTemplate($controllerInfo);
        $templatePath = $this->templateSelector->getTemplatePath($templateName);

        if (! File::exists($templatePath)) {
            return null;
        }

        $prompt = $this->promptGenerator->buildMinimalControllerPrompt($controllerInfo, $relationships, $templatePath);
        $markdown = $this->callPrism($prompt);

        if ($markdown === null || $markdown === '') {
            return null;
        }

        $outputPath = base_path('docs/developer/backend/controllers/'.mb_strtolower($controllerName).'.md');
        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $this->normalizeMarkdown($markdown));

        return $outputPath;
    }

    /**
     * Generate Page documentation using Prism and write to file.
     *
     * @param  array<string, mixed>  $pageInfo
     * @param  array<string, mixed>  $relationships
     * @return string|null Written file path or null on failure
     */
    public function generatePageDoc(
        string $pagePath,
        array $pageInfo,
        array $relationships
    ): ?string {
        $templateName = $this->templateSelector->selectPageTemplate($pageInfo);
        $templatePath = $this->templateSelector->getTemplatePath($templateName);

        if (! File::exists($templatePath)) {
            return null;
        }

        $prompt = $this->promptGenerator->buildMinimalPagePrompt($pageInfo, $relationships, $templatePath);
        $markdown = $this->callPrism($prompt);

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

    /**
     * Call Prism to generate text.
     */
    private function callPrism(string $prompt): ?string
    {
        try {
            $response = $this->prism->generate($prompt);

            return $response instanceof TextResponse ? $response->text : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Strip optional markdown code fence around the doc body.
     */
    private function normalizeMarkdown(string $markdown): string
    {
        $markdown = mb_trim($markdown);

        if (preg_match('/^```(?:markdown|md)?\s*\n(.*)\n```\s*$/s', $markdown, $m)) {
            return mb_trim($m[1]);
        }

        return $markdown;
    }
}
