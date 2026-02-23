<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('mail.mailer', config('mail.default', 'log'));
        $this->migrator->add('mail.smtp_host', config('mail.mailers.smtp.host', '127.0.0.1'));
        $this->migrator->add('mail.smtp_port', (int) config('mail.mailers.smtp.port', 2525));
        $this->migrator->add('mail.smtp_username', config('mail.mailers.smtp.username'));
        $this->migrator->addEncrypted('mail.smtp_password', config('mail.mailers.smtp.password'));
        $this->migrator->add('mail.smtp_encryption', config('mail.mailers.smtp.scheme'));
        $this->migrator->add('mail.from_address', config('mail.from.address', 'hello@example.com'));
        $this->migrator->add('mail.from_name', config('mail.from.name', 'Example'));
    }
};
