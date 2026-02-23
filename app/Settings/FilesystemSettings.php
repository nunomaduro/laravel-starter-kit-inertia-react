<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class FilesystemSettings extends Settings
{
    public string $default_disk = 'local';

    public ?string $s3_key = null;

    public ?string $s3_secret = null;

    public ?string $s3_region = 'us-east-1';

    public ?string $s3_bucket = null;

    public ?string $s3_url = null;

    public static function group(): string
    {
        return 'filesystem';
    }

    /** @return array<string> */
    public static function encrypted(): array
    {
        return ['s3_key', 's3_secret'];
    }
}
