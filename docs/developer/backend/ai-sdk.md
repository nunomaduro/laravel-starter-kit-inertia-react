# Laravel AI SDK

This project uses the [Laravel AI SDK](https://laravel.com/docs/12.x/ai) (`laravel/ai`) as the **primary pattern for all AI work**. It is built on top of `prism-php/prism` and adds agents, streaming, conversation memory, tools, structured output, images, audio, embeddings, and more.

> **Single rule:** Write `laravel/ai` Agent classes for everything. The only exception is `PrismService::withTools()` for MCP/Relay tool fetching — see [prism.md](./prism.md).

---

## When to Use What

| Use case | Use |
|----------|-----|
| Text generation, one-off prompts | `agent()` helper or dedicated Agent class |
| Structured JSON output | Agent + `HasStructuredOutput` interface |
| Vision / multimodal (image input) | Agent + `Image::fromUpload()` attachment |
| Streaming (HTTP) | `(new MyAgent)->stream(...)` |
| Agents with conversation history | Agent + `RemembersConversations` trait |
| Agents with semantic memory | Agent + `StoreMemory` / `RecallMemory` tools (see [ai-memory.md](./ai-memory.md)) |
| Custom tools | Agent + `HasTools` + `php artisan make:tool` |
| Provider-native tools (web search, file search) | Agent + `WebSearch` / `WebFetch` / `FileSearch` |
| Images, TTS, STT, embeddings, reranking | `Image::of()`, `Audio::of()`, `Transcription::fromPath()`, `Embeddings::for()`, `Reranking::of()` |
| MCP tools from external servers | `PrismService::withTools()` via Relay — see [prism.md](./prism.md) |

---

## Configuration

Laravel AI is configured in `config/ai.php`. Provider API keys come from `.env`. The same `OPENROUTER_API_KEY` used by Prism is also available to agents using the `openrouter` provider.

Provider defaults in `config/ai.php`:

- **Text:** `openai` (override per agent with `#[Provider('openrouter')]` etc.)
- **Images:** `gemini`
- **Embeddings:** `openai`
- **Reranking:** `cohere`

---

## Agents

The primary building block. Create one with:

```bash
php artisan make:agent MyAgent
php artisan make:agent DocumentAnalyzer --structured
```

Agents live in `app/Ai/Agents/`. Every agent implements the `Agent` interface and uses the `Promptable` trait.

### Basic text generation

```php
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

final class SupportAgent implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a helpful support agent.';
    }
}

$response = (new SupportAgent)->prompt('How do I reset my password?');
echo $response->text;
```

### Anonymous agent (one-off calls)

For quick, one-off calls without a dedicated class — used by `DocumentationPrismGenerator` and `AISeederCodeGenerator`:

```php
use function Laravel\Ai\agent;

$response = agent(instructions: 'You are a technical writer.')
    ->prompt('Document this action...');

echo $response->text;
```

### Provider and model attributes

```php
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;

#[Provider('openrouter')]
#[Model('google/gemini-2.0-flash-001')]
#[Temperature(0.3)]
final class ThemeSuggestionAgent implements Agent, HasStructuredOutput
{
    use Promptable;
    // ...
}
```

---

## Structured Output

Implement `HasStructuredOutput` and define `schema()`. The response is accessed as an array via `$response->structured`.

```php
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\HasStructuredOutput;

final class ReviewAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a code reviewer.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'feedback' => $schema->string()->required(),
            'score'    => $schema->integer()->min(1)->max(10)->required(),
            'approved' => $schema->boolean()->required(),
        ];
    }
}

$response = (new ReviewAgent)->prompt('Review this PR...');
echo $response->structured['score']; // e.g. 8
```

**Real example in this codebase:** `App\Ai\Agents\ThemeSuggestionAgent` — analyzes a logo image and returns a full theme configuration. Used by `SuggestThemeFromLogo` action.

---

## Image Attachments (Vision / Multimodal)

Pass images as attachments to any agent:

```php
use Laravel\Ai\Files\Image;

// From an UploadedFile (most convenient — handles base64 + mime automatically)
$response = (new ThemeSuggestionAgent)->prompt(
    prompt: 'Suggest a theme based on this logo.',
    attachments: [Image::fromUpload($file)],
);

// Other sources
Image::fromPath('/path/to/image.png')
Image::fromStorage('logos/company.png')
Image::fromUrl('https://example.com/logo.png')
Image::fromBase64($base64String, 'image/png')
```

---

## Conversation Memory

Use `RemembersConversations` for automatic DB-backed conversation persistence:

```php
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Conversational;

final class ChatAgent implements Agent, Conversational
{
    use Promptable, RemembersConversations;

    public function instructions(): string
    {
        return 'You are a helpful assistant.';
    }
}

// New conversation
$response = (new ChatAgent)->forUser($user)->prompt('Hello!');
$id = $response->conversationId;

// Continue
$response = (new ChatAgent)->continue($id, as: $user)->prompt('Follow up...');
```

Requires the `agent_conversations` and `agent_conversation_messages` tables (included in migrations).

---

## Semantic Memory

For agents that should remember facts across sessions, see [ai-memory.md](./ai-memory.md). The `AssistantAgent` (`app/Ai/Agents/AssistantAgent.php`) is a full example with `StoreMemory` / `RecallMemory` tools and `WithMemoryUnlessUnavailable` middleware.

---

## Streaming

Returns a Laravel `StreamedResponse` suitable for returning directly from a route:

```php
// In a controller
return (new SupportAgent)->stream('Explain this error...');
```

See `app/Http/Controllers/Api/ChatController.php` for the full streaming implementation using NDJSON + TanStack AG-UI events.

---

## Tools

```bash
php artisan make:tool SearchUsers
```

Tools live in `app/Ai/Tools/`. Implement `handle(Request $request)` and `schema(JsonSchema $schema)`. Attach to an agent via `HasTools`:

```php
use Laravel\Ai\Contracts\HasTools;

final class MyAgent implements Agent, HasTools
{
    use Promptable;

    public function tools(): iterable
    {
        return [new SearchUsers];
    }
}
```

See `app/Ai/Tools/UsersIndex.php` and `app/Ai/Tools/UsersShow.php` for existing examples.

---

## Provider-Native Tools

These are executed by the AI provider, not your app:

```php
use Laravel\Ai\Providers\Tools\{WebSearch, WebFetch, FileSearch};

public function tools(): iterable
{
    return [
        (new WebSearch)->max(5)->allow(['laravel.com']),
        new WebFetch,
    ];
}
```

---

## Images, Audio, Embeddings

```php
use Laravel\Ai\{Image, Audio, Embeddings, Reranking, Transcription};

// Image generation
$image = Image::of('A sunset over mountains')->landscape()->generate();
$path  = $image->store();

// Text-to-speech
$audio = Audio::of('Hello from Laravel.')->female()->generate();

// Speech-to-text
$transcript = Transcription::fromStorage('recording.mp3')->generate();

// Embeddings
$response = Embeddings::for(['Text one', 'Text two'])->generate();

// Reranking
$ranked = Reranking::of($documents)->rerank('PHP frameworks');
```

---

## Testing

Every capability provides a `fake()` method with assertions:

```php
use App\Ai\Agents\SupportAgent;
use Laravel\Ai\{Image, Audio, Embeddings};

// Agents
SupportAgent::fake(['Response text']);
(new SupportAgent)->prompt('Hello');
SupportAgent::assertPrompted('Hello');
SupportAgent::assertNeverPrompted();

// Images
Image::fake();
Image::assertGenerated(fn ($p) => str_contains($p, 'sunset'));

// Embeddings
Embeddings::fake();
Embeddings::assertGenerated(fn ($p) => $p->contains('Laravel'));
```

---

For MCP tool integration via Relay, see [prism.md](./prism.md).
For semantic agent memory, see [ai-memory.md](./ai-memory.md).
