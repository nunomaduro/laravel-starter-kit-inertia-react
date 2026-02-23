<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class ImpersonateSettings extends Settings
{
    public bool $enabled = true;

    public string $banner_style = 'dark';

    public static function group(): string
    {
        return 'impersonate';
    }
}
