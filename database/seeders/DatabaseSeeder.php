<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SeederCategory;
use App\Services\SeedingMetrics;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ReflectionClass;

final class DatabaseSeeder extends Seeder
{
    private ?SeedingMetrics $metrics = null;

    /**
     * @param  array<SeederCategory>|null  $categories  Categories to run based on environment.
     * @param  array<string>|null  $only  Specific seeders to run (by class name).
     * @param  array<string>|null  $skip  Seeders to skip (by class name).
     * @param  bool  $strict  Strict mode - fail on errors.
     */
    public function __construct(
        private readonly ?array $categories = null,
        private readonly ?array $only = null,
        private readonly ?array $skip = null,
        private readonly bool $strict = false,
    ) {
        $this->metrics = new SeedingMetrics;
    }

    public function run(): void
    {
        $startTime = microtime(true);
        $categories = $this->categories ?? $this->getDefaultCategories();
        $seeders = $this->discoverSeeders($categories);

        if ($seeders === []) {
            $this->command?->info('No seeders found to run.');

            return;
        }

        $this->command?->info(sprintf('Running %d seeder(s)...', count($seeders)));

        if ($this->strict) {
            $this->command?->warn('Strict mode enabled - will fail on any errors');
        }

        $this->command?->newLine();

        foreach ($seeders as $seeder) {
            $shortName = class_basename($seeder);
            $this->metrics->startSeeder($shortName);

            try {
                $this->command?->info("Running {$shortName}...");
                $this->call($seeder);
                $this->metrics->endSeeder($shortName);
            } catch (Exception $e) {
                $this->metrics->endSeeder($shortName);
                $this->metrics->addError($shortName, $e->getMessage());

                if ($this->strict) {
                    $this->command?->error("Seeder {$shortName} failed: {$e->getMessage()}");
                    throw $e;
                }
                $this->command?->warn("Seeder {$shortName} had errors: {$e->getMessage()}");
                Log::warning("Seeder {$shortName} failed", ['error' => $e->getMessage()]);

            }
        }

        $duration = round(microtime(true) - $startTime, 2);
        $summary = $this->metrics->getSummary();

        $this->command?->newLine();
        $this->command?->info('Seeding completed.');
        $this->command?->line("Duration: {$duration}s");
        $this->command?->line("Records created: {$summary['total_records']}");
        $this->command?->line("Warnings: {$summary['total_warnings']}");
        $this->command?->line("Errors: {$summary['total_errors']}");

        $metricsPath = storage_path('logs/seeding_metrics_'.now()->format('Y-m-d_His').'.json');
        $this->metrics->save($metricsPath);

        Log::info('Seeding completed', [
            'duration' => $duration,
            'seeders' => count($seeders),
            'summary' => $summary,
        ]);
    }

    /**
     * Get default categories based on environment.
     *
     * @return array<SeederCategory>
     */
    private function getDefaultCategories(): array
    {
        $categories = [SeederCategory::Essential];

        if (app()->environment('local', 'testing')) {
            $categories[] = SeederCategory::Development;
        }

        if (app()->environment('production')) {
            $categories[] = SeederCategory::Production;
        }

        return $categories;
    }

    /**
     * Discover seeder classes in the given categories.
     *
     * @param  array<SeederCategory>  $categories
     * @return array<string>
     */
    private function discoverSeeders(array $categories): array
    {
        $seeders = [];

        foreach ($categories as $category) {
            $path = database_path("seeders/{$category->value}");
            $namespace = "Database\\Seeders\\{$category->value}";

            if (! File::isDirectory($path)) {
                continue;
            }

            foreach (File::files($path) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $className = $namespace.'\\'.$file->getBasename('.php');
                if (! class_exists($className) || ! is_subclass_of($className, Seeder::class)) {
                    continue;
                }

                $reflection = new ReflectionClass($className);
                if ($reflection->isAbstract() || $reflection->isInterface()) {
                    continue;
                }

                $shortName = $reflection->getShortName();

                if ($this->shouldSkip($shortName)) {
                    continue;
                }

                if ($this->only !== null && ! in_array($shortName, $this->only, true)) {
                    continue;
                }

                $seeders[] = $className;
            }
        }

        return $this->sortByDependencies($seeders);
    }

    /**
     * Check if a seeder should be skipped.
     */
    private function shouldSkip(string $shortName): bool
    {
        if ($this->skip === null) {
            return false;
        }

        return in_array($shortName, $this->skip, true);
    }

    /**
     * Sort seeders by dependencies.
     *
     * @param  array<string>  $seeders
     * @return array<string>
     */
    private function sortByDependencies(array $seeders): array
    {
        $sorted = [];
        $visited = [];
        $visiting = [];

        foreach ($seeders as $seeder) {
            $this->visitSeeder($seeder, $seeders, $sorted, $visited, $visiting);
        }

        return $sorted;
    }

    /**
     * Visit a seeder and resolve its dependencies.
     *
     * @param  array<string>  $allSeeders
     * @param  array<string>  $sorted
     * @param  array<string, bool>  $visited
     * @param  array<string, bool>  $visiting
     */
    private function visitSeeder(
        string $seeder,
        array $allSeeders,
        array &$sorted,
        array &$visited,
        array &$visiting
    ): void {
        if (isset($visited[$seeder])) {
            return;
        }

        if (isset($visiting[$seeder])) {
            return;
        }

        $visiting[$seeder] = true;

        $dependencies = $this->getDependencies($seeder);

        foreach ($dependencies as $dependency) {
            $fullDependency = $this->resolveDependency($dependency, $allSeeders);

            if ($fullDependency !== null) {
                $this->visitSeeder($fullDependency, $allSeeders, $sorted, $visited, $visiting);
            }
        }

        unset($visiting[$seeder]);
        $visited[$seeder] = true;
        $sorted[] = $seeder;
    }

    /**
     * Get dependencies for a seeder class.
     *
     * @return array<string>
     */
    private function getDependencies(string $seeder): array
    {
        if (! class_exists($seeder)) {
            return [];
        }

        $reflection = new ReflectionClass($seeder);

        if (! $reflection->hasProperty('dependencies')) {
            return [];
        }

        $property = $reflection->getProperty('dependencies');

        $dependencies = $property->getValue(new $seeder);

        if (! is_array($dependencies)) {
            return [];
        }

        return $dependencies;
    }

    /**
     * Resolve a dependency name to a full class name.
     *
     * @param  array<string>  $allSeeders
     */
    private function resolveDependency(string $dependency, array $allSeeders): ?string
    {
        if (class_exists($dependency)) {
            return $dependency;
        }

        foreach ($allSeeders as $seeder) {
            $reflection = new ReflectionClass($seeder);

            if ($reflection->getShortName() === $dependency) {
                return $seeder;
            }
        }

        return null;
    }
}
