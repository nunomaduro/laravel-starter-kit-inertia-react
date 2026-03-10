<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('logging.default_channel', config('logging.default', 'stack'));
        $this->migrator->add('logging.log_level', 'debug');
        $this->migrator->addEncrypted('logging.slack_webhook_url', config('logging.channels.slack.url'));
        $this->migrator->add('logging.slack_log_level', config('logging.channels.slack.level', 'critical'));
    }
};
