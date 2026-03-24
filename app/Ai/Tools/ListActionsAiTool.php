<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use Stringable;
use Throwable;

/**
 * AI tool that lists all Action classes in the project.
 *
 * Read-only — does not modify any files or execute any code.
 */
final class ListActionsAiTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'List all Action classes in the application with their handle() method signatures';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'filter' => $schema->string()->description('Filter actions by name (e.g., "User", "Create", "Employee")'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $filter = $request->get('filter');
        $actions = [];

        $actions = [...$actions, ...$this->scanActions(app_path('Actions'), 'App\\Actions')];

        $modulePaths = File::directories(base_path('modules'));

        foreach ($modulePaths as $modulePath) {
            $actionsDir = $modulePath.'/src/Actions';

            if (File::isDirectory($actionsDir)) {
                $moduleName = basename($modulePath);
                $namespace = 'Modules\\'.Str::studly($moduleName).'\\Actions';
                $actions = [...$actions, ...$this->scanActions($actionsDir, $namespace)];
            }
        }

        if ($filter) {
            $actions = array_filter($actions, fn (array $a): bool => str_contains(mb_strtolower($a['name']), mb_strtolower($filter)));
        }

        if ($actions === []) {
            return 'No actions found.';
        }

        $output = "# Actions\n\n";

        foreach ($actions as $action) {
            $output .= "- **{$action['name']}** (`{$action['class']}`)\n";

            if ($action['signature']) {
                $output .= "  `handle({$action['signature']})`\n";
            }
        }

        return $output;
    }

    /**
     * @return array<int, array{name: string, class: string, signature: string}>
     */
    private function scanActions(string $directory, string $namespace): array
    {
        $actions = [];

        if (! File::isDirectory($directory)) {
            return $actions;
        }

        foreach (File::allFiles($directory) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = Str::before($file->getRelativePathname(), '.php');
            $className = $namespace.'\\'.str_replace('/', '\\', $relativePath);

            if (! class_exists($className)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($className);

                if ($reflection->isAbstract() || ! $reflection->hasMethod('handle')) {
                    continue;
                }

                $handleMethod = $reflection->getMethod('handle');
                $params = collect($handleMethod->getParameters())->map(function (ReflectionParameter $p): string {
                    $type = $p->getType() instanceof ReflectionNamedType ? $p->getType()->getName() : 'mixed';
                    $type = class_basename($type);

                    return "{$type} \${$p->getName()}";
                })->implode(', ');

                $actions[] = [
                    'name' => class_basename($className),
                    'class' => $className,
                    'signature' => $params,
                ];
            } catch (Throwable) {
                continue;
            }
        }

        return $actions;
    }
}
