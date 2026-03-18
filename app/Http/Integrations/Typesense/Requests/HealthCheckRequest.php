<?php

declare(strict_types=1);

namespace App\Http\Integrations\Typesense\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

final class HealthCheckRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/health';
    }
}
