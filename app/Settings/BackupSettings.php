<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class BackupSettings extends Settings
{
    public string $name = 'laravel-backup';

    public int $keep_all_backups_for_days = 7;

    public int $keep_daily_backups_for_days = 16;

    public int $keep_weekly_backups_for_weeks = 8;

    public int $keep_monthly_backups_for_months = 4;

    public int $keep_yearly_backups_for_years = 2;

    public int $delete_oldest_when_size_mb = 5000;

    public static function group(): string
    {
        return 'backup';
    }
}
