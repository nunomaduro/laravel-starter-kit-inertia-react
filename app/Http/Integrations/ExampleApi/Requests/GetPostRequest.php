<?php

declare(strict_types=1);

namespace App\Http\Integrations\ExampleApi\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Example Saloon request: fetch a single post by ID from JSONPlaceholder.
 *
 * @see docs/developer/backend/saloon.md
 */
final class GetPostRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly int $id
    ) {}

    public function resolveEndpoint(): string
    {
        return '/posts/'.$this->id;
    }
}
