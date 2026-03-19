<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class MonitoringSettings extends Settings
{
    public ?string $sentry_dsn = null;

    public float $sentry_sample_rate = 1.0;

    public ?float $sentry_traces_sample_rate = null;

    public static function group(): string
    {
        return 'monitoring';
    }

    /** @return array<string> */
    public static function encrypted(): array
    {
        return ['sentry_dsn'];
    }
}
