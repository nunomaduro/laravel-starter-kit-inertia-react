<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class MailSettings extends Settings
{
    public string $mailer = 'log';

    public string $smtp_host = '127.0.0.1';

    public int $smtp_port = 2525;

    public ?string $smtp_username = null;

    public ?string $smtp_password = null;

    public ?string $smtp_encryption = null;

    public string $from_address = 'hello@example.com';

    public string $from_name = 'Example';

    public static function group(): string
    {
        return 'mail';
    }

    /** @return array<string> */
    public static function encrypted(): array
    {
        return ['smtp_password'];
    }
}
