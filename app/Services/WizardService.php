<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use Throwable;

/**
 * Shared logic for the project wizard (CLI + web).
 *
 * Loads domain templates, analyzes project descriptions via AI,
 * and configures the AI assistant with domain knowledge.
 */
final readonly class WizardService
{
    public function __construct(
        private AIService $aiService,
    ) {}

    /**
     * Load all available domain templates.
     *
     * @return array<string, array<string, mixed>>
     */
    public function loadDomains(): array
    {
        $domains = [];
        $domainPath = resource_path('ai/domains');

        if (! File::isDirectory($domainPath)) {
            return $domains;
        }

        foreach (File::files($domainPath) as $file) {
            if ($file->getExtension() !== 'json') {
                continue;
            }

            $data = json_decode(File::get($file->getPathname()), true);

            if (is_array($data) && isset($data['slug'])) {
                $domains[$data['slug']] = $data;
            }
        }

        return $domains;
    }

    /**
     * Use AI to recommend modules based on a project description.
     *
     * @return array{slugs: array<int, string>, reasoning: string}
     */
    public function analyzeDescription(string $description): array
    {
        $domains = $this->loadDomains();

        if (! $this->aiService->isAvailable() || $domains === []) {
            return ['slugs' => [], 'reasoning' => 'AI unavailable — select modules manually.'];
        }

        $domainList = collect($domains)->map(fn (array $d): string => sprintf(
            '- %s (%s): %s',
            $d['name'],
            $d['slug'],
            $d['description'],
        ))->implode("\n");

        $prompt = <<<PROMPT
        Analyze this project description and recommend which modules to install.

        Available modules:
        {$domainList}

        Project description: "{$description}"

        Reply with a JSON object with two keys:
        - "slugs": array of recommended module slugs (e.g., ["hr", "crm"])
        - "reasoning": one sentence explaining why these modules fit

        If none match, return {"slugs": [], "reasoning": "No modules match this description."}
        PROMPT;

        try {
            $response = $this->aiService->chat(
                new \App\Ai\Agents\AssistantAgent,
                $prompt,
            );

            $text = $response->text ?? '';

            if (preg_match('/\{.*\}/s', $text, $matches)) {
                $parsed = json_decode($matches[0], true);

                if (is_array($parsed) && isset($parsed['slugs'])) {
                    return [
                        'slugs' => array_values(array_filter($parsed['slugs'], fn ($s): bool => is_string($s) && isset($domains[$s]))),
                        'reasoning' => $parsed['reasoning'] ?? 'AI recommended these modules based on your description.',
                    ];
                }
            }
        } catch (Throwable) {
            // Fall through
        }

        return ['slugs' => [], 'reasoning' => 'Could not analyze description. Select modules manually.'];
    }

    /**
     * Get a preview of what will be generated for the selected modules.
     *
     * @param  array<int, string>  $selectedSlugs
     * @return array<string, mixed>
     */
    public function preview(array $selectedSlugs): array
    {
        $domains = $this->loadDomains();
        $modules = [];
        $totalModels = 0;
        $totalPages = 0;

        foreach ($selectedSlugs as $slug) {
            $domain = $domains[$slug] ?? null;

            if (! $domain) {
                continue;
            }

            $modelCount = count($domain['terminology'] ?? []);
            $totalModels += $modelCount;
            $totalPages += $modelCount * 3; // list + create + edit per model

            $modules[] = [
                'slug' => $slug,
                'name' => $domain['name'],
                'description' => $domain['description'],
                'models' => $modelCount,
                'features' => $domain['suggested_features'] ?? [],
            ];
        }

        return [
            'modules' => $modules,
            'summary' => [
                'total_modules' => count($modules),
                'total_models' => $totalModels,
                'total_pages' => $totalPages,
                'includes' => [
                    'Multi-tenancy (organization-based)',
                    'AI Chat Assistant (with domain knowledge)',
                    'Billing & Subscriptions',
                    'Filament Admin Panel',
                    'Authentication & 2FA',
                    'Feature Flags',
                    'Real-time Broadcasting',
                ],
            ],
        ];
    }

    /**
     * Save the AI assistant context for selected modules.
     *
     * @param  array<int, string>  $selectedSlugs
     */
    public function configureAIContext(string $description, array $selectedSlugs): void
    {
        $domains = $this->loadDomains();
        $contextFile = storage_path('app/ai-context.json');

        $context = [
            'project_description' => $description,
            'configured_at' => now()->toIso8601String(),
            'domains' => [],
        ];

        foreach ($selectedSlugs as $slug) {
            $domain = $domains[$slug] ?? null;

            if (! $domain) {
                continue;
            }

            $context['domains'][] = [
                'name' => $domain['name'],
                'slug' => $slug,
                'terminology' => $domain['terminology'] ?? [],
                'common_queries' => $domain['common_queries'] ?? [],
                'example_interactions' => $domain['example_interactions'] ?? [],
            ];
        }

        File::ensureDirectoryExists(dirname($contextFile));
        File::put($contextFile, json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
