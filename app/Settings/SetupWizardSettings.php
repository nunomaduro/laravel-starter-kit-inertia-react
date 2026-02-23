<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class SetupWizardSettings extends Settings
{
    public bool $setup_completed = false;

    /** @var array<string> */
    public array $completed_steps = [];

    public static function group(): string
    {
        return 'setup-wizard';
    }
}
