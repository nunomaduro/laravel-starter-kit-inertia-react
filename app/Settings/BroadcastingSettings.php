<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class BroadcastingSettings extends Settings
{
    public ?string $reverb_app_id = null;

    public ?string $reverb_app_key = null;

    public ?string $reverb_app_secret = null;

    public string $reverb_host = 'localhost';

    public int $reverb_port = 8080;

    public string $reverb_scheme = 'http';

    public static function group(): string
    {
        return 'broadcasting';
    }

    /** @return array<string> */
    public static function encrypted(): array
    {
        return ['reverb_app_secret'];
    }
}
