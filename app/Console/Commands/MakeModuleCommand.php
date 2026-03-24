<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\ModuleLoader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Scaffolds a complete module directory structure under modules/.
 *
 * Generates: service provider, model, policy, action, controller, feature flag,
 * migration, factory, routes, and tests. Adds the module to config/modules.php
 * and registers the PSR-4 autoload entry in composer.json.
 */
#[AsCommand(name: 'make:module')]
final class MakeModuleCommand extends Command
{
    /**
     * Map of stub file => relative output path within the module directory.
     * Placeholders in paths are replaced at runtime.
     *
     * @var array<string, string>
     */
    private const STUB_MAP = [
        'module.json.stub' => 'module.json',
        'composer.json.stub' => 'composer.json',
        'service-provider.stub' => 'src/Providers/{{ ModuleName }}ModuleServiceProvider.php',
        'feature.stub' => 'src/Features/{{ ModuleName }}Feature.php',
        'model.stub' => 'src/Models/{{ ModuleName }}.php',
        'policy.stub' => 'src/Policies/{{ ModuleName }}Policy.php',
        'action.stub' => 'src/Actions/Create{{ ModuleName }}.php',
        'controller.stub' => 'src/Http/Controllers/{{ ModuleName }}Controller.php',
        'routes.stub' => 'routes/web.php',
        'migration.stub' => 'database/migrations/{{ timestamp }}_create_{{ module_name }}_tables.php',
        'factory.stub' => 'database/Factories/{{ ModuleName }}Factory.php',
        'feature-test.stub' => 'tests/Feature/{{ ModuleName }}ControllerTest.php',
        'unit-test.stub' => 'tests/Unit/{{ ModuleName }}Test.php',
    ];

    protected $signature = 'make:module {name : The module name (e.g. Inventory or inventory)}';

    protected $description = 'Scaffold a complete module directory structure under modules/';

    public function handle(): int
    {
        $rawName = (string) $this->argument('name');
        $moduleName = Str::kebab($rawName);
        $studlyName = Str::studly($rawName);
        $snakeName = Str::snake($rawName);
        $label = str_replace(['-', '_'], ' ', Str::title($moduleName));
        $modelVariable = Str::camel($rawName);
        $modelVariablePlural = Str::plural($modelVariable);

        $modulePath = base_path("modules/{$moduleName}");

        if (File::isDirectory($modulePath)) {
            $this->components->error("Module directory [modules/{$moduleName}] already exists.");

            return self::FAILURE;
        }

        $modules = ModuleLoader::all();

        if (array_key_exists($moduleName, $modules)) {
            $this->components->error("Module [{$moduleName}] already exists in config/modules.php.");

            return self::FAILURE;
        }

        $this->components->info("Scaffolding module [{$studlyName}] at modules/{$moduleName}/...");
        $this->newLine();

        $replacements = [
            '{{ moduleName }}' => $moduleName,
            '{{ ModuleName }}' => $studlyName,
            '{{ module_name }}' => $snakeName,
            '{{ moduleLabel }}' => $label,
            '{{ modelVariable }}' => $modelVariable,
            '{{ modelVariablePlural }}' => $modelVariablePlural,
            '{{ timestamp }}' => now()->format('Y_m_d_His'),
        ];

        $this->generateFiles($modulePath, $replacements);
        $this->newLine();
        $this->addToModulesConfig($modules, $moduleName);
        $this->addComposerAutoload($studlyName, $moduleName);
        $this->runComposerDumpAutoload();

        $this->newLine();
        $this->components->info("Module [{$studlyName}] scaffolded successfully.");
        $this->line('  Directory: modules/'.$moduleName.'/');
        $this->line('  Provider:  Modules\\'.$studlyName.'\\Providers\\'.$studlyName.'ModuleServiceProvider');
        $this->newLine();
        $this->components->info('Next steps:');
        $this->line('  1. Enable the module: php artisan module:enable '.$moduleName);
        $this->line('  2. Run migrations:    php artisan migrate');
        $this->line('  3. Customize the generated files to fit your needs.');

        return self::SUCCESS;
    }

    /**
     * Generate all module files from stubs.
     *
     * @param  array<string, string>  $replacements
     */
    private function generateFiles(string $modulePath, array $replacements): void
    {
        $stubsPath = base_path('stubs/make-module');

        foreach (self::STUB_MAP as $stub => $outputPath) {
            $outputPath = $this->applyReplacements($outputPath, $replacements);
            $fullOutputPath = $modulePath.'/'.$outputPath;

            File::ensureDirectoryExists(dirname($fullOutputPath));

            $stubContent = File::get($stubsPath.'/'.$stub);
            $content = $this->applyReplacements($stubContent, $replacements);

            File::put($fullOutputPath, $content);

            $this->components->twoColumnDetail(
                $outputPath,
                '<fg=green;options=bold>CREATED</>'
            );
        }
    }

    /**
     * Apply placeholder replacements to a string.
     *
     * @param  array<string, string>  $replacements
     */
    private function applyReplacements(string $content, array $replacements): string
    {
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Add the new module to config/modules.php (disabled by default).
     *
     * @param  array<string, bool>  $modules
     */
    private function addToModulesConfig(array $modules, string $moduleName): void
    {
        $modules[$moduleName] = false;
        ModuleLoader::writeConfig($modules);

        $this->components->twoColumnDetail(
            "config/modules.php ('{$moduleName}' => false)",
            '<fg=green;options=bold>UPDATED</>'
        );
    }

    /**
     * Add PSR-4 autoload entry to the root composer.json.
     */
    private function addComposerAutoload(string $studlyName, string $moduleName): void
    {
        $composerPath = base_path('composer.json');

        if (! File::exists($composerPath)) {
            return;
        }

        $contents = File::get($composerPath);

        /** @var array<string, mixed>|null $composer */
        $composer = json_decode($contents, true);

        if (! is_array($composer)) {
            $this->components->warn('Failed to parse composer.json. Add the PSR-4 entry manually.');

            return;
        }

        $namespace = "Modules\\{$studlyName}\\";
        $path = "modules/{$moduleName}/src/";

        if (! isset($composer['autoload']['psr-4'])) {
            $composer['autoload']['psr-4'] = [];
        }

        if (isset($composer['autoload']['psr-4'][$namespace])) {
            return;
        }

        $composer['autoload']['psr-4'][$namespace] = $path;

        $json = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json !== false) {
            File::put($composerPath, $json."\n");
        }

        $this->components->twoColumnDetail(
            "composer.json ({$namespace} => {$path})",
            '<fg=green;options=bold>UPDATED</>'
        );
    }

    private function runComposerDumpAutoload(): void
    {
        $this->components->info('Running composer dump-autoload...');
        Process::run('composer dump-autoload');
    }
}
