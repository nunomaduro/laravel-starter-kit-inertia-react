<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AISeedGenerator;
use App\Services\ModelRegistry;
use App\Services\PrismService;
use App\Services\SeedSpecGenerator;
use App\Services\TraditionalSeedGenerator;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;

final class SeedsGenerateAiCommand extends Command
{
    protected $signature = 'seeds:generate-ai
                            {--model= : Generate for specific model only}
                            {--scenario=basic_demo : Scenario to generate (basic_demo, edge_cases, performance, i18n)}
                            {--provider= : AI provider (openai, anthropic, local)}
                            {--api-key= : API key for AI provider}
                            {--dry-run : Show prompts without calling AI}';

    protected $description = 'Generate seed JSON data using AI (offline, curated)';

    public function handle(
        SeedSpecGenerator $specGenerator,
        AISeedGenerator $aiGenerator,
        ModelRegistry $registry,
        PrismService $prismService,
        TraditionalSeedGenerator $traditionalGenerator
    ): int {
        $specificModel = $this->option('model');
        $scenario = $this->option('scenario');
        $provider = $this->option('provider') ?? 'local';
        $dryRun = $this->option('dry-run');

        $models = $specificModel
            ? ["App\\Models\\{$specificModel}"]
            : $registry->getAllModels();

        if ($models === []) {
            $this->info('No models found.');

            return self::SUCCESS;
        }

        $this->info("Generating AI seed data (scenario: {$scenario})...");
        $this->newLine();

        if ($dryRun) {
            $this->warn('DRY RUN MODE - Prompts will be shown but AI will not be called');
            $this->newLine();
        }

        foreach ($models as $modelClass) {
            $modelName = class_basename($modelClass);

            try {
                $spec = $specGenerator->loadSpec($modelClass);

                if ($spec === null) {
                    $this->warn("  {$modelName}: No spec found (run seeds:spec-sync first)");

                    continue;
                }

                $profile = $aiGenerator->loadProfile($modelClass);

                if ($profile === null) {
                    // Generate profile from spec
                    $profile = $aiGenerator->generateProfile($modelClass, $spec);
                    $aiGenerator->saveProfile($modelClass, $profile);
                    $this->info("  {$modelName}: Created AI profile");
                }

                $prompt = $aiGenerator->buildPrompt($spec, $profile, $scenario);

                if ($dryRun) {
                    $this->line("  {$modelName} Prompt:");
                    $this->line('  '.str_repeat('-', 60));
                    $this->line($prompt);
                    $this->line('  '.str_repeat('-', 60));
                    $this->newLine();
                } else {
                    // Check if AI is available
                    $prismProvider = $this->getPrismProvider($provider);
                    $useAI = $prismService->isAvailable($prismProvider);

                    if ($useAI) {
                        $this->line("  {$modelName}: Using AI generation");
                        $jsonData = $this->callAI($prompt, $provider, $prismService);

                        if ($jsonData !== null) {
                            $this->saveGeneratedJson($modelName, $jsonData, $scenario, 'ai');
                            $this->info("  {$modelName}: Generated JSON data with AI");
                        } else {
                            $this->warn("  {$modelName}: AI generation failed, falling back to traditional method");
                            $jsonData = $this->fallbackToTraditional($spec, $traditionalGenerator);
                            if ($jsonData !== null) {
                                $this->saveGeneratedJson($modelName, $jsonData, $scenario, 'traditional');
                                $this->info("  {$modelName}: Generated JSON data with Faker");
                            }
                        }
                    } else {
                        $this->line("  {$modelName}: AI not available, using traditional Faker generation");
                        $jsonData = $this->fallbackToTraditional($spec, $traditionalGenerator);
                        if ($jsonData !== null) {
                            $this->saveGeneratedJson($modelName, $jsonData, $scenario, 'traditional');
                            $this->info("  {$modelName}: Generated JSON data with Faker");
                        }
                    }
                }
            } catch (Exception $e) {
                $this->error("  {$modelName}: Error - {$e->getMessage()}");
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info('Dry run complete. Run without --dry-run to generate data.');
        } else {
            $this->info('AI generation complete. Review generated JSON files before committing.');
        }

        return self::SUCCESS;
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
     * @return array<int, array<string, mixed>>|null
     */
    private function callAI(string $prompt, string $provider, PrismService $prismService): ?array
    {
        try {
            $prismProvider = $this->getPrismProvider($provider);

            $model = $prismService->defaultModelForProvider($prismProvider);

            $schema = [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                ],
            ];

            try {
                $jsonData = $prismService->generateStructured($prompt, $schema, $model);

                throw_unless(is_array($jsonData), Exception::class, 'Structured output did not return array');
            } catch (Exception $e) {
                $this->line('  Using text output (structured not available)');
                $response = $prismService->using($prismProvider, $model)
                    ->withPrompt($prompt)
                    ->asText();

                $text = $response->text;
                $jsonData = json_decode($text, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    if (preg_match('/```(?:json)?\s*(\[.*?\])/s', $text, $matches)) {
                        $jsonData = json_decode($matches[1], true);
                    } elseif (preg_match('/\[.*\]/s', $text, $matches)) {
                        $jsonData = json_decode($matches[0], true);
                    }
                }

                if ($jsonData === null || json_last_error() !== JSON_ERROR_NONE) {
                    $this->warn('Failed to parse JSON from AI response: '.json_last_error_msg());

                    return null;
                }
            }

            if (isset($jsonData['data']) && is_array($jsonData['data'])) {
                return $jsonData['data'];
            }

            if (isset($jsonData[0]) && is_array($jsonData[0])) {
                return $jsonData;
            }

            if (is_array($jsonData) && ! isset($jsonData[0])) {
                return [$jsonData];
            }

            return null;
        } catch (Exception $e) {
            $this->error('AI call failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $spec
     * @return array<int, array<string, mixed>>|null
     */
    private function fallbackToTraditional(array $spec, TraditionalSeedGenerator $generator): ?array
    {
        try {
            return $generator->generate($spec, 5);
        } catch (Exception $e) {
            $this->error('Traditional generation failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $jsonData
     */
    private function saveGeneratedJson(string $modelName, array $jsonData, string $scenario, string $source = 'ai'): void
    {
        $jsonKey = Str::snake(Str::plural($modelName));
        $jsonPath = database_path("seeders/data/{$jsonKey}.json");

        $existingData = [];

        if (File::exists($jsonPath)) {
            $existingContent = File::get($jsonPath);
            $existingData = json_decode($existingContent, true) ?? [];
        }

        if (! isset($existingData['_scenarios'])) {
            $existingData['_scenarios'] = [];
        }

        $existingData['_scenarios'][$scenario] = $jsonData;
        $existingData['_source'] = $source;
        $existingData['_generated_at'] = now()->toIso8601String();
        $existingData['_auto_generated'] = true;

        if ($scenario === 'basic_demo') {
            $existingData[$jsonKey] = $jsonData;
        }

        File::put($jsonPath, json_encode($existingData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
