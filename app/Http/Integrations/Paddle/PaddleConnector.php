<?php

declare(strict_types=1);

namespace App\Http\Integrations\Paddle;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

final class PaddleConnector extends Connector
{
    use AcceptsJson;

    public function resolveBaseUrl(): string
    {
        return config('paddle.sandbox', true)
            ? 'https://sandbox-api.paddle.com'
            : 'https://api.paddle.com';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.config('paddle.vendor_auth_code'),
            'Content-Type' => 'application/json',
        ];
    }
}
