<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

/**
 * Scaffolds a complete module: Model, Migration, Factory, Seeder, Actions (CRUD),
 * Controller, Form Requests, Inertia React Pages, Filament Resource, Pest Tests,
 * route registration, and documentation stub.
 */
final class MakeModuleCommand extends Command
{
    protected $signature = 'make:module
                            {name : The model name (e.g. Project, Invoice)}
                            {--panel=admin : Filament panel (admin or system)}
                            {--no-ai : Skip AI seed generation}';

    protected $description = 'Scaffold a complete module with model, actions, controller, pages, tests, and Filament resource';

    private string $model;

    private string $modelVariable;

    private string $modelPlural;

    private string $modelKebab;

    private string $modelKebabPlural;

    private string $modelTitle;

    public function handle(): int
    {
        $this->model = Str::studly($this->argument('name'));
        $this->modelVariable = Str::camel($this->model);
        $this->modelPlural = Str::camel(Str::plural($this->model));
        $this->modelKebab = Str::kebab($this->model);
        $this->modelKebabPlural = Str::kebab(Str::plural($this->model));
        $this->modelTitle = Str::plural(Str::headline($this->model));

        $this->info("Scaffolding module: {$this->model}");
        $this->newLine();

        // 1. Model + Migration + Factory + Seeder + Filament Resource (via make:model:full)
        $this->callModelFull();

        // 2. Actions (Create, Update, Delete)
        $this->createActions();

        // 3. Controller (resource, using Actions)
        $this->createController();

        // 4. Form Requests
        $this->createFormRequests();

        // 5. Inertia React Pages (index, create, edit)
        $this->createPages();

        // 6. Pest Feature Test
        $this->createTest();

        // 7. Route registration
        $this->registerRoute();

        // 8. Documentation stub
        $this->createDocStub();

        $this->newLine();
        $this->info("Module {$this->model} scaffolded successfully!");
        $this->newLine();
        $this->line('  Next steps:');
        $this->line("  1. Edit the migration: <comment>database/migrations/*_create_{$this->modelKebabPlural}_table.php</comment>");
        $this->line("  2. Add fields to form requests: <comment>app/Http/Requests/Store{$this->model}Request.php</comment>");
        $this->line("  3. Customize React pages: <comment>resources/js/pages/{$this->modelKebabPlural}/</comment>");
        $this->line('  4. Run migrations: <comment>php artisan migrate</comment>');
        $this->line('  5. Generate Wayfinder routes: <comment>php artisan wayfinder:generate</comment>');
        $this->line("  6. Run tests: <comment>php artisan test --filter={$this->model}</comment>");

        return self::SUCCESS;
    }

    private function callModelFull(): void
    {
        $options = [
            'name' => $this->model,
            '--all' => true,
            '--no-interaction' => true,
        ];

        if ($this->option('no-ai')) {
            $options['--no-ai'] = true;
        }

        Artisan::call('make:model:full', $options);
        $this->info('  ✓ Model, migration, factory, seeder');

        // Create Filament resource separately (--panel may fail if Filament artisan signature differs)
        try {
            Artisan::call('make:filament-resource', [
                'model' => $this->model,
                '--panel' => $this->option('panel'),
                '--no-interaction' => true,
            ]);
            $this->info("  ✓ Filament resource ({$this->option('panel')} panel)");
        } catch (Throwable $e) {
            $this->warn("  ⊘ Filament resource skipped: {$e->getMessage()}");
        }
    }

    private function createActions(): void
    {
        $actions = ['create-action', 'update-action', 'delete-action'];

        foreach ($actions as $stub) {
            $actionName = match ($stub) {
                'create-action' => "Create{$this->model}",
                'update-action' => "Update{$this->model}",
                'delete-action' => "Delete{$this->model}",
            };

            $path = app_path("Actions/{$actionName}.php");

            if (File::exists($path)) {
                $this->warn("  ⊘ Action already exists: {$actionName}");

                continue;
            }

            $content = $this->renderStub("module/{$stub}.stub");
            File::put($path, $content);
        }

        $this->info("  ✓ Actions: Create{$this->model}, Update{$this->model}, Delete{$this->model}");
    }

    private function createController(): void
    {
        $path = app_path("Http/Controllers/{$this->model}Controller.php");

        if (File::exists($path)) {
            $this->warn("  ⊘ Controller already exists: {$this->model}Controller");

            return;
        }

        File::put($path, $this->renderStub('module/controller.stub'));
        $this->info("  ✓ Controller: {$this->model}Controller");
    }

    private function createFormRequests(): void
    {
        foreach (['Store', 'Update'] as $prefix) {
            $requestName = "{$prefix}{$this->model}Request";
            $path = app_path("Http/Requests/{$requestName}.php");

            if (File::exists($path)) {
                continue;
            }

            Artisan::call('make:request', [
                'name' => $requestName,
                '--no-interaction' => true,
            ]);
        }

        $this->info("  ✓ Form requests: Store{$this->model}Request, Update{$this->model}Request");
    }

