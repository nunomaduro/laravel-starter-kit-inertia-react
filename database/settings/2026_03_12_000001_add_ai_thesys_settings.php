<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->addEncrypted('ai.thesys_api_key', null);
        $this->migrator->add('ai.thesys_model', config('services.thesys.model', 'c1-nightly'));
    }
};
