<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('auth.two_factor_enforcement', 'optional');
        $this->migrator->add('auth.session_lifetime', (int) config('session.lifetime', 120));
        $this->migrator->add('auth.password_min_length', 8);
        $this->migrator->add('auth.password_require_uppercase', false);
        $this->migrator->add('auth.password_require_numbers', false);
        $this->migrator->add('auth.password_require_symbols', false);
    }
};
