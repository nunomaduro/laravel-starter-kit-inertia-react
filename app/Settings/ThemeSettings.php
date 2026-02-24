<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class ThemeSettings extends Settings
{
    public string $preset = 'default';

    public string $base_color = 'neutral';

    public string $radius = 'default';

    public string $font = 'instrument-sans';

    public string $default_appearance = 'system'; // light | dark | system

    public static function group(): string
    {
        return 'theme';
    }
}
