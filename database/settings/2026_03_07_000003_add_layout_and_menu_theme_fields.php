<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('theme.sidebar_layout', 'main');
        $this->migrator->add('theme.menu_color', 'default');
        $this->migrator->add('theme.menu_accent', 'subtle');
    }
};
