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

    /** optional | required | admins_only */
    public string $two_factor_enforcement = 'optional';

    public int $session_lifetime = 120;

    public int $password_min_length = 8;

    public bool $password_require_uppercase = false;

    public bool $password_require_numbers = false;

    public bool $password_require_symbols = false;

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
