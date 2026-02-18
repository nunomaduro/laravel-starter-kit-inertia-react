# Backup & Restore

Application and database backups are handled by **spatie/laravel-backup** (v10).

## Configuration

- **Config**: `config/backup.php` — backup name, disks, database dump, cleanup policy, notifications.
- **Schedule**: In `routes/console.php`, `backup:run` and `backup:clean` run daily at 01:00 (run before clean so the day’s backup exists before old ones are removed).

## Environment

Optional in `.env.example`:

- `BACKUP_DISK` — disk used for storing backups (default from config).
- `BACKUP_ARCHIVE_PASSWORD` — optional password for encrypted archives.

## Commands

- `php artisan backup:run` — create a new backup (files + DB as configured).
- `php artisan backup:clean` — remove backups older than the configured retention.
- `php artisan backup:list` — list existing backups (when supported by the disk).

## Restore

Restore is manual: use the backup archive (and DB dump) from the configured disk and restore files/database according to your hosting or runbook. The package does not provide a one-command restore.

## References

- [spatie/laravel-backup](https://github.com/spatie/laravel-backup) — configuration and notifications.