    private function createPages(): void
    {
        $pagesDir = resource_path("js/pages/{$this->modelKebabPlural}");

        if (! File::isDirectory($pagesDir)) {
            File::makeDirectory($pagesDir, 0755, true);
        }

        $pages = [
            'index-page.stub' => 'index.tsx',
            'create-page.stub' => 'create.tsx',
            'edit-page.stub' => 'edit.tsx',
        ];

        foreach ($pages as $stub => $filename) {
            $path = "{$pagesDir}/{$filename}";

            if (File::exists($path)) {
                $this->warn("  ⊘ Page already exists: {$filename}");

                continue;
            }

            File::put($path, $this->renderStub("module/{$stub}"));
        }

        $this->info("  ✓ Inertia pages: {$this->modelKebabPlural}/index, create, edit");
    }

    private function createTest(): void
    {
        $testDir = base_path('tests/Feature/Controllers');

        if (! File::isDirectory($testDir)) {
            File::makeDirectory($testDir, 0755, true);
        }

        $path = "{$testDir}/{$this->model}ControllerTest.php";

        if (File::exists($path)) {
            $this->warn("  ⊘ Test already exists: {$this->model}ControllerTest.php");

            return;
        }

        File::put($path, $this->renderStub('module/feature-test.stub'));
        $this->info("  ✓ Feature test: {$this->model}ControllerTest");
    }

    private function registerRoute(): void
    {
        $routesFile = base_path('routes/web.php');
        $content = File::get($routesFile);

        $routeLine = "Route::resource('{$this->modelKebabPlural}', \\App\\Http\\Controllers\\{$this->model}Controller::class);";

        if (Str::contains($content, $this->modelKebabPlural)) {
            $this->warn("  ⊘ Route already registered for {$this->modelKebabPlural}");

            return;
        }

        // Find the auth middleware group and append the route
        $marker = '// === Module routes (auto-registered by make:module) ===';

        if (! Str::contains($content, $marker)) {
            // Add marker and route before the last closing of the auth group
            $content .= "\n\n{$marker}\n";
        }

        $content .= "    {$routeLine}\n";
        File::put($routesFile, $content);

        $this->info("  ✓ Route registered: {$this->modelKebabPlural}");
    }

    private function createDocStub(): void
    {
        $docsDir = base_path('docs/developer/backend/actions');

        if (! File::isDirectory($docsDir)) {
            File::makeDirectory($docsDir, 0755, true);
        }

        $docPath = "{$docsDir}/".Str::lower($this->model).'.md';

        if (File::exists($docPath)) {
            return;
        }

        $content = <<<MD
        # {$this->model} Module

        ## Actions

        - `Create{$this->model}` — Creates a new {$this->model} record
        - `Update{$this->model}` — Updates an existing {$this->model} record
        - `Delete{$this->model}` — Deletes a {$this->model} record

        ## Controller

        `{$this->model}Controller` — Resource controller rendering Inertia pages.

        ## Pages

        - `{$this->modelKebabPlural}/index` — List view with pagination
        - `{$this->modelKebabPlural}/create` — Create form
        - `{$this->modelKebabPlural}/edit` — Edit form

        ## Routes

        | Method | URI | Name |
        |--------|-----|------|
        | GET | /{$this->modelKebabPlural} | {$this->modelKebabPlural}.index |
        | GET | /{$this->modelKebabPlural}/create | {$this->modelKebabPlural}.create |
        | POST | /{$this->modelKebabPlural} | {$this->modelKebabPlural}.store |
        | GET | /{$this->modelKebabPlural}/{id} | {$this->modelKebabPlural}.show |
        | GET | /{$this->modelKebabPlural}/{id}/edit | {$this->modelKebabPlural}.edit |
        | PUT | /{$this->modelKebabPlural}/{id} | {$this->modelKebabPlural}.update |
        | DELETE | /{$this->modelKebabPlural}/{id} | {$this->modelKebabPlural}.destroy |
        MD;

        File::put($docPath, $content);
        $this->info('  ✓ Documentation stub');
    }

    private function renderStub(string $stubPath): string
    {
        $stub = File::get(base_path("stubs/{$stubPath}"));

        return str_replace(
            ['{{ model }}', '{{ modelVariable }}', '{{ modelPlural }}', '{{ modelKebab }}', '{{ modelKebabPlural }}', '{{ modelTitle }}'],
            [$this->model, $this->modelVariable, $this->modelPlural, $this->modelKebab, $this->modelKebabPlural, $this->modelTitle],
            $stub,
        );
    }
}
