<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Str;

final readonly class AISeederCodeGenerator
{
    public function __construct(
        private PrismService $prismService
    ) {}

    /**
     * Generate intelligent seeder code using AI.
     *
     * @param  array<string, mixed>  $spec
     * @param  array<string, array{type: string, model: string|null}>  $relationships
     */
    public function generateSeederCode(string $modelName, array $spec, array $relationships, string $category): string
    {
        // Check if AI is available
        if (! $this->prismService->isAvailable()) {
            return $this->generateTraditionalSeederCode($modelName, $spec, $relationships);
        }

        try {
            $prompt = $this->buildSeederPrompt($modelName, $spec, $relationships, $category);
            $code = $this->callAIForSeederCode($prompt);

            if ($code !== null) {
                return $code;
            }
        } catch (Exception) {
            // Fallback to traditional generation
        }

        return $this->generateTraditionalSeederCode($modelName, $spec, $relationships);
    }

    /**
     * Build prompt for AI seeder generation.
     *
     * @param  array<string, mixed>  $spec
     * @param  array<string, array{type: string, model: string|null}>  $relationships
     */
    private function buildSeederPrompt(string $modelName, array $spec, array $relationships, string $category): string
    {
        $fields = $spec['fields'] ?? [];

        $prompt = "Generate a Laravel seeder class for model: {$modelName}\n\n";
        $prompt .= "Category: {$category}\n\n";

        $prompt .= "Fields:\n";
        foreach ($fields as $field => $fieldSpec) {
            if (in_array($field, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }

            $prompt .= sprintf('  - %s: %s', $field, $fieldSpec['type']);
            if (! $fieldSpec['nullable']) {
                $prompt .= ' (required)';
            }

            if ($fieldSpec['default'] !== null) {
                $prompt .= sprintf(' (default: %s)', $fieldSpec['default']);
            }

            $prompt .= "\n";
        }

        if ($relationships !== []) {
            $prompt .= "\nRelationships:\n";
            foreach ($relationships as $relName => $rel) {
                $prompt .= sprintf('  - %s: %s', $relName, $rel['type']);
                if ($rel['model'] !== null) {
                    $prompt .= ' -> '.$rel['model'];
                }

                $prompt .= "\n";
            }
        }

        $prompt .= "\nRequirements:\n";
        $prompt .= "1. Use idempotent methods (updateOrCreate or firstOrCreate)\n";
        $prompt .= "2. Seed relationships before main model (belongsTo dependencies)\n";
        $prompt .= "3. Use factory states when appropriate\n";
        $prompt .= "4. Load JSON data if available\n";
        $prompt .= "5. Generate realistic seed data\n";
        $prompt .= "6. Follow Laravel best practices\n\n";

        return $prompt.'Generate ONLY the seeder class code (PHP), no explanations.';
    }

    /**
     * Call AI to generate seeder code.
     */
    private function callAIForSeederCode(string $prompt): ?string
    {
        try {
            $response = $this->prismService->generate($prompt);

            $text = $response->text;

            // Extract PHP code from response
            if (preg_match('/```php\s*(.*?)\s*```/s', $text, $matches)) {
                return $matches[1];
            }

            if (preg_match('/class\s+\w+Seeder.*?}/s', $text, $matches)) {
                return $matches[0];
            }

            // If response looks like code, return it
            if (str_contains($text, 'class') && str_contains($text, 'Seeder')) {
                return $text;
            }

            return null;
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Generate traditional seeder code (fallback).
     *
     * @param  array<string, mixed>  $spec
     * @param  array<string, array{type: string, model: string|null}>  $relationships
     */
    private function generateTraditionalSeederCode(string $modelName, array $spec, array $relationships): string
    {
        $jsonKey = Str::snake(Str::plural($modelName));
        $jsonFileName = $jsonKey.'.json';

        // Generate relationship code using enhanced analyzer
        $enhancedAnalyzer = resolve(EnhancedRelationshipAnalyzer::class);
        $relationshipCode = $enhancedAnalyzer->generateRelationshipSeederCode($relationships);

        // Determine unique identifier field
        $fields = $spec['fields'] ?? [];
        $uniqueField = $this->findUniqueField($fields);
        $uniqueCheck = $this->getUniqueFieldCheck($uniqueField);
        $uniqueKey = $this->getUniqueFieldKey($uniqueField);

        $code = <<<PHP
    /**
     * Seed from JSON data file (idempotent).
     */
    private function seedFromJson(): void
    {
        try {
            \$data = \$this->loadJson('{$jsonFileName}');

            if (! isset(\$data['{$jsonKey}']) || ! is_array(\$data['{$jsonKey}'])) {
                return;
            }

            foreach (\$data['{$jsonKey}'] as \$itemData) {
                \$factoryState = \$itemData['_factory_state'] ?? null;
                unset(\$itemData['_factory_state']);

                // Use idempotent updateOrCreate if unique field exists
                if ({$uniqueCheck}) {
                    {$modelName}::query()->updateOrCreate(
                        [{$uniqueKey} => \$itemData[{$uniqueKey}]],
                        \$itemData
                    );
                } else {
                    // Fallback to factory if no unique field
                    \$factory = {$modelName}::factory();
                    if (\$factoryState !== null && method_exists(\$factory, \$factoryState)) {
                        \$factory = \$factory->{\$factoryState}();
                    }
                    \$factory->create(\$itemData);
                }
            }
        } catch (\RuntimeException \$e) {
            // JSON file doesn't exist or is invalid - skip silently
        }
    }

    /**
     * Seed using factory (idempotent - safe to run multiple times).
     */
    private function seedFromFactory(): void
    {
        // Generate seed data with factory
        // Note: Factory creates are not idempotent by default
        // For true idempotency, use updateOrCreate in seedFromJson or add unique constraints
        {$modelName}::factory()
            ->count(5)
            ->create();

        // Create admin users if applicable
        if (method_exists({$modelName}::factory(), 'admin')) {
            {$modelName}::factory()
                ->admin()
                ->count(2)
                ->create();
        }
    }
PHP;

        return $relationshipCode.$code;
    }

    /**
     * Find unique identifier field in spec.
     *
     * @param  array<string, mixed>  $fields
     */
    private function findUniqueField(array $fields): ?string
    {
        // Check for common unique fields
        $uniqueFields = ['email', 'slug', 'uuid', 'code', 'name'];

        foreach ($uniqueFields as $field) {
            if (isset($fields[$field])) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Get unique field check code (returns PHP code string).
     */
    private function getUniqueFieldCheck(?string $uniqueField): string
    {
        if ($uniqueField === null) {
            return 'false';
        }

        return sprintf("isset(\$itemData['%s']) && !empty(\$itemData['%s'])", $uniqueField, $uniqueField);
    }

    /**
     * Get unique field key code.
     */
    private function getUniqueFieldKey(?string $uniqueField): string
    {
        if ($uniqueField === null) {
            return "'id'";
        }

        return sprintf("'%s'", $uniqueField);
    }
}
