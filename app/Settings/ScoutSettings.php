<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class ScoutSettings extends Settings
{
    public string $driver = 'collection';

    public string $prefix = '';

    public bool $queue = false;

    public bool $identify = false;

    public ?string $typesense_api_key = null;

    public string $typesense_host = 'localhost';

    public int $typesense_port = 8108;

    public string $typesense_protocol = 'http';

    public static function group(): string
    {
        return 'scout';
    }

    /** @return array<string> */
    public static function encrypted(): array
    {
        return ['typesense_api_key'];
    }
}
