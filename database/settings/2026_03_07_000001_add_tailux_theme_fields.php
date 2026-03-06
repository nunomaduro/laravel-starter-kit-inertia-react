<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('theme.dark_color_scheme', 'navy');
        $this->migrator->add('theme.primary_color', 'indigo');
        $this->migrator->add('theme.light_color_scheme', 'slate');
        $this->migrator->add('theme.card_skin', 'shadow');
        $this->migrator->add('theme.border_radius', 'default');
    }
};
