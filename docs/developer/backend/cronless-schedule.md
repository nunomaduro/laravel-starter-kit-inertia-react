# Cronless schedule

[spatie/laravel-cronless-schedule](https://github.com/spatie/laravel-cronless-schedule) runs the Laravel scheduler without cron by executing `schedule:run` in a loop at a configurable interval. Use it when cron is not available (e.g. Heroku, Render, or other PaaS) or when you want to simulate the scheduler in local/test without setting up cron.

## When to use

- **PaaS / serverless-style workers:** Platforms that don’t provide cron (or charge for it) can run a single long-lived process that executes the scheduler every minute.
- **Local development:** Run `php artisan schedule:run-cronless` instead of configuring cron to trigger `schedule:run` every minute.
- **Testing:** Run the scheduler at a higher frequency (e.g. every few seconds) for a short period with `--frequency` and `--stop-after-seconds`.

The existing schedule in `routes/console.php` is unchanged; only the **trigger** changes from cron to this command.

## Command

```bash
php artisan schedule:run-cronless
```

By default the command runs forever and executes the scheduler every 60 seconds.

### Options

- **--frequency=SECONDS** — Interval in seconds between runs (default: 60). Example: `--frequency=5` for every 5 seconds.
- **--command=COMMAND** — Artisan command to run instead of `schedule:run`. Example: `--command=your-command`.
- **--stop-after-seconds=SECONDS** — Stop after the given number of seconds. Example: `--stop-after-seconds=300`.

### Manual run

While the command is running, pressing Enter triggers an extra run of the scheduler.

## Deployment

On platforms without cron, run this command as a separate process (e.g. a second dyno on Heroku, or a background worker). Ensure the process is restarted on deploy and monitored so the scheduler keeps running. See [Deployment](./deployment.md) for the “Running the scheduler without cron” section.

## Existing scheduled tasks

All tasks defined in `routes/console.php` (backup, billing metrics, dunning reminders, sitemap, permission sync, health checks, model prune, etc.) are executed by the scheduler when it runs. No code changes are required; only how the scheduler is invoked (cron vs cronless) changes.
