<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class InfrastructureSettings extends Settings
{
    /** Cache, session, and queue driver: 'database' | 'redis' */
    public string $cache_driver = 'database';

    public string $session_driver = 'database';

    public string $queue_connection = 'database';

    public string $redis_host = '127.0.0.1';

    public int $redis_port = 6379;

    public ?string $redis_password = null;

    public static function group(): string
    {
        return 'infrastructure';
    }

    /** @return list<string> */
    public static function encrypted(): array
    {
        return ['redis_password'];
    }
}
