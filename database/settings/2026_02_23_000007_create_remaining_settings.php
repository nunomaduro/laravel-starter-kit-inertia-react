<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Filesystem
        $this->migrator->add('filesystem.default_disk', config('filesystems.default', 'local'));
        $this->migrator->addEncrypted('filesystem.s3_key', config('filesystems.disks.s3.key'));
        $this->migrator->addEncrypted('filesystem.s3_secret', config('filesystems.disks.s3.secret'));
        $this->migrator->add('filesystem.s3_region', config('filesystems.disks.s3.region', 'us-east-1'));
        $this->migrator->add('filesystem.s3_bucket', config('filesystems.disks.s3.bucket'));
        $this->migrator->add('filesystem.s3_url', config('filesystems.disks.s3.url'));

        // Broadcasting
        $this->migrator->add('broadcasting.reverb_app_id', config('reverb.apps.apps.0.app_id'));
        $this->migrator->add('broadcasting.reverb_app_key', config('reverb.apps.apps.0.key'));
        $this->migrator->addEncrypted('broadcasting.reverb_app_secret', config('reverb.apps.apps.0.secret'));
        $this->migrator->add('broadcasting.reverb_host', config('reverb.servers.reverb.host', 'localhost'));
        $this->migrator->add('broadcasting.reverb_port', (int) config('reverb.servers.reverb.port', 8080));
        $this->migrator->add('broadcasting.reverb_scheme', config('reverb.servers.reverb.hostname') ? 'https' : 'http');

        // Permission
        $this->migrator->add('permission.teams_enabled', (bool) config('permission.teams', true));
        $this->migrator->add('permission.team_foreign_key', config('permission.team_foreign_key', 'organization_id'));

        // Activity Log
        $this->migrator->add('activitylog.enabled', (bool) config('activitylog.enabled', true));
        $this->migrator->add('activitylog.delete_records_older_than_days_enabled', false);
        $this->migrator->add('activitylog.delete_records_older_than_days', 365);

        // Impersonate
        $this->migrator->add('impersonate.enabled', true);
        $this->migrator->add('impersonate.banner_style', config('filament-impersonate.banner.style', 'dark'));

        // Backup
        $this->migrator->add('backup.name', config('backup.backup.name', 'laravel-backup'));
        $this->migrator->add('backup.keep_all_backups_for_days', (int) config('backup.cleanup.default_strategy.keep_all_backups_for_days', 7));
        $this->migrator->add('backup.keep_daily_backups_for_days', (int) config('backup.cleanup.default_strategy.keep_daily_backups_for_days', 16));
        $this->migrator->add('backup.keep_weekly_backups_for_weeks', (int) config('backup.cleanup.default_strategy.keep_weekly_backups_for_weeks', 8));
        $this->migrator->add('backup.keep_monthly_backups_for_months', (int) config('backup.cleanup.default_strategy.keep_monthly_backups_for_months', 4));
        $this->migrator->add('backup.keep_yearly_backups_for_years', (int) config('backup.cleanup.default_strategy.keep_yearly_backups_for_years', 2));
        $this->migrator->add('backup.delete_oldest_when_size_mb', (int) config('backup.cleanup.default_strategy.delete_oldest_backups_when_using_more_megabytes_than', 5000));

        // Media
        $this->migrator->add('media.disk_name', config('media-library.disk_name', 'public'));
        $this->migrator->add('media.max_file_size', (int) config('media-library.max_file_size', 10240));
    }
};
