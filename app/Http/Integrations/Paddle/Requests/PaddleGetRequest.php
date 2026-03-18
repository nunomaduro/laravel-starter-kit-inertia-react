<?php

declare(strict_types=1);

namespace App\Http\Integrations\Paddle\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class PaddleGetRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $endpoint
    ) {}

    public function resolveEndpoint(): string
    {
        return $this->endpoint;
    }
}
