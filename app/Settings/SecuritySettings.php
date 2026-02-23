<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class SecuritySettings extends Settings
{
    public bool $csp_enabled = true;

    public bool $csp_nonce_enabled = false;

    public string $csp_report_uri = '';

    public bool $honeypot_enabled = true;

    public int $honeypot_seconds = 1;

    /** @var array<string> */
    public array $ip_whitelist = [];

    public static function group(): string
    {
        return 'security';
    }
}
