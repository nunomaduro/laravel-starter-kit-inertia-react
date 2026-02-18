<?php

declare(strict_types=1);

namespace App\Providers;

use Eznix86\AI\Memory\MemoryServiceProvider as BaseMemoryServiceProvider;
use Illuminate\Support\Facades\Schema;

/**
 * Extends Laravel AI Memory to load migrations only when using PostgreSQL.
 * This keeps the test suite working with SQLite (phpunit.xml default).
 */
final class MemoryServiceProvider extends BaseMemoryServiceProvider
{
    public function boot(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            $this->loadMigrationsFrom(dirname(__DIR__, 2).'/vendor/eznix86/laravel-ai-memory/database/migrations');
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                dirname(__DIR__, 2).'/vendor/eznix86/laravel-ai-memory/config/memory.php' => config_path('memory.php'),
            ], 'memory-config');
        }
    }
}
