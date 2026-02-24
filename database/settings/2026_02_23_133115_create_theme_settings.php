<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('theme.preset', config('theme.preset', 'default'));
        $this->migrator->add('theme.base_color', config('theme.base_color', 'neutral'));
        $this->migrator->add('theme.radius', config('theme.radius', 'default'));
        $this->migrator->add('theme.font', config('theme.font', 'instrument-sans'));
        $this->migrator->add('theme.default_appearance', config('theme.default_appearance', 'system'));
    }
};
