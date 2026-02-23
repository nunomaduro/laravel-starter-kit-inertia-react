<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class FeatureFlagSettings extends Settings
{
    /** @var array<string> */
    public array $globally_disabled_modules = [];

    public static function group(): string
    {
        return 'feature-flags';
    }
}
