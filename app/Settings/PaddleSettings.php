<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class PaddleSettings extends Settings
{
    public ?string $vendor_id = null;

    public ?string $vendor_auth_code = null;

    public ?string $public_key = null;

    public ?string $webhook_secret = null;

    public bool $sandbox = true;

    public static function group(): string
    {
        return 'paddle';
    }

    /** @return array<string> */
    public static function encrypted(): array
    {
        return ['vendor_id', 'vendor_auth_code', 'public_key', 'webhook_secret'];
    }
}
