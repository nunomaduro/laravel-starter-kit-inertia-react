# Laravel AI SDK

This project includes the [Laravel AI SDK](https://laravel.com/docs/12.x/ai-sdk) alongside [Prism](./prism.md). The two stacks are **complementary**: use each where it fits best.

## When to Use Laravel AI SDK vs Prism

| Use case | Use this |
|----------|----------|
| **OpenRouter**, many models via one API, ad-hoc text/structured | **Prism** (`ai()` helper, `PrismService`) |
| **Seed commands**, AI seeder generation, docs generation | **Prism** (existing commands) |
| **MCP / Relay** tool calling from Prism | **Prism** + Relay |
| **Agents** (instructions, conversation storage, tools, schema) | **Laravel AI SDK** |
| **Embeddings** generation (`Str::toEmbeddings()`, `Embeddings::for()`) | **Laravel AI SDK** |
| **Images, TTS, STT**, reranking, files, vector stores | **Laravel AI SDK** |
| **Provider tools** (WebSearch, WebFetch, FileSearch) | **Laravel AI SDK** |
| **RAG** with `SimilaritySearch` tool + pgvector | **Laravel AI SDK** (agents) + existing pgvector storage |

**Rule of thumb:** Prism = OpenRouter + ad-hoc calls + existing app commands. Laravel AI = agents, embeddings, media, and first-party provider features.

## Configuration

Laravel AI is configured in `config/ai.php`. Provider API keys are read from `.env` (e.g. `OPENAI_API_KEY`, `ANTHROPIC_API_KEY`). The same `OPENROUTER_API_KEY` used by Prism is also used by the Laravel AI `openrouter` provider when you choose it for an agent.

Defaults in config:

- **Text:** `openai` (override per agent or call)
- **Images:** `gemini`
- **Embeddings:** `openai`
- **Reranking:** `cohere`

Set the keys for the providers you use. Optional keys are documented in `.env.example`.

## Agents

Create an agent with:

```bash
php artisan make:agent SalesCoach
php artisan make:agent DocumentAnalyzer --structured
```

Agents live in `app/Ai/Agents/`. Implement `instructions()`, optional `messages()`, `tools()`, and `schema()` for structured output. Use `RemembersConversations` for persistence (uses `agent_conversations` and `agent_conversation_messages` tables).

```php
$response = (new SalesCoach)->forUser($user)->prompt('Hello!');
// Or with conversation: ->continue($conversationId, as: $user)->prompt('Follow-up');
```

## Agent memory (semantic store/recall)

For agents that should **remember** facts across conversations (user preferences, prior decisions, context), use [Laravel AI Memory](./ai-memory.md) (eznix86/laravel-ai-memory). It provides:

- **AgentMemory** facade: `store()`, `recall()`, `forget()`, `forgetAll()`
- **StoreMemory / RecallMemory** tools so the agent can save and look up facts
- **WithMemory** middleware to inject relevant memories into each prompt

The app includes `App\Ai\Agents\AssistantAgent` as an example agent with memory. Requires PostgreSQL + pgvector and `config/ai.php` embeddings/reranking (e.g. OpenAI, Cohere).

## Embeddings and Vectors

- **Generate embeddings:** Laravel AI `Embeddings::for([...])->generate()` or `Str::of('...')->toEmbeddings()`.
- **Store and query:** Use existing [pgvector](./pgvector.md) setup (pgvector package + `embedding_demos` or your own tables). Laravel 12’s `whereVectorSimilarTo` can be used if you adopt the framework’s vector column support; the project currently uses the `pgvector/pgvector` package and `HasNeighbors` for queries. The **memories** table (from [laravel-ai-memory](./ai-memory.md)) also uses pgvector for agent memory.

For RAG in an agent, use the SDK’s `SimilaritySearch` tool with a model that has an `embedding` column.

## Migrations

Running `php artisan migrate` creates:

- `agent_conversations` – conversation metadata (user, title)
- `agent_conversation_messages` – messages per conversation

These are used by the `RemembersConversations` trait.

## Testing

Laravel AI provides fakes for agents, embeddings, images, etc. Example:

```php
use App\Ai\Agents\SalesCoach;

SalesCoach::fake(['Expected response']);
$response = (new SalesCoach)->prompt('...');
SalesCoach::assertPrompted('...');
```

See the [Laravel AI SDK documentation](https://laravel.com/docs/12.x/ai-sdk) for full coverage of agents, tools, embeddings, images, audio, and testing.
