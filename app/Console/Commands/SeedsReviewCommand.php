<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ModelRegistry;
use App\Services\PrismService;
use App\Services\SeedSpecGenerator;
use Exception;
use Illuminate\Console\Command;
use Prism\Prism\Enums\Provider;

final class SeedsReviewCommand extends Command
{
    protected $signature = 'seeds:review
                            {--model= : Review specific model only}
                            {--dry-run : Show review prompts without calling AI}
                            {--provider= : AI provider (openai, anthropic, local)}';

    protected $description = 'AI-based review of seeders and specs';

    public function handle(SeedSpecGenerator $specGenerator, ModelRegistry $registry, PrismService $prismService): int
    {
        $specificModel = $this->option('model');
        $dryRun = $this->option('dry-run');
        $provider = $this->option('provider') ?? 'local';

        $models = $specificModel
            ? ['App\Models\\'.$specificModel]
            : $registry->getAllModels();

        if ($models === []) {
            $this->info('No models found.');

            return self::SUCCESS;
        }

        $this->info('Reviewing seeders and specs...');
        $this->newLine();

        if ($dryRun) {
            $this->warn('DRY RUN MODE - Review prompts will be shown but AI will not be called');
            $this->newLine();
        }

        $issues = [];

        foreach ($models as $modelClass) {
            $modelName = class_basename($modelClass);

            try {
                $spec = $specGenerator->loadSpec($modelClass);
                $seederInfo = $registry->hasSeeder($modelClass);

                if ($spec === null) {
                    $issues[] = [
                        'model' => $modelName,
                        'severity' => 'error',
                        'message' => 'Missing seed spec',
                    ];

                    continue;
                }

                if (! $seederInfo['exists']) {
                    $issues[] = [
                        'model' => $modelName,
                        'severity' => 'error',
                        'message' => 'Missing seeder',
                    ];

                    continue;
                }

                $prompt = $this->buildReviewPrompt($modelName, $spec, $seederInfo);

                if ($dryRun) {
                    $this->line(sprintf('  %s Review Prompt:', $modelName));
                    $this->line('  '.str_repeat('-', 60));
                    $this->line($prompt);
                    $this->line('  '.str_repeat('-', 60));
                    $this->newLine();
                } else {
                    $prismProvider = $this->getPrismProvider($provider);
                    $useAI = $prismService->isAvailable($prismProvider);

                    if ($useAI) {
                        $this->line(sprintf('  %s: Using AI review', $modelName));
                        $review = $this->callAIReview($prompt, $provider, $prismService);

                        if ($review !== null) {
                            $this->displayReview($modelName, $review);
                        } else {
                            $this->warn(sprintf('  %s: AI review failed, performing basic validation', $modelName));
                            $this->performBasicValidation($modelName, $spec);
                        }
                    } else {
                        $this->line(sprintf('  %s: AI not available, performing basic validation', $modelName));
                        $this->performBasicValidation($modelName, $spec);
                    }
                }
            } catch (Exception $e) {
                $this->error(sprintf('  %s: Error - %s', $modelName, $e->getMessage()));
            }
        }

        if ($issues !== []) {
            $this->newLine();
            $this->warn('Issues found:');

            foreach ($issues as $issue) {
                $this->line(sprintf('  [%s] %s: %s', $issue['severity'], $issue['model'], $issue['message']));
            }
        }

        return self::SUCCESS;
    }

    private function buildReviewPrompt(string $modelName, array $spec, array $seederInfo): string
    {
        $prompt = "Review the seeding setup for model: {$modelName}\n\n";
        $prompt .= "Seed Spec:\n";
        $prompt .= json_encode($spec, JSON_PRETTY_PRINT)."\n\n";
        $prompt .= "Seeder Category: {$seederInfo['category']}\n\n";
        $prompt .= "Please review:\n";
        $prompt .= "1. Are all relationships properly handled in the seeder?\n";
        $prompt .= "2. Is the seeding logic idempotent (safe to run multiple times)?\n";
        $prompt .= "3. Are the example values in JSON realistic?\n";
        $prompt .= "4. Are there any potential issues or improvements?\n\n";

        return $prompt.'Return a JSON object with: {issues: [], suggestions: []}';
    }

    private function getPrismProvider(string $provider): Provider
    {
        return match ($provider) {
            'openrouter' => Provider::OpenRouter,
            'openai' => Provider::OpenAI,
            'anthropic' => Provider::Anthropic,
            default => Provider::OpenRouter,
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    private function callAIReview(string $prompt, string $provider, PrismService $prismService): ?array
    {
        try {
            $prismProvider = $this->getPrismProvider($provider);

            $model = $prismService->defaultModelForProvider($prismProvider);

            $schema = [
                'type' => 'object',
                'properties' => [
                    'issues' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                    ],
                    'suggestions' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                    ],
                ],
                'required' => ['issues', 'suggestions'],
            ];

            try {
                $jsonData = $prismService->generateStructured($prompt, $schema, $model);
            } catch (Exception) {
                $response = $prismService->using($prismProvider, $model)
                    ->withPrompt($prompt)
                    ->asText();

                $text = $response->text;
                $jsonData = json_decode($text, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $text, $matches)) {
                        $jsonData = json_decode($matches[1], true);
                    } elseif (preg_match('/\{.*\}/s', $text, $matches)) {
                        $jsonData = json_decode($matches[0], true);
                    }
                }

                if ($jsonData === null || json_last_error() !== JSON_ERROR_NONE) {
                    $this->warn('Failed to parse JSON from AI response: '.json_last_error_msg());

                    return null;
                }
            }

            return [
                'issues' => $jsonData['issues'] ?? [],
                'suggestions' => $jsonData['suggestions'] ?? [],
            ];
        } catch (Exception $exception) {
            $this->error('AI call failed: '.$exception->getMessage());

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $review
     */
    private function displayReview(string $modelName, array $review): void
    {
        $this->line(sprintf('  %s:', $modelName));

        $issues = $review['issues'] ?? [];

        if (! empty($issues)) {
            $this->warn('    Issues:');

            foreach ($issues as $issue) {
                $this->line('      - '.$issue);
            }
        }

        $suggestions = $review['suggestions'] ?? [];

        if (! empty($suggestions)) {
            $this->info('    Suggestions:');

            foreach ($suggestions as $suggestion) {
                $this->line('      - '.$suggestion);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $spec
     */
    private function performBasicValidation(string $modelName, array $spec): void
    {
        $issues = [];
        $suggestions = [];

        if (empty($spec['fields'] ?? [])) {
            $issues[] = 'No fields defined in spec';
        }

        $relationships = $spec['relationships'] ?? [];
        if (! empty($relationships)) {
            $suggestions[] = 'Consider verifying relationship seeding in seeder';
        }

        $valueHints = $spec['value_hints'] ?? [];
        if (empty($valueHints)) {
            $suggestions[] = 'Add value hints for better seed data generation';
        }

        if ($issues !== [] || $suggestions !== []) {
            $this->displayReview($modelName, [
                'issues' => $issues,
                'suggestions' => $suggestions,
            ]);
        } else {
            $this->info(sprintf('  %s: Basic validation passed', $modelName));
        }
    }
}
