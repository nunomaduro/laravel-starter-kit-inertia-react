<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class LoggingSettings extends Settings
{
    public string $default_channel = 'stack';

    public string $log_level = 'debug';

    public ?string $slack_webhook_url = null;

    public string $slack_log_level = 'critical';

    public static function group(): string
    {
        return 'logging';
    }

    /** @return array<string> */
    public static function encrypted(): array
    {
        return ['slack_webhook_url'];
    }
}
