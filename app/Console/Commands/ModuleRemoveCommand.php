<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\ModuleLoader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Console\Attribute\AsCommand;

use function Laravel\Prompts\confirm;

#[AsCommand(name: 'module:remove')]
final class ModuleRemoveCommand extends Command
{
    protected $signature = 'module:remove
        {name : The module name to remove}
        {--force : Skip confirmation}
        {--rollback : Rollback module migrations before removing}
        {--keep-migrations : Keep migration files in place}';

    protected $description = 'Permanently remove a module from the project';

    public function handle(): int
    {
        $name = (string) $this->argument('name');

        $modules = ModuleLoader::all();

        if (! array_key_exists($name, $modules)) {
            $this->components->error("Module [{$name}] does not exist in config/modules.php.");
            $this->line('Available modules: '.implode(', ', array_keys($modules)));

            return self::FAILURE;
        }

        if (! $this->option('force')) {
            $confirmed = confirm(
                label: "Are you sure you want to permanently remove module [{$name}]? This cannot be undone.",
                default: false,
            );

            if (! $confirmed) {
                $this->components->info('Module removal cancelled.');

                return self::SUCCESS;
            }
        }

        // Resolve the module namespace from module.json before we delete anything
        $namespace = $this->resolveModuleNamespace($name);

        // Step 1: Disable the module if currently enabled
        if ($modules[$name] === true) {
            $this->components->info("Disabling module [{$name}] before removal...");
            $modules[$name] = false;
            ModuleLoader::writeConfig($modules);
            $this->call('config:clear');
        }

        // Step 2: Rollback migrations if requested
        if ($this->option('rollback')) {
            $migrationPath = "modules/{$name}/database/migrations/";

            if (File::isDirectory(base_path($migrationPath))) {
                $this->components->info('Rolling back module migrations...');
                $this->call('migrate:rollback', [
                    '--path' => $migrationPath,
                    '--force' => true,
                ]);
            } else {
                $this->components->warn("No migrations directory found at [{$migrationPath}]. Skipping rollback.");
            }
        }

        // Step 3: Remove frontend pages
        $frontendPath = resource_path("js/pages/{$name}");

        if (File::isDirectory($frontendPath)) {
            File::deleteDirectory($frontendPath);
            $this->components->info("Removed frontend pages at [resources/js/pages/{$name}/].");
        }

        // Step 4: Remove the module directory
        $modulePath = base_path("modules/{$name}");

        if (File::isDirectory($modulePath)) {
            if ($this->option('keep-migrations')) {
                // Delete everything except database/migrations
                $this->deleteDirectoryExceptMigrations($modulePath);
                $this->components->info("Removed module directory [modules/{$name}/] (migrations kept).");
            } else {
                File::deleteDirectory($modulePath);
                $this->components->info("Removed module directory [modules/{$name}/].");
            }
        } else {
            $this->components->warn("Module directory [modules/{$name}/] not found. It may have already been removed.");
        }

        // Step 5: Remove PSR-4 entries from composer.json
        $this->removeComposerAutoloadEntries($namespace);

        // Step 6: Remove module entry from config/modules.php
        unset($modules[$name]);
        ModuleLoader::writeConfig($modules);
        $this->components->info("Removed [{$name}] from config/modules.php.");

        // Step 7: Remove provider entries from bootstrap/providers.php
        $this->removeBootstrapProviderEntries($namespace);

        // Step 8: Run composer dump-autoload
        $this->components->info('Running composer dump-autoload...');
        Process::run('composer dump-autoload');

        // Step 9: Clear config cache
        $this->call('config:clear');

        // Step 10: Scan for remaining references
        $this->scanForRemainingReferences($namespace, $name);

        $this->newLine();
        $this->components->info("Module [{$name}] has been permanently removed.");

        return self::SUCCESS;
    }

    /**
     * Resolve the module's PHP namespace from its module.json manifest.
     */
    private function resolveModuleNamespace(string $name): string
    {
        $manifest = ModuleLoader::readManifest($name);

        if ($manifest !== null && isset($manifest['provider']) && is_string($manifest['provider'])) {
            // e.g. "Modules\\Blog\\BlogServiceProvider" → "Modules\\Blog\\"
            $provider = $manifest['provider'];
            $lastBackslash = mb_strrpos($provider, '\\');

            if ($lastBackslash !== false) {
                return mb_substr($provider, 0, $lastBackslash + 1);
            }
        }

        // Fallback: derive from module name (e.g., "blog" → "Modules\\Blog\\")
        $studlyName = str_replace(['-', '_'], '', ucwords($name, '-_'));

        return "Modules\\{$studlyName}\\";
    }

