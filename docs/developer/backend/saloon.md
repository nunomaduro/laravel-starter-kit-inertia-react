# Saloon HTTP Client

Third-party API integrations use **Saloon** (`saloonphp/saloon` v3) for a consistent, testable HTTP client layer.

## Where integrations live

- **Connectors**: `App\Http\Integrations\{ApiName}\{ApiName}Connector.php` — base URL, default headers, optional auth.
- **Requests**: `App\Http\Integrations\{ApiName}\Requests\*.php` — one class per endpoint (method + path).

## Integrations in this app

### Paddle (billing)

- **Connector**: `App\Http\Integrations\Paddle\PaddleConnector` — base URL from `config('paddle.sandbox')` (sandbox vs production), Bearer token from `config('paddle.vendor_auth_code')`.
- **Requests**: `PaddleGetRequest` (GET), `PaddleApiRequest` (POST/PATCH with JSON body).
- **Usage**: Injected into `App\Services\PaymentGateway\Gateways\PaddleGateway`; the gateway calls `$this->connector->send(new PaddleGetRequest('/subscriptions/'.$id))` or `$this->connector->send(new PaddleApiRequest(Method::POST, '/customers', $data))` and uses `$response->json()`.

### Typesense (search health check)

- **Connector**: `App\Http\Integrations\Typesense\TypesenseConnector` — base URL and API key passed in the constructor (used with dynamic host/port during install).
- **Request**: `HealthCheckRequest` — GET `/health`.
- **Usage**: `AppInstallCommand::verifyTypesense()` builds the connector with the user-provided host/port/key and sends `HealthCheckRequest`.

## Adding a new integration

1. Create a connector under `app/Http/Integrations/{Name}/` extending `Saloon\Http\Connector`, implementing `resolveBaseUrl()` and optionally `defaultHeaders()` / `defaultAuth()`.
2. Create request classes under `app/Http/Integrations/{Name}/Requests/` extending `Saloon\Http\Request`, setting `$method` and `resolveEndpoint()`.
3. Add any API base URL or keys to `config/services.php` and `.env.example` (never commit secrets).
4. Use the connector in Actions or jobs; prefer dependency injection for testability.

## Testing

- Use Saloon’s `FakeResponse` or `MockClient` to avoid real HTTP calls in tests.
- See [Saloon testing docs](https://docs.saloon.dev/testing/overview) for mocking and fixtures.

## References

- [Saloon v3 docs](https://docs.saloon.dev)
- Paddle: `config/paddle.php`; Typesense: CLI installer and Scout settings.
