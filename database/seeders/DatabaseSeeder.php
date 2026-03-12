<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SeederCategory;
use App\Providers\SettingsOverlayServiceProvider;
use App\Services\SeedingMetrics;
use App\Settings\SetupWizardSettings;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
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

        if (app()->environment('local', 'testing') && in_array(SeederCategory::Development, $categories, true)) {
            $this->applyHerdDefaultsForSeeding();
        }

        config(['tenancy.seed_in_progress' => true]);

        $this->command?->info(sprintf('Running %d seeder(s)...', count($seeders)));

        if ($this->strict) {
            $this->command?->warn('Strict mode enabled - will fail on any errors');
        }

        $this->command?->newLine();

        try {
            foreach ($seeders as $seeder) {
                $shortName = class_basename($seeder);
                $this->metrics->startSeeder($shortName);

                try {
                    $this->command?->info(sprintf('Running %s...', $shortName));
                    $this->call($seeder);
                    $this->metrics->recordCreated($shortName, 'Seeder', 1);
                    $this->metrics->endSeeder($shortName);
                } catch (Exception $e) {
                    $this->metrics->endSeeder($shortName);
                    $this->metrics->addError($shortName, $e->getMessage());

                    if ($this->strict) {
                        $this->command?->error(sprintf('Seeder %s failed: %s', $shortName, $e->getMessage()));
                        throw $e;
                    }

                    $this->command?->warn(sprintf('Seeder %s had errors: %s', $shortName, $e->getMessage()));
                    Log::warning(sprintf('Seeder %s failed', $shortName), ['error' => $e->getMessage()]);

                }
            }
        } finally {
            config(['tenancy.seed_in_progress' => false]);
        }

        $duration = round(microtime(true) - $startTime, 2);
        $summary = $this->metrics->getSummary();

        $this->command?->newLine();
        $this->command?->info('Seeding completed.');
        $this->command?->line(sprintf('Duration: %ss', $duration));
        $this->command?->line('Records created: '.$summary['total_records']);
        $this->command?->line('Warnings: '.$summary['total_warnings']);
        $this->command?->line('Errors: '.$summary['total_errors']);

        $metricsPath = storage_path('logs/seeding_metrics_'.now()->format('Y-m-d_His').'.json');
        $this->metrics->save($metricsPath);

        Log::info('Seeding completed', [
            'duration' => $duration,
            'seeders' => count($seeders),
            'summary' => $summary,
        ]);

        if (
            app()->environment('local', 'testing')
            && in_array(SeederCategory::Development, $categories, true)
            && (int) ($summary['total_errors'] ?? 0) === 0
        ) {
            if (app()->environment('local')) {
                $this->enableAllFeaturesForLocalDevelopment();
            }
            $this->markSetupCompleteAfterDevelopmentSeeding();
        }
    }

    /**
     * After migrate:fresh --seed on local, ensure every Pennant feature is on so the app
     * (sidebar, routes, Filament) is fully visible without manual Filament toggles.
     */
    private function enableAllFeaturesForLocalDevelopment(): void
    {
        Feature::purge();
        Feature::flushCache();

        if (DB::getSchemaBuilder()->hasTable('feature_segments')) {
            DB::table('feature_segments')->delete();
        }

        $classes = array_unique(array_merge(
            array_values(config('feature-flags.inertia_features', [])),
            array_values(config('feature-flags.route_feature_map', [])),
        ));

        foreach ($classes as $class) {
            if (is_string($class) && class_exists($class)) {
                Feature::activateForEveryone($class, true);
            }
        }

        $this->command?->info('All feature flags activated for local development.');
    }

    /**
     * Use collection Scout driver during seed so Typesense is not required locally.
     */
    private function applyHerdDefaultsForSeeding(): void
    {
        config(['scout.driver' => 'collection']);
    }

    /**
     * Mark setup wizard complete after successful development seed (local/testing).
     */
    private function markSetupCompleteAfterDevelopmentSeeding(): void
    {
        $wizard = resolve(SetupWizardSettings::class);
        $wizard->setup_completed = true;
        $wizard->save();
        SettingsOverlayServiceProvider::applyOverlay();
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
            $path = database_path('seeders/'.$category->value);
            $namespace = 'Database\Seeders\\'.$category->value;

            if (! File::isDirectory($path)) {
                continue;
            }

            foreach (File::files($path) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $className = $namespace.'\\'.$file->getBasename('.php');
                if (! class_exists($className)) {
                    continue;
                }

                if (! is_subclass_of($className, Seeder::class)) {
                    continue;
                }

                $reflection = new ReflectionClass($className);
                if ($reflection->isAbstract()) {
                    continue;
                }

                if ($reflection->isInterface()) {
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
