<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class EnvValidateCommand extends Command
{
    protected $signature = 'env:validate
                            {--production : Also warn when recommended production vars are missing or weak}
                            {--strict : Treat warnings as failures}';

    protected $description = 'Validate required and recommended environment variables';

    private bool $failed = false;

    private bool $warned = false;

    public function handle(): int
    {
        $this->checkAppKey();
        $this->checkDatabase();
        $this->checkAppUrl();

        if ($this->option('production')) {
            $this->checkProductionRecommendations();
        }

        if ($this->failed) {
            $this->error('One or more required checks failed. Fix the issues above and run again.');

            return self::FAILURE;
        }

        if ($this->option('strict') && $this->warned) {
            $this->warn('One or more warnings were treated as failures (--strict).');

            return self::FAILURE;
        }

        if ($this->warned) {
            $this->warn('Validation passed with warnings. Review the output above.');
        } else {
            $this->info('Environment validation passed.');
        }

        return self::SUCCESS;
    }

    private function checkAppKey(): void
    {
        $key = config('app.key');
        if ($key === null || $key === '') {
            $this->line('  <error>✗</error> APP_KEY is not set. Run: <comment>php artisan key:generate</comment>');
            $this->failed = true;

            return;
        }

        $this->line('  <info>✓</info> APP_KEY is set');
    }

    private function checkDatabase(): void
    {
        $default = config('database.default');
        $connection = config('database.connections.'.$default);
        if (! is_array($connection)) {
            $this->line('  <error>✗</error> Database connection "'.$default.'" is not configured.');
            $this->failed = true;

            return;
        }

        $driver = $connection['driver'] ?? 'unknown';
        $database = $connection['database'] ?? '';
        if ($database === '') {
            $this->line('  <error>✗</error> DB_DATABASE (or database name for connection "'.$default.'") is empty.');
            $this->failed = true;

            return;
        }

        $this->line('  <info>✓</info> Database configured ('.$driver.' / '.$database.')');
    }

    private function checkAppUrl(): void
    {
        $url = config('app.url');
        if ($url === null || $url === '') {
            $this->line('  <error>✗</error> APP_URL is not set.');
            $this->failed = true;

            return;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            $this->line('  <error>✗</error> APP_URL is not a valid URL: '.$url);
            $this->failed = true;

            return;
        }

        $this->line('  <info>✓</info> APP_URL is set and valid');
    }

    private function checkProductionRecommendations(): void
    {
        $env = config('app.env', 'local');
        if ($env !== 'production') {
            return;
        }

        $sessionDriver = config('session.driver', 'file');
        $cacheStore = config('cache.default', 'file');
        $queueConnection = config('queue.default', 'sync');

        if ($sessionDriver === 'file') {
            $this->line('  <comment>!</comment> SESSION_DRIVER=file is not recommended for multi-server production. Prefer redis or database.');
            $this->warned = true;
        }

        if ($cacheStore === 'file') {
            $this->line('  <comment>!</comment> CACHE_STORE=file is not supported for multi-server production. Use Redis or equivalent.');
            $this->warned = true;
        }

        if ($queueConnection === 'sync') {
            $this->line('  <comment>!</comment> QUEUE_CONNECTION=sync runs jobs synchronously. Use redis/database for production queues.');
            $this->warned = true;
        }

        $allGood = $sessionDriver !== 'file' && $cacheStore !== 'file' && $queueConnection !== 'sync';
        if ($allGood) {
            $this->line('  <info>✓</info> Production recommendations (session, cache, queue) look good');
        }
    }
}
