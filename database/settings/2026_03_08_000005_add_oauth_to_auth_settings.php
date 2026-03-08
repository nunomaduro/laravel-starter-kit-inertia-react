<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('auth.google_oauth_enabled', false);
        $this->migrator->add('auth.google_client_id', '');
        $this->migrator->add('auth.google_client_secret', encrypt(''));
        $this->migrator->add('auth.github_oauth_enabled', false);
        $this->migrator->add('auth.github_client_id', '');
        $this->migrator->add('auth.github_client_secret', encrypt(''));
    }
};
