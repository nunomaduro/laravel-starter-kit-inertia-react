<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class ThemeSettings extends Settings
{
    public string $preset = 'default';

    public string $base_color = 'neutral';

    public string $radius = 'default';

    /** @var 'inter'|'geist'|'poppins'|'outfit'|'plus-jakarta-sans' */
    public string $font = 'inter';

    /** @var 'light'|'dark'|'system' */
    public string $default_appearance = 'system';

    public string $dark_color_scheme = 'navy';

    public string $primary_color = 'indigo';

    public string $light_color_scheme = 'slate';

    public string $card_skin = 'shadow';

    public string $border_radius = 'default';

    /** @var 'main'|'sideblock' */
    public string $sidebar_layout = 'main';

    /** @var 'default'|'primary'|'muted' */
    public string $menu_color = 'default';

    /** @var 'subtle'|'strong'|'bordered' */
    public string $menu_accent = 'subtle';

    public bool $allow_user_theme_customization = false;

    public static function group(): string
    {
        return 'theme';
    }
}
