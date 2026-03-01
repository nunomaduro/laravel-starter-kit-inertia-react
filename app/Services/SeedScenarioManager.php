<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;

final class SeedScenarioManager
{
    /**
     * Load scenario definitions.
     *
     * @return array<string, array<string, mixed>>
     */
    public function loadScenarios(): array
    {
        $scenariosPath = database_path('seeders/scenarios.json');

        if (! File::exists($scenariosPath)) {
            return [];
        }

        $content = File::get($scenariosPath);

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR) ?? [];
    }

    /**
     * Get scenario definition.
     *
     * @return array<string, mixed>|null
     */
    public function getScenario(string $scenarioName): ?array
    {
        $scenarios = $this->loadScenarios();

        return $scenarios[$scenarioName] ?? null;
    }

    /**
     * Register a scenario.
     */
    public function registerScenario(string $name, array $definition): void
    {
        $scenarios = $this->loadScenarios();
        $scenarios[$name] = $definition;

        $scenariosPath = database_path('seeders/scenarios.json');
        $scenariosDir = dirname($scenariosPath);

        if (! File::isDirectory($scenariosDir)) {
            File::makeDirectory($scenariosDir, 0755, true);
        }

        File::put($scenariosPath, json_encode($scenarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Execute a scenario.
     *
     * @return array<string, mixed>
     */
    public function executeScenario(string $scenarioName): array
    {
        $scenario = $this->getScenario($scenarioName);

        throw_if($scenario === null, InvalidArgumentException::class, "Scenario '{$scenarioName}' not found");

        $results = [];

        foreach ($scenario['models'] ?? [] as $modelConfig) {
            $modelClass = $modelConfig['class'] ?? null;
            if ($modelClass === null) {
                continue;
            }
            if (! class_exists($modelClass)) {
                continue;
            }

            $count = $modelConfig['count'] ?? 1;
            $state = $modelConfig['state'] ?? null;

            $factory = $modelClass::factory();

            if ($state !== null && method_exists($factory, $state)) {
                $factory = $factory->{$state}();
            }

            $results[$modelClass] = $factory->count($count)->create();
        }

        return $results;
    }
}
