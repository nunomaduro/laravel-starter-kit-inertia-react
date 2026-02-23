<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class CookieConsentSettings extends Settings
{
    public bool $enabled = true;

    public static function group(): string
    {
        return 'cookie-consent';
    }
}
