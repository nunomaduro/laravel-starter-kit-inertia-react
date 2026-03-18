<?php

declare(strict_types=1);

namespace App\Http\Integrations\Typesense;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\HasTimeout;

final class TypesenseConnector extends Connector
{
    use AcceptsJson;
    use HasTimeout;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey
    ) {}

    public function resolveBaseUrl(): string
    {
        return $this->baseUrl;
    }

    protected function defaultHeaders(): array
    {
        return [
            'X-TYPESENSE-API-KEY' => $this->apiKey,
        ];
    }

    protected function defaultTimeout(): int
    {
        return 5;
    }
}
