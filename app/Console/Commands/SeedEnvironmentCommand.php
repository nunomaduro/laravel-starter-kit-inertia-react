<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\SeederCategory;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

final class SeedEnvironmentCommand extends Command
{
    protected $signature = 'seed:environment
                            {--category= : Run specific category only (essential, development, production)}
                            {--only= : Run specific seeder(s) (comma-separated)}
                            {--skip= : Skip specific seeder(s) (comma-separated)}
                            {--fresh : Fresh migrate before seeding}
                            {--force : Force the operation to run in production}
                            {--strict : Strict mode - fail on any errors}
                            {--lenient : Lenient mode - continue on warnings (default in local)}';

    protected $description = 'Seed the database with environment-aware seeders';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Cannot seed in production without --force flag.');

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->info('Running fresh migrations...');
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->info('Migrations completed.');
        }

        $categories = $this->getCategories();
        $only = $this->getOnly();
        $skip = $this->getSkip();
        $strict = $this->option('strict') || (! $this->option('lenient') && app()->environment('production', 'testing'));

        $seeder = new DatabaseSeeder($categories, $only, $skip, $strict);
        $seeder->setCommand($this);
        $seeder->run();

        return self::SUCCESS;
    }

    /**
     * @return array<SeederCategory>|null
     */
    private function getCategories(): ?array
    {
        $category = $this->option('category');

        if ($category === null) {
            return null;
        }

        $category = mb_strtolower($category);

        return match ($category) {
            'essential' => [SeederCategory::Essential],
            'development' => [SeederCategory::Essential, SeederCategory::Development],
            'production' => [SeederCategory::Production],
            default => null,
        };
    }

    /**
     * @return array<string>|null
     */
    private function getOnly(): ?array
    {
        $only = $this->option('only');

        if ($only === null) {
            return null;
        }

        return array_map(trim(...), explode(',', $only));
    }

    /**
     * @return array<string>|null
     */
    private function getSkip(): ?array
    {
        $skip = $this->option('skip');

        if ($skip === null) {
            return null;
        }

        return array_map(trim(...), explode(',', $skip));
    }
}
