<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class IntegrationsSettings extends Settings
{
    public ?string $slack_webhook_url = null;

    public ?string $slack_bot_token = null;

    public ?string $slack_channel = null;

    public ?string $postmark_token = null;

    public ?string $resend_key = null;

    public static function group(): string
    {
        return 'integrations';
    }

    /** @return array<string> */
    public static function encrypted(): array
    {
        return ['slack_webhook_url', 'slack_bot_token', 'postmark_token', 'resend_key'];
    }
}
