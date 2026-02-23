<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class ActivityLogSettings extends Settings
{
    public bool $enabled = true;

    public bool $delete_records_older_than_days_enabled = false;

    public int $delete_records_older_than_days = 365;

    public static function group(): string
    {
        return 'activitylog';
    }
}
