<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// When route-based permissions are enabled, keep permissions in sync with named routes daily.
if (config('permission.route_based_enforcement', false)) {
    Schedule::command('permission:sync-routes', ['--silent' => true])->daily();
}

// Remove expired personal data exports (GDPR).
Schedule::command('personal-data-export:clean')->daily();

// Regenerate sitemap for SEO.
Schedule::command('sitemap:generate')->daily();

// Database and file backups (spatie/laravel-backup). Run first, then clean old ones.
Schedule::command('backup:run')->daily()->at('01:00');
Schedule::command('backup:clean')->daily()->at('01:00');

// Telescope: prune old entries (local dev; entries older than 24h).
if (class_exists(Laravel\Telescope\Telescope::class)) {
    Schedule::command('telescope:prune')->daily();
}

// Database Mail: prune old mail exceptions (martinpetricko/laravel-database-mail).
Schedule::command('model:prune', [
    '--model' => [MartinPetricko\LaravelDatabaseMail\Models\MailException::class],
])->daily();

// Billing jobs: metrics, credit expiration, trial reminders.
Schedule::job(new App\Jobs\Billing\GenerateBillingMetrics)->daily()->at('02:00');
Schedule::job(new App\Jobs\Billing\ExpireCredits)->daily()->at('03:00');
Schedule::job(new App\Jobs\Billing\ProcessTrialEndingReminders)->daily()->at('04:00');
Schedule::job(new App\Jobs\Billing\ProcessDunningReminders)->daily()->at('05:00');
