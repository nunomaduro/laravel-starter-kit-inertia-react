<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // AppSettings — application URL (was APP_URL env var)
        $this->migrator->add('app.url', config('app.url', 'http://localhost'));

        // BroadcastingSettings — default connection (was BROADCAST_CONNECTION env var)
        $this->migrator->add('broadcasting.default_connection', config('broadcasting.default', 'log'));
    }
};
