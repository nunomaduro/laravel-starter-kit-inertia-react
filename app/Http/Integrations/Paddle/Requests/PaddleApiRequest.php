<?php

declare(strict_types=1);

namespace App\Http\Integrations\Paddle\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Paddle API request with JSON body (POST, PATCH, etc.).
 *
 * @param  array<string, mixed>  $payload
 */
final class PaddleApiRequest extends Request implements HasBody
{
    use HasJsonBody;

    public function __construct(
        Method $method,
        private readonly string $endpoint,
        private readonly array $payload = []
    ) {
        $this->method = $method;
    }

    public function resolveEndpoint(): string
    {
        return $this->endpoint;
    }

    protected function defaultBody(): array
    {
        return $this->payload;
    }
}
