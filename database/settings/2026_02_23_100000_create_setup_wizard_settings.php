<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('setup-wizard.setup_completed', false);
        $this->migrator->add('setup-wizard.completed_steps', []);
    }
};
