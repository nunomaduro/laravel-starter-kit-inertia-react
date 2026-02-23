<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class PerformanceSettings extends Settings
{
    public bool $cache_enabled = false;

    public int $cache_lifetime_seconds = 604800;

    public string $cache_driver = 'file';

    public static function group(): string
    {
        return 'performance';
    }
}
