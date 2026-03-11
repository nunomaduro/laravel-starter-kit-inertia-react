<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->update('theme.dark_color_scheme', fn (): string => '');
        $this->migrator->update('theme.primary_color', fn (): string => '');
        $this->migrator->update('theme.light_color_scheme', fn (): string => '');
        $this->migrator->update('theme.font', fn (): string => 'instrument-sans');
    }
};
