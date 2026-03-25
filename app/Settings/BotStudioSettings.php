<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class BotStudioSettings extends Settings
{
    public int $max_agents_basic = 3;

    public int $max_agents_pro = 0;

    public int $max_knowledge_file_size_mb = 10;

    public int $max_knowledge_total_mb = 100;

    public string $default_model = 'gpt-4o-mini';

    /** @var array<int, string> */
    public array $allowed_models = ['gpt-4o-mini', 'gpt-4o', 'claude-sonnet-4-5', 'claude-haiku-4-5'];

    public static function group(): string
    {
        return 'bot_studio';
    }
}
