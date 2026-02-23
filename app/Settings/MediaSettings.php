<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class MediaSettings extends Settings
{
    public string $disk_name = 'public';

    public int $max_file_size = 10240;

    public static function group(): string
    {
        return 'media';
    }
}
