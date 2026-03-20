---
name: developing-with-prism
description: Guide for the narrow Prism/Relay role in this project. Activate ONLY when working with MCP tool integration via `PrismService::withTools()` (Relay bridge). For all other AI features — text generation, structured output, images, audio, embeddings, streaming, tools, conversation memory — use `developing-with-ai-sdk` instead.
---

# Developing with Prism / Relay

> **Single rule for this project:** Use `laravel/ai` Agent classes for everything. The only exception is `PrismService::withTools()`, which bridges [Relay](https://github.com/prism-php/relay) MCP tools into a Prism request — Relay has no `laravel/ai` equivalent.

See [ai-sdk.md](../../docs/developer/backend/ai-sdk.md) for agents, structured output, text generation, images, audio, and embeddings.

---

## Decision Workflow

| What you need | Use |
|---------------|-----|
| Text generation, chat, agents | `laravel/ai` — see `developing-with-ai-sdk` |
| Structured JSON output | `laravel/ai` Agent + `HasStructuredOutput` |
| Vision / multimodal | `laravel/ai` Agent + `Image::fromUpload()` |
| Streaming | `laravel/ai` Agent `->stream()` |
| Conversation history | `laravel/ai` Agent + `RemembersConversations` |
| Custom tools | `laravel/ai` Agent + `HasTools` |
| Embeddings | `Laravel\Ai\Embeddings::for()` |
| Images, Audio, TTS, STT | `Laravel\Ai\Image`, `Audio`, `Transcription` |
| **MCP tools from external servers** | **`PrismService::withTools()` via Relay ← only use case** |
| Provider availability check | `PrismService::isAvailable()` |

---

## PrismService — The Only Direct Prism Usage

`App\Services\PrismService` has exactly two methods:

### `isAvailable(?Provider $provider = null): bool`

Returns `true` when the configured provider has an API key. Use before optional AI calls:

```php
use App\Services\PrismService;

if (app(PrismService::class)->isAvailable()) {
    // safe to call AI
}
```

### `withTools(string|array $servers, ?string $model = null): PendingRequest`

Fetches tools from one or more MCP servers via Relay and returns a Prism `PendingRequest`:

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

## Error Handling

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

---

## Artisan Commands

```bash
# Validate Prism and Relay configuration
php artisan prism:validate

# Quick smoke-test (text via laravel/ai, MCP tools via Relay)
php artisan prism:example --prompt="Explain Laravel in one sentence"
php artisan prism:example --prompt="Navigate to laravel.com" --tools=puppeteer
```

---

For all other AI features, see [ai-sdk.md](../../docs/developer/backend/ai-sdk.md) and use the `developing-with-ai-sdk` skill.
