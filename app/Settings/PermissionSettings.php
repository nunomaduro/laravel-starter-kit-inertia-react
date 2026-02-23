<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class PermissionSettings extends Settings
{
    public bool $teams_enabled = true;

    public string $team_foreign_key = 'organization_id';

    public static function group(): string
    {
        return 'permission';
    }
}
