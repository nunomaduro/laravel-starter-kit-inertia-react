<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\SeederCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class SeedersListCommand extends Command
{
    protected $signature = 'seeders:list
                            {--category= : Filter by category}
                            {--json : Output as JSON}';

    protected $description = 'List all available seeders with their status';

    public function handle(): int
    {
        $category = $this->option('category');
        $categories = $category ? [SeederCategory::from(mb_strtolower($category))] : SeederCategory::cases();

        $seeders = [];

        foreach ($categories as $cat) {
            $path = database_path('seeders/'.$cat->value);

            if (! File::isDirectory($path)) {
                continue;
            }

            $files = File::files($path);

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $seederName = $file->getBasename('.php');
                $jsonFile = database_path('seeders/data/'.Str::snake(Str::plural(Str::before($seederName, 'Seeder'))).'.json');
                $hasJson = File::exists($jsonFile);

                $seeders[] = [
                    'name' => $seederName,
                    'category' => $cat->value,
                    'has_json' => $hasJson,
                    'path' => $file->getRelativePathname(),
                ];
            }
        }

        if ($this->option('json')) {
            $this->line(json_encode($seeders, JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        if ($seeders === []) {
            $this->info('No seeders found.');

            return self::SUCCESS;
        }

        $this->info('Available Seeders:');
        $this->newLine();

        $headers = ['Name', 'Category', 'JSON Data', 'Path'];
        $rows = [];

        foreach ($seeders as $seeder) {
            $rows[] = [
                $seeder['name'],
                $seeder['category'],
                $seeder['has_json'] ? '✓' : '✗',
                $seeder['path'],
            ];
        }

        $this->table($headers, $rows);

        return self::SUCCESS;
    }
}
