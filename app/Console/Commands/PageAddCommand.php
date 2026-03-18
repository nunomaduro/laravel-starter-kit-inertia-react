<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class PageAddCommand extends Command
{
    protected $signature = 'page:add
        {name : The page path, e.g. settings/notifications or marketing/landing}
        {--force : Overwrite existing files}';

    protected $description = 'Scaffold a new Inertia + React page with its Laravel controller';

    public function handle(): int
    {
        $name = $this->argument('name');

        // Normalize to forward slashes and strip leading/trailing slashes
        $pagePath = mb_trim(str_replace('\\', '/', $name), '/');

        // Derive parts
        $segments = explode('/', $pagePath);
        $pageBaseName = Str::studly(end($segments));
        $namespace = $this->resolveNamespace($segments);
        $className = $pageBaseName.'Controller';
        $componentPath = implode('/', array_map(Str::kebab(...), $segments));
        $routeName = implode('.', array_map(fn ($s) => Str::snake($s), $segments));
        $routePath = implode('/', array_map(Str::kebab(...), $segments));

        $this->newLine();
        $this->line(sprintf('  <fg=blue>Scaffolding page:</> <fg=white;options=bold>%s</>', $pagePath));
        $this->newLine();

        // Generate the TSX page file
        $tsxSuccess = $this->generateTsxPage($componentPath, $pageBaseName, $pagePath);

        // Generate the PHP controller
        $phpSuccess = $this->generateController($segments, $namespace, $className, $componentPath);

        // Print route snippet
        if ($tsxSuccess && $phpSuccess) {
            $this->printRouteSnippet($namespace, $className, $routePath, $routeName);
        }

        return self::SUCCESS;
    }

    private function generateTsxPage(string $componentPath, string $componentName, string $pagePath): bool
    {
        $targetPath = resource_path(sprintf('js/pages/%s.tsx', $componentPath));
        $targetDir = dirname($targetPath);

        if (! $this->option('force') && File::exists($targetPath)) {
            $this->components->warn(sprintf('TSX page already exists: resources/js/pages/%s.tsx', $componentPath));

            return false;
        }

        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $stub = File::get(base_path('stubs/page/page.stub'));
        $pageTitle = implode(' ', array_map(Str::title(...), explode('/', $pagePath)));

        $content = str_replace(
            ['{{ ComponentName }}', '{{ PageTitle }}', '{{ routePath }}'],
            [$componentName, $pageTitle, $componentPath],
            $stub
        );

        File::put($targetPath, $content);
        $this->components->info(sprintf('TSX page created: resources/js/pages/%s.tsx', $componentPath));

        return true;
    }

    private function generateController(array $segments, string $namespace, string $className, string $componentPath): bool
    {
        $relPath = implode('/', array_map(Str::studly(...), $segments));
        $targetPath = app_path(sprintf('Http/Controllers/%sController.php', $relPath));
        $targetDir = dirname($targetPath);

        if (! $this->option('force') && File::exists($targetPath)) {
            $this->components->warn(sprintf('Controller already exists: app/Http/Controllers/%sController.php', $relPath));

            return false;
        }

        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $stub = File::get(base_path('stubs/page/controller.stub'));

        $content = str_replace(
            ['{{ Namespace }}', '{{ ClassName }}', '{{ component }}'],
            [$namespace, $className, $componentPath],
            $stub
        );

        File::put($targetPath, $content);
        $this->components->info(sprintf('Controller created: app/Http/Controllers/%sController.php', $relPath));

        return true;
    }

    /**
     * @param  array<string>  $segments
     */
    private function resolveNamespace(array $segments): string
    {
        $base = 'App\\Http\\Controllers';

        if (count($segments) <= 1) {
            return $base;
        }

        $sub = implode('\\', array_map(Str::studly(...), array_slice($segments, 0, -1)));

        return sprintf('%s\%s', $base, $sub);
    }

    private function printRouteSnippet(string $namespace, string $className, string $routePath, string $routeName): void
    {
        $useStatement = str_replace('App\\Http\\Controllers\\', '', sprintf('%s\%s', $namespace, $className));

        $this->newLine();
        $this->line('  <fg=yellow;options=bold>Add this route to routes/web.php:</>');
        $this->newLine();
        $this->line(sprintf('  <fg=gray>use App\Http\Controllers\%s;</>', $useStatement));
        $this->newLine();
        $this->line(sprintf("  <fg=cyan>Route::get('%s', %s::class)->name('%s');</>", $routePath, $className, $routeName));
        $this->newLine();
        $this->line('  <fg=gray>Then run:</> <fg=white>npm run build</>');
        $this->newLine();
    }
}