    /**
     * Remove PSR-4 autoload entries matching the module namespace from composer.json.
     */
    private function removeComposerAutoloadEntries(string $namespace): void
    {
        $composerPath = base_path('composer.json');

        if (! File::exists($composerPath)) {
            $this->components->warn('composer.json not found. Skipping autoload cleanup.');

            return;
        }

        $contents = File::get($composerPath);

        /** @var array<string, mixed>|null $composer */
        $composer = json_decode($contents, true);

        if (! is_array($composer)) {
            $this->components->error('Failed to parse composer.json.');

            return;
        }

        $removed = false;

        foreach (['autoload', 'autoload-dev'] as $section) {
            if (! isset($composer[$section]['psr-4']) || ! is_array($composer[$section]['psr-4'])) {
                continue;
            }

            foreach (array_keys($composer[$section]['psr-4']) as $key) {
                if (str_starts_with((string) $key, $namespace) || $namespace === (string) $key) {
                    unset($composer[$section]['psr-4'][$key]);
                    $this->components->info("Removed PSR-4 entry [{$key}] from composer.json [{$section}].");
                    $removed = true;
                }
            }
        }

        if ($removed) {
            $json = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if ($json !== false) {
                File::put($composerPath, $json."\n");
            }
        } else {
            $this->components->warn("No PSR-4 entries found for namespace [{$namespace}] in composer.json.");
        }
    }

    /**
     * Remove hard-coded provider entries from bootstrap/providers.php.
     */
    private function removeBootstrapProviderEntries(string $namespace): void
    {
        $providersPath = base_path('bootstrap/providers.php');

        if (! File::exists($providersPath)) {
            return;
        }

        $contents = File::get($providersPath);
        $originalContents = $contents;

        // Escape the namespace for regex (backslashes need extra escaping)
        $escapedNamespace = preg_quote($namespace, '/');

        // Remove lines matching the module's namespace (e.g., "    Modules\Blog\BlogServiceProvider::class,")
        $contents = (string) preg_replace(
            '/\s*.*'.str_replace('\\\\', '\\\\\\\\', $escapedNamespace).'.*::class,?\s*\n/m',
            "\n",
            $contents,
        );

        // Clean up any resulting double blank lines
        $contents = (string) preg_replace('/\n{3,}/', "\n\n", $contents);

        if ($contents !== $originalContents) {
            File::put($providersPath, $contents);
            $this->components->info('Removed provider entries from bootstrap/providers.php.');
        }
    }

    /**
     * Delete a module directory but keep the database/migrations subdirectory intact.
     */
    private function deleteDirectoryExceptMigrations(string $modulePath): void
    {
        $migrationsPath = $modulePath.'/database/migrations';
        $tempPath = sys_get_temp_dir().'/module_migrations_'.uniqid();

        // Move migrations to temp if they exist
        $hasMigrations = File::isDirectory($migrationsPath);

        if ($hasMigrations) {
            File::moveDirectory($migrationsPath, $tempPath);
        }

        // Delete the entire module directory
        File::deleteDirectory($modulePath);

        // Restore migrations
        if ($hasMigrations) {
            File::ensureDirectoryExists($migrationsPath);
            File::moveDirectory($tempPath, $migrationsPath);
        }
    }

    /**
     * Scan for remaining references to the module namespace in key directories.
     */
    private function scanForRemainingReferences(string $namespace, string $name): void
    {
        $directories = ['app', 'config', 'routes'];
        $references = [];

        // Escape namespace for use in string search (e.g., "Modules\Blog\" → "Modules\\Blog\\")
        $searchTerms = [
            $namespace,
            mb_rtrim($namespace, '\\'),
            "modules/{$name}",
        ];

        foreach ($directories as $directory) {
            $dirPath = base_path($directory);

            if (! File::isDirectory($dirPath)) {
                continue;
            }

            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            foreach (File::allFiles($dirPath) as $file) {
                $contents = $file->getContents();

                foreach ($searchTerms as $term) {
                    if (str_contains($contents, $term)) {
                        $references[] = $file->getRelativePathname();

                        break;
                    }
                }
            }
        }

        if ($references !== []) {
            $this->newLine();
            $this->components->warn('Remaining references to this module were found in:');

            foreach (array_unique($references) as $ref) {
                $this->line("  - {$ref}");
            }

            $this->components->warn('Please review and update these files manually.');
        }
    }
}
