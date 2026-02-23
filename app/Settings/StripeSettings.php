<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class StripeSettings extends Settings
{
    public ?string $key = null;

    public ?string $secret = null;

    public ?string $webhook_secret = null;

    public static function group(): string
    {
        return 'stripe';
    }

    /** @return array<string> */
    public static function encrypted(): array
    {
        return ['key', 'secret', 'webhook_secret'];
    }
}
