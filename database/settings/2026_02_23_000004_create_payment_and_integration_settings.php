<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Stripe
        $this->migrator->addEncrypted('stripe.key', config('stripe.key'));
        $this->migrator->addEncrypted('stripe.secret', config('stripe.secret'));
        $this->migrator->addEncrypted('stripe.webhook_secret', config('stripe.webhook_secret'));

        // Paddle
        $this->migrator->addEncrypted('paddle.vendor_id', config('paddle.vendor_id'));
        $this->migrator->addEncrypted('paddle.vendor_auth_code', config('paddle.vendor_auth_code'));
        $this->migrator->addEncrypted('paddle.public_key', config('paddle.public_key'));
        $this->migrator->addEncrypted('paddle.webhook_secret', config('paddle.webhook_secret'));
        $this->migrator->add('paddle.sandbox', (bool) config('paddle.sandbox', true));

        // Lemon Squeezy
        $this->migrator->addEncrypted('lemon-squeezy.api_key', config('lemon-squeezy.api_key'));
        $this->migrator->addEncrypted('lemon-squeezy.signing_secret', config('lemon-squeezy.signing_secret'));
        $this->migrator->add('lemon-squeezy.store', config('lemon-squeezy.store'));
        $this->migrator->add('lemon-squeezy.path', config('lemon-squeezy.path', 'lemon-squeezy'));
        $this->migrator->add('lemon-squeezy.currency_locale', config('lemon-squeezy.currency_locale', 'en'));
        $this->migrator->add('lemon-squeezy.generic_variant_id', config('services.lemon_squeezy.generic_variant_id'));

        // Integrations
        $this->migrator->addEncrypted('integrations.slack_webhook_url', config('services.slack.webhook_url'));
        $this->migrator->addEncrypted('integrations.slack_bot_token', config('services.slack.notifications.bot_user_oauth_token'));
        $this->migrator->add('integrations.slack_channel', config('services.slack.notifications.channel'));
        $this->migrator->addEncrypted('integrations.postmark_token', config('services.postmark.token'));
        $this->migrator->addEncrypted('integrations.resend_key', config('services.resend.key'));
    }
};
