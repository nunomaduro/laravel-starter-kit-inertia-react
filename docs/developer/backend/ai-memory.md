# Laravel AI Memory

This project uses [eznix86/laravel-ai-memory](https://github.com/eznix86/laravel-ai-memory) to give Laravel AI agents **semantic memory**: store and recall facts across conversations using embeddings and pgvector.

## Requirements

- **Laravel AI SDK** (config/ai.php) with embeddings and reranking providers (e.g. OpenAI for embeddings, Cohere for reranking).
- **PostgreSQL** with **pgvector** extension (same as [pgvector.md](./pgvector.md)).
- Migrations have been run (`memories` table).

**Testing:** The app registers a custom `App\Providers\MemoryServiceProvider` that loads the package’s migrations only when the default DB connection is PostgreSQL. This allows the test suite to run with SQLite (phpunit.xml default). The `tests/Feature/AgentMemoryTest.php` tests are skipped when not using PostgreSQL.

## What It Does

- **Store** — Save a fact (e.g. "User prefers dark mode") with optional context (e.g. `user_id`). Content is embedded and stored in the `memories` table.
- **Recall** — Given a query (e.g. "What are the user's preferences?"), retrieve relevant memories by semantic similarity, then rerank for quality.
- **WithMemory middleware** — Before each agent prompt, relevant memories are recalled and prepended as context so the agent "remembers" without you passing full history.
- **StoreMemory / RecallMemory tools** — The agent can decide when to save or look up memories via Laravel AI tools.

## Configuration

Config is in `config/memory.php` (publish with `php artisan vendor:publish --tag=memory-config`). Options:

| Option | Default | Description |
|--------|---------|-------------|
| `dimensions` | 1536 | Embedding vector size; must match your embedding model (OpenAI text-embedding-3-small = 1536). |
| `similarity_threshold` | 0.5 | Minimum cosine similarity for recall (0–1). |
| `recall_limit` | 10 | Max memories returned by `recall()`. |
| `middleware_recall_limit` | 5 | Max memories injected by WithMemory middleware. |
| `recall_oversample_factor` | 2 | Candidates fetched before reranking (limit × factor). |
| `table` | memories | Database table name. |

Environment variables (optional): `MEMORY_DIMENSIONS`, `MEMORY_SIMILARITY_THRESHOLD`, `MEMORY_RECALL_LIMIT`, `MEMORY_MIDDLEWARE_RECALL_LIMIT`, `MEMORY_RECALL_OVERSAMPLE_FACTOR`, `MEMORY_TABLE`. See `.env.example`.

## Usage

### AgentMemory facade

```php
use Eznix86\AI\Memory\Facades\AgentMemory;

// Store a memory (scoped by context)
$memory = AgentMemory::store('User prefers dark mode', ['user_id' => $userId]);

// Recall relevant memories (semantic search + reranking)
$memories = AgentMemory::recall('What are the user preferences?', ['user_id' => $userId], limit: 5);

// All memories for a context
$all = AgentMemory::all(['user_id' => $userId]);

// Forget one or all
AgentMemory::forget($memory->id);
AgentMemory::forgetAll(['user_id' => $userId]);
```

### Agent with memory (AssistantAgent)

The app includes an example agent that uses memory tools and WithMemory middleware:

```php
use App\Ai\Agents\AssistantAgent;

$agent = new AssistantAgent(['user_id' => auth()->id()]);
$response = $agent->prompt('What do you remember about my preferences?');
echo $response->text;
```

- **Context** — Pass e.g. `['user_id' => $user->id]` so memories are scoped per user (or per organization, etc.).
- **Tools** — The model can call Store Memory and Recall Memory when appropriate.
- **Middleware** — WithMemory injects relevant memories into every prompt automatically.

### Building your own agent with memory

Implement `Agent`, `HasTools`, and optionally `HasMiddleware`; use `Promptable`; add the memory tools and WithMemory:

```php
use Eznix86\AI\Memory\Middleware\WithMemory;
use Eznix86\AI\Memory\Tools\RecallMemory;
use Eznix86\AI\Memory\Tools\StoreMemory;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

class MyAgent implements Agent, HasTools, HasMiddleware
{
    use Promptable;

    public function __construct(protected array $context = []) {}

    public function instructions(): \Stringable|string
    {
        return 'You are a helpful assistant with memory. Use Store Memory to save facts; use Recall Memory to look them up.';
    }

    public function tools(): iterable
    {
        return [
            (new RecallMemory)->context($this->context),
            (new StoreMemory)->context($this->context),
        ];
    }

    public function middleware(): array
    {
        return [new WithMemory($this->context)];
    }
}
```

## Testing

Use `AgentMemory::fake()` so store/recall use deterministic embeddings and no real API calls:

```php
use Eznix86\AI\Memory\Facades\AgentMemory;

test('agent remembers user preferences', function () {
    AgentMemory::fake();

    AgentMemory::store('User prefers dark mode', ['user_id' => 'user-123']);
    $memories = AgentMemory::recall('preferences', ['user_id' => 'user-123']);

    expect($memories)->toHaveCount(1)
        ->and($memories->first()->content)->toBe('User prefers dark mode');
});
```

When testing an agent that uses WithMemory, `AgentMemory::fake()` is enough; no need to fake Embeddings or Reranking separately.

## Related

- [Laravel AI SDK](./ai-sdk.md) — Agents, embeddings, and when to use vs Prism.
- [PostgreSQL + pgvector](./pgvector.md) — Vector extension and existing embedding usage.
