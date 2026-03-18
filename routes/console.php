<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

if (config('permission.route_based_enforcement', false)) {
    Schedule::command('permission:sync-routes', ['--silent' => true])->daily();
}

Schedule::command('personal-data-export:clean')->daily();
Schedule::command('sitemap:generate')->daily();

Schedule::command('backup:run')->daily()->at('01:00');
Schedule::command('backup:clean')->daily()->at('01:00');

if (class_exists(Laravel\Telescope\Telescope::class)) {
    Schedule::command('telescope:prune')->daily();
}

Schedule::command('model:prune', [
    '--model' => [MartinPetricko\LaravelDatabaseMail\Models\MailException::class],
])->daily();

Schedule::command('model:prune', [
    '--model' => [Spatie\WebhookClient\Models\WebhookCall::class],
])->daily();

Schedule::job(new App\Jobs\Billing\GenerateBillingMetrics)->daily()->at('02:00');
Schedule::job(new App\Jobs\Billing\ExpireCredits)->daily()->at('03:00');
Schedule::job(new App\Jobs\Billing\ProcessTrialEndingReminders)->daily()->at('04:00');
Schedule::job(new App\Jobs\Billing\ProcessDunningReminders)->daily()->at('05:00');
