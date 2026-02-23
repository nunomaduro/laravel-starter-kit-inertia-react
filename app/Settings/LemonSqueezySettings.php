<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class LemonSqueezySettings extends Settings
{
    public ?string $api_key = null;

    public ?string $signing_secret = null;

    public ?string $store = null;

    public string $path = 'lemon-squeezy';

    public string $currency_locale = 'en';

    public ?string $generic_variant_id = null;

    public static function group(): string
    {
        return 'lemon-squeezy';
    }

    /** @return array<string> */
    public static function encrypted(): array
    {
        return ['api_key', 'signing_secret'];
    }
}
