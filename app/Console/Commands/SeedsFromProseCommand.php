<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PrismService;
use App\Services\SeedSpecGenerator;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;

final class SeedsFromProseCommand extends Command
{
    protected $signature = 'seeds:from-prose
                            {description : Natural language description of the model/domain}
                            {--model= : Model name to generate spec for}
                            {--dry-run : Show generated spec without saving}
                            {--provider= : AI provider (openai, anthropic, local)}';

    protected $description = 'Generate seed spec from natural language description';

    public function handle(SeedSpecGenerator $specGenerator, PrismService $prismService): int
    {
        $description = $this->argument('description');
        $modelName = $this->option('model');
        $dryRun = $this->option('dry-run');
        $provider = $this->option('provider') ?? 'local';

        if ($modelName === null) {
            $this->error('Model name is required. Use --model=ModelName');

            return self::FAILURE;
        }

        $this->info('Generating seed spec from description...');
        $this->newLine();

        $prompt = $this->buildProsePrompt($description, $modelName);

        if ($dryRun) {
            $this->line('Prompt:');
            $this->line(str_repeat('-', 60));
            $this->line($prompt);
            $this->line(str_repeat('-', 60));
            $this->newLine();
            $this->info('Dry run complete. Run without --dry-run to generate spec.');
        } else {
            $prismProvider = $this->getPrismProvider($provider);
            $useAI = $prismService->isAvailable($prismProvider);

            if ($useAI) {
                $this->line('Using AI to generate spec from description');
                $spec = $this->callAIForSpec($prompt, $provider, $modelName, $prismService);
            } else {
                $this->warn('AI not available - generating basic spec structure');
                $spec = $this->generateBasicSpec($modelName, $description);
            }

            if ($spec !== null) {
                $modelClass = 'App\Models\\'.$modelName;

                if (class_exists($modelClass)) {
                    $specGenerator->saveSpec($modelClass, $spec);
                    $this->info('Seed spec generated for '.$modelName);
                } else {
                    $this->warn(sprintf('Model %s does not exist. Spec would be generated when model is created.', $modelName));
                }
            } else {
                $this->error('Failed to generate spec from description.');
            }
        }

        return self::SUCCESS;
    }

    private function buildProsePrompt(string $description, string $modelName): string
    {
        $prompt = "Convert this natural language description into a seed spec JSON structure:\n\n";
        $prompt .= sprintf('Description: %s%s', $description, PHP_EOL);
        $prompt .= "Model Name: {$modelName}\n\n";
        $prompt .= "Generate a JSON seed spec with:\n";
        $prompt .= "- fields: array of field definitions (name, type, nullable, default)\n";
        $prompt .= "- relationships: array of relationship definitions (type, model)\n";
        $prompt .= "- value_hints: array of example values\n";
        $prompt .= "- scenarios: array of scenario names\n\n";

        return $prompt.'Return only valid JSON matching the seed spec format.';
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
     * @return array<string, mixed>
     */
    private function generateBasicSpec(string $modelName, string $description): array
    {
        return [
            'model' => $modelName,
            'table' => Str::snake(Str::plural($modelName)),
            'fields' => [],
            'relationships' => [],
            'value_hints' => [],
            'scenarios' => ['basic_demo'],
            '_note' => sprintf('Basic spec generated from description: %s. Run seeds:spec-sync to populate from actual model/migration.', $description),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function callAIForSpec(string $prompt, string $provider, string $modelName, PrismService $prismService): ?array
    {
        try {
            $prismProvider = $this->getPrismProvider($provider);

            // Use model from config based on provider
            $model = $prismService->defaultModelForProvider($prismProvider);

            // Define schema for seed spec
            $schema = [
                'type' => 'object',
                'properties' => [
                    'model' => ['type' => 'string'],
                    'table' => ['type' => 'string'],
                    'fields' => [
                        'type' => 'object',
                        'additionalProperties' => [
                            'type' => 'object',
                            'properties' => [
                                'type' => ['type' => 'string'],
                                'nullable' => ['type' => 'boolean'],
                                'default' => [],
                            ],
                        ],
                    ],
                    'relationships' => [
                        'type' => 'object',
                        'additionalProperties' => [
                            'type' => 'object',
                            'properties' => [
                                'type' => ['type' => 'string'],
                                'model' => ['type' => 'string'],
                            ],
                        ],
                    ],
                    'value_hints' => [
                        'type' => 'object',
                        'additionalProperties' => true,
                    ],
                    'scenarios' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                    ],
                ],
                'required' => ['model', 'table', 'fields', 'relationships'],
            ];

            try {
                $jsonData = $prismService->generateStructured($prompt, $schema, $model);
            } catch (Exception) {
                // Fallback to text parsing
                $this->line('  Using text output (structured not available)');
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

                    return $this->generateBasicSpec($modelName, '');
                }
            }

            // Ensure required fields are present
            $jsonData['model'] ??= $modelName;
            $jsonData['table'] ??= Str::snake(Str::plural($modelName));

            return $jsonData;
        } catch (Exception $exception) {
            $this->warn('AI call failed: '.$exception->getMessage());
            $this->info('Falling back to basic spec structure');

            return $this->generateBasicSpec($modelName, '');
        }
    }
}
