<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class AuthSettings extends Settings
{
    public bool $registration_enabled;

    public bool $email_verification_required;

    public bool $google_oauth_enabled = false;

    public string $google_client_id = '';

    public string $google_client_secret = '';

    public bool $github_oauth_enabled = false;

    public string $github_client_id = '';

    public string $github_client_secret = '';

    public static function group(): string
    {
        return 'auth';
    }

    /**
     * @return list<string>
     */
    public static function encrypted(): array
    {
        return ['google_client_secret', 'github_client_secret'];
    }
}
