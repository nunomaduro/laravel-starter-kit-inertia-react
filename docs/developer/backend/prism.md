# Prism / Relay Integration

This starter kit keeps [Prism PHP](https://prismphp.com/) and [Prism Relay](https://github.com/prism-php/relay) as direct dependencies, but their role is intentionally narrow.

> **Single rule:** Use `laravel/ai` Agent classes for all AI work. The only exception is `PrismService::withTools()`, which bridges [Relay](https://github.com/prism-php/relay) MCP tools into a Prism request — Relay has no `laravel/ai` equivalent.

See [ai-sdk.md](./ai-sdk.md) for agents, structured output, text generation, images, audio, and embeddings.

---

## Why Prism is Still Here

`laravel/ai` depends on `prism-php/prism` internally, so Prism is always in vendor. We keep it as a direct dependency for two reasons:

1. **Relay** — `prism-php/relay` connects to external MCP servers and exposes their tools as Prism tool objects. There is no `laravel/ai` client-side MCP equivalent.
2. **Availability check** — `PrismService::isAvailable()` reads the provider config to let optional AI features degrade gracefully when API keys are missing.

---

## PrismService

`App\Services\PrismService` is a thin wrapper with two methods:

### `isAvailable(?Provider $provider = null): bool`

Returns `true` when the given provider (defaults to `config('prism.defaults.provider')`) has an API key configured. Use this before optional AI calls so the app degrades gracefully:

```php
use App\Services\PrismService;

if (app(PrismService::class)->isAvailable()) {
    // safe to call AI
}
```

### `withTools(string|array $servers, ?string $model = null): PendingRequest`

Fetches tools from one or more MCP servers via Relay and returns a Prism `PendingRequest` pre-loaded with those tools. This is the **only** place in the codebase that calls Prism directly.

```php
use App\Services\PrismService;

$response = app(PrismService::class)
    ->withTools('puppeteer')
    ->withPrompt('Navigate to laravel.com and summarise the homepage')
    ->asText();

// Multiple servers
$response = app(PrismService::class)
    ->withTools(['puppeteer', 'github'])
    ->withPrompt('Find the Laravel repo and take a screenshot')
    ->asText();
```

---

## Configuration

Prism is configured in `config/prism.php` and `PrismSettings` (DB-backed, org-overridable):

```env
OPENROUTER_API_KEY=your-api-key-here
OPENROUTER_URL=https://openrouter.ai/api/v1

PRISM_DEFAULT_PROVIDER=openrouter
PRISM_DEFAULT_MODEL=deepseek/deepseek-r1-0528:free
```

The `withTools()` method uses `PRISM_DEFAULT_PROVIDER` and `PRISM_DEFAULT_MODEL` automatically. Pass `$model` to override for a specific call.

---

## MCP Integration with Relay

Relay is configured in `config/relay.php`. Define each MCP server your app needs:

```php
'servers' => [
    'puppeteer' => [
        'transport' => Transport::Stdio,
        'command'   => ['npx', '-y', '@modelcontextprotocol/server-puppeteer'],
        'timeout'   => env('RELAY_PUPPETEER_SERVER_TIMEOUT', 60),
        'env'       => [],
    ],
],
```

### Available transports

- **Stdio** — locally running MCP servers communicating via standard I/O
- **HTTP** — MCP servers communicating over HTTP

---

## Artisan Commands

### `prism:validate`

Validates Prism and Relay configuration:

```bash
php artisan prism:validate
```

Checks:
- Default provider and model values
- OpenRouter API key and URL
- MCP server definitions in `config/relay.php`
- Tool availability from each configured MCP server

### `prism:example`

Quick smoke-test for text generation and MCP tools:

```bash
# Basic text generation (uses laravel/ai agent internally)
php artisan prism:example --prompt="Explain Laravel in one sentence"

# With MCP tools via Relay
php artisan prism:example --prompt="Navigate to laravel.com" --tools=puppeteer

# Multiple MCP servers
php artisan prism:example --tools="puppeteer,github" --prompt="..."
```

---

## Error Handling

Relay throws specific exceptions you can catch when using `withTools()`:

```php
use Prism\Relay\Exceptions\RelayException;
use Prism\Relay\Exceptions\ServerConfigurationException;
use Prism\Relay\Exceptions\ToolCallException;

try {
    $response = app(PrismService::class)
        ->withTools('puppeteer')
        ->withPrompt('...')
        ->asText();
} catch (ServerConfigurationException $e) {
    // MCP server not found in config/relay.php
} catch (ToolCallException $e) {
    // Tool execution failed
} catch (RelayException $e) {
    // Other Relay error
}
```

For Prism-level errors (rate limits, invalid model, etc.) catch `Prism\Prism\Exceptions\PrismException` and its subclasses.

---

For all other AI features — agents, structured output, text generation, images, audio, embeddings — see [ai-sdk.md](./ai-sdk.md).
