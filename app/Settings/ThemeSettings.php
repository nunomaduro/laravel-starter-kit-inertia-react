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

    /** @var 'light'|'dark'|'system' */
    public string $default_appearance = 'system';

    public string $dark_color_scheme = 'navy';

    public string $primary_color = 'indigo';

    public string $light_color_scheme = 'slate';

    public string $card_skin = 'shadow';

    public string $border_radius = 'default';

    public static function group(): string
    {
        return 'theme';
    }
}
