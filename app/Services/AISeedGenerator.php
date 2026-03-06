<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class AISeedGenerator
{
    /**
     * Generate AI profile for a model.
     *
     * @return array<string, mixed>
     */
    public function generateProfile(string $modelClass, array $spec): array
    {
        $modelName = class_basename($modelClass);

        return [
            'model' => $modelName,
            'domain_description' => $this->generateDomainDescription($spec),
            'locale' => config('app.locale', 'en'),
            'tone' => 'professional',
            'field_semantics' => $this->extractFieldSemantics($spec),
            'scenarios' => ['basic_demo', 'edge_cases'],
        ];
    }

    /**
     * Build AI prompt for generating seed data.
     */
    public function buildPrompt(array $spec, array $profile, string $scenario = 'basic_demo'): string
    {
        $model = $spec['model'] ?? 'Model';
        $fields = $spec['fields'] ?? [];
        $relationships = $spec['relationships'] ?? [];

        $prompt = "Generate realistic seed data for a {$model} model.\n\n";
        $prompt .= sprintf('Domain: %s%s', $profile['domain_description'], PHP_EOL);
        $prompt .= sprintf('Locale: %s%s', $profile['locale'], PHP_EOL);
        $prompt .= "Scenario: {$scenario}\n\n";

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

            if (isset($fieldSpec['enum'])) {
                $prompt .= sprintf(' (enum: %s)', $fieldSpec['enum']);
            }

            $prompt .= "\n";
        }

        if (! empty($relationships)) {
            $prompt .= "\nRelationships:\n";

            foreach ($relationships as $relName => $relSpec) {
                $prompt .= sprintf('  - %s: %s%s', $relName, $relSpec['type'], PHP_EOL);
            }
        }

        return $prompt."\nGenerate 5-10 example records as JSON array. Return only valid JSON.";
    }

    /**
     * Save AI profile.
     */
    public function saveProfile(string $modelClass, array $profile): void
    {
        $modelName = class_basename($modelClass);
        $profilesDir = database_path('seeders/profiles');

        if (! File::isDirectory($profilesDir)) {
            File::makeDirectory($profilesDir, 0755, true);
        }

        $profilePath = sprintf('%s/%s.json', $profilesDir, $modelName);

        File::put($profilePath, json_encode($profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Load AI profile.
     *
     * @return array<string, mixed>|null
     */
    public function loadProfile(string $modelClass): ?array
    {
        $modelName = class_basename($modelClass);
        $profilePath = database_path(sprintf('seeders/profiles/%s.json', $modelName));

        if (! File::exists($profilePath)) {
            return null;
        }

        $content = File::get($profilePath);

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Generate domain description from spec.
     */
    private function generateDomainDescription(array $spec): string
    {
        $model = $spec['model'] ?? 'Model';
        $relationships = $spec['relationships'] ?? [];
        $desc = 'A '.$model;

        if (! empty($relationships)) {
            $relDescs = [];

            foreach ($relationships as $relName => $relSpec) {
                $type = $relSpec['type'] ?? '';
                $description = match ($type) {
                    'belongsTo' => 'belongs to '.$relName,
                    'hasMany' => 'has many '.$relName,
                    'belongsToMany' => 'belongs to many '.$relName,
                    default => null,
                };

                if ($description !== null) {
                    $relDescs[] = $description;
                }
            }

            if ($relDescs !== []) {
                $desc .= ' that '.implode(', ', $relDescs);
            }
        }

        return $desc.'.';
    }

    /**
     * Extract field semantics from spec.
     *
     * @return array<string, mixed>
     */
    private function extractFieldSemantics(array $spec): array
    {
        $semantics = [];
        $fields = $spec['fields'] ?? [];
        $valueHints = $spec['value_hints'] ?? [];

        foreach ($fields as $field => $fieldSpec) {
            $semantic = [
                'field' => $field,
                'type' => $fieldSpec['type'] ?? 'string',
            ];

            if (isset($fieldSpec['enum'])) {
                $semantic['enum'] = $fieldSpec['enum'];
            }

            if (isset($valueHints[$field])) {
                $semantic['hint'] = $valueHints[$field];
            }

            // Infer semantics from field name
            if (Str::contains($field, 'status')) {
                $semantic['semantic'] = 'status';
            } elseif (Str::contains($field, 'email')) {
                $semantic['semantic'] = 'email';
            } elseif (Str::contains($field, 'url')) {
                $semantic['semantic'] = 'url';
            } elseif (Str::contains($field, 'price') || Str::contains($field, 'amount')) {
                $semantic['semantic'] = 'currency';
            }

            $semantics[$field] = $semantic;
        }

        return $semantics;
    }
}
