<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('bot_studio.max_agents_basic', 3);
        $this->migrator->add('bot_studio.max_agents_pro', 0);
        $this->migrator->add('bot_studio.max_knowledge_file_size_mb', 10);
        $this->migrator->add('bot_studio.max_knowledge_total_mb', 100);
        $this->migrator->add('bot_studio.default_model', 'gpt-4o-mini');
        $this->migrator->add('bot_studio.allowed_models', ['gpt-4o-mini', 'gpt-4o', 'claude-sonnet-4-5', 'claude-haiku-4-5']);
    }
};
