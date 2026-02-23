<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // AiSettings extensions — Cohere and Jina API keys (not covered by PrismSettings)
        $this->migrator->addEncrypted('ai.cohere_api_key', config('ai.providers.cohere.key'));
        $this->migrator->addEncrypted('ai.jina_api_key', config('ai.providers.jina.key'));

        // TenancySettings extensions — enabled, domain, subdomain_resolution
        $this->migrator->add('tenancy.enabled', config('tenancy.enabled', true));
        $this->migrator->add('tenancy.domain', config('tenancy.domain'));
        $this->migrator->add('tenancy.subdomain_resolution', config('tenancy.subdomain_resolution', true));
    }
};
