# Laravel AI SDK - Complete Guide

**Version:** 1.0.0
**Laravel Version:** 13.x
**PHP Version:** 8.3+
**Organization:** Laravel Community
**Date:** March 2026

## Overview

Comprehensive guide for building AI-powered features with the Laravel AI SDK (`laravel/ai`). Contains 17 rules across 7 categories covering agents, tools, media generation, embeddings, files, infrastructure, and testing.

### Key Features

- Agent classes with instructions, tools, and structured output
- Conversation context with RemembersConversations
- Streaming responses with SSE and Vercel AI SDK protocol
- Custom tools with JSON schema validation
- Provider tools: WebSearch, WebFetch, FileSearch, SimilaritySearch
- Image generation and audio TTS/STT
- Vector embeddings with caching and reranking
- File and vector store management for RAG
- Provider failover for resilience
- Comprehensive faking and assertion API for testing

## Categories

This guide is organized into 7 categories, prioritized by their impact on AI-powered applications:

1. **Agents** (CRITICAL) — Creating, configuring, and prompting AI agents
2. **Tools** (HIGH) — Custom tools and provider tools for agents
3. **Embeddings & Search** (HIGH) — Vector embeddings, similarity search, and reranking
4. **Media Generation** (MEDIUM) — Image generation, text-to-speech, and transcription
5. **Files & Storage** (MEDIUM) — File storage with providers and vector stores for RAG
6. **Infrastructure** (MEDIUM) — Provider failover for resilience
7. **Testing** (HIGH) — Faking AI responses and asserting prompts in tests

### References

- [Laravel AI SDK Documentation](https://laravel.com/docs/13.x/ai-sdk)
- [Laravel AI SDK Repository](https://github.com/laravel/ai)

---

title: Create and Configure Agents
impact: CRITICAL
impactDescription: Foundation for all AI interactions in Laravel
tags: agent, create, configure, attributes, provider, model
---

## Create and Configure Agents

**Impact: CRITICAL (Foundation for all AI interactions in Laravel)**

Agents are dedicated PHP classes that encapsulate instructions, tools, and output schemas for interacting with AI providers. Create agents with `make:agent` and configure them with PHP attributes.

## Bad Example

```php
// Hardcoded API calls scattered across controllers
class ChatController extends Controller
{
    public function respond(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => ['Authorization' => 'Bearer ' . config('services.openai.key')],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a sales coach.'],
                    ['role' => 'user', 'content' => $request->input('message')],
                ],
            ],
        ]);

        // Provider-specific, untestable, no structure
        return json_decode($response->getBody(), true);
    }
}
```

## Good Example

```php
// Generate with artisan
// php artisan make:agent SalesCoach
// php artisan make:agent SalesCoach --structured

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class SalesCoach implements Agent
{
    use Promptable;

    public function __construct(public User $user) {}

    public function instructions(): string
    {
        return 'You are a sales coach, analyzing transcripts and providing feedback.';
    }
}
```

```php
// Configure with PHP attributes — provider, model, limits
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider(Lab::Anthropic)]
#[Model('claude-haiku-4-5-20251001')]
#[MaxSteps(10)]
#[MaxTokens(4096)]
#[Temperature(0.7)]
#[Timeout(120)]
class SalesCoach implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a sales coach.';
    }
}
```

```php
// Auto-select cheapest or smartest model
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Attributes\UseSmartestModel;

#[UseCheapestModel]
class SimpleSummarizer implements Agent { /* ... */ }

#[UseSmartestModel]
class ComplexReasoner implements Agent { /* ... */ }
```

```php
// Provider-specific options (reasoning effort, penalties)
use Laravel\Ai\Contracts\HasProviderOptions;
use Laravel\Ai\Enums\Lab;

class SalesCoach implements Agent, HasProviderOptions
{
    use Promptable;

    public function providerOptions(Lab|string $provider): array
    {
        return match ($provider) {
            Lab::OpenAI => ['reasoning' => ['effort' => 'low']],
            Lab::Anthropic => ['thinking' => ['budget_tokens' => 1024]],
            default => [],
        };
    }
}
```

## Why

- **Provider-agnostic**: Switch between OpenAI, Anthropic, Gemini without code changes
- **Testable**: Agents can be faked in tests — raw API calls cannot
- **Declarative**: PHP attributes make configuration visible at the class level
- **Container-resolved**: `make()` injects dependencies automatically

Reference: [Laravel AI SDK — Agents](https://laravel.com/docs/13.x/ai-sdk#agents)

---

title: Prompting Agents and Conversation Context
impact: CRITICAL
impactDescription: Core interaction pattern for all AI agent usage
tags: agent, prompt, conversation, context, remember
---

## Prompting Agents and Conversation Context

**Impact: CRITICAL (Core interaction pattern for all AI agent usage)**

Prompt agents with `prompt()`, pass conversation history with the `Conversational` interface, or use `RemembersConversations` for automatic persistence.

## Bad Example

```php
// No conversation history — agent has no memory between requests
class ChatController extends Controller
{
    public function respond(Request $request)
    {
        // Every request starts fresh — no context from previous messages
        $response = (new SalesCoach)->prompt($request->input('message'));

        return response()->json(['reply' => (string) $response]);
    }
}
```

## Good Example

```php
// Prompt with make() for dependency injection
use App\Ai\Agents\SalesCoach;

$agent = SalesCoach::make(user: $user);
$response = $agent->prompt('Analyze this sales transcript...');

return (string) $response;
```

```php
// Override provider or model per-prompt
use Laravel\Ai\Enums\Lab;

$response = (new SalesCoach)->prompt(
    'Analyze this sales transcript...',
    provider: Lab::Anthropic,
    model: 'claude-haiku-4-5-20251001',
    timeout: 120,
);
```

```php
// Attach files to a prompt
use Laravel\Ai\Files;

$response = (new SalesCoach)->prompt(
    'Analyze the attached sales transcript...',
    attachments: [
        Files\Document::fromStorage('transcript.pdf'),
        Files\Image::fromPath('/home/laravel/chart.png'),
        $request->file('transcript'),
    ],
);
```

```php
// Manual conversation context — implement Conversational
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Messages\Message;

class SalesCoach implements Agent, Conversational
{
    use Promptable;

    public function __construct(public User $user) {}

    public function instructions(): string
    {
        return 'You are a sales coach.';
    }

    public function messages(): iterable
    {
        return History::where('user_id', $this->user->id)
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->map(fn ($msg) => new Message($msg->role, $msg->content))
            ->all();
    }
}
```

```php
// Automatic conversation persistence — RemembersConversations
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Conversational;

class SalesCoach implements Agent, Conversational
{
    use Promptable, RemembersConversations;

    public function instructions(): string
    {
        return 'You are a sales coach.';
    }
}

// Start a new conversation
$response = (new SalesCoach)->forUser($user)->prompt('Hello!');
$conversationId = $response->conversationId;

// Continue an existing conversation
$response = (new SalesCoach)
    ->continue($conversationId, as: $user)
    ->prompt('Tell me more about that.');
```

## Why

- **Stateful conversations**: Users can have multi-turn conversations with agents
- **Automatic persistence**: `RemembersConversations` stores messages to database
- **Flexible context**: Implement `messages()` for custom storage (Redis, external API, etc.)
- **Attachments**: Send documents and images alongside prompts

Reference: [Laravel AI SDK — Prompting](https://laravel.com/docs/13.x/ai-sdk#prompting)

---

title: Structured Output with JSON Schema
impact: HIGH
impactDescription: Type-safe, parseable AI responses
tags: agent, structured-output, schema, json
---

## Structured Output with JSON Schema

**Impact: HIGH (Type-safe, parseable AI responses)**

Implement `HasStructuredOutput` to force agents to return data matching a defined JSON schema. Access the response like an array instead of parsing free-form text.

## Bad Example

```php
// Parsing free-form text — fragile and error-prone
$response = (new SalesCoach)->prompt('Rate this transcript 1-10 and give feedback.');

// Trying to extract structured data from prose
preg_match('/score[:\s]*(\d+)/i', (string) $response, $matches);
$score = (int) ($matches[1] ?? 0); // Unreliable
```

## Good Example

```php
// php artisan make:agent SalesCoach --structured

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class SalesCoach implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a sales coach. Analyze transcripts and return structured feedback.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'feedback' => $schema->string()->required(),
            'score' => $schema->integer()->min(1)->max(10)->required(),
        ];
    }
}
```

```php
// Access structured response like an array
$response = (new SalesCoach)->prompt('Analyze this sales transcript...');

$score = $response['score'];       // int — guaranteed 1-10
$feedback = $response['feedback'];  // string — guaranteed present
```

## Why

- **Reliable parsing**: No regex or string matching — response is always valid JSON
- **Type guarantees**: Schema enforces types, ranges, and required fields
- **Testable**: Faking structured agents auto-generates matching fake data
- **API-ready**: Return structured responses directly from API endpoints

Reference: [Laravel AI SDK — Structured Output](https://laravel.com/docs/13.x/ai-sdk#structured-output)

---

title: Streaming, Broadcasting, and Queueing
impact: HIGH
impactDescription: Real-time and background AI processing
tags: agent, streaming, broadcast, queue, sse, vercel
---

## Streaming, Broadcasting, and Queueing

**Impact: HIGH (Real-time and background AI processing)**

Stream agent responses for real-time UX, broadcast to channels for live updates, or queue prompts for background processing.

## Bad Example

```php
// Blocking request — user waits for entire response before seeing anything
Route::post('/coach', function (Request $request) {
    $response = (new SalesCoach)->prompt($request->input('message'));

    // User stares at spinner for 10+ seconds
    return response()->json(['reply' => (string) $response]);
});
```

## Good Example

```php
// Stream — return SSE directly from route
use App\Ai\Agents\SalesCoach;

Route::get('/coach', function () {
    return (new SalesCoach)->stream('Analyze this sales transcript...');
});
```

```php
// Stream with completion callback
use Laravel\Ai\Responses\StreamedAgentResponse;

Route::get('/coach', function () {
    return (new SalesCoach)
        ->stream('Analyze this sales transcript...')
        ->then(function (StreamedAgentResponse $response) {
            // $response->text, $response->events, $response->usage
        });
});
```

```php
// Stream using Vercel AI SDK protocol (for Next.js/React frontends)
Route::get('/coach', function () {
    return (new SalesCoach)
        ->stream('Analyze this sales transcript...')
        ->usingVercelDataProtocol();
});
```

```php
// Iterate through stream events manually
$stream = (new SalesCoach)->stream('Analyze this transcript...');

foreach ($stream as $event) {
    // Process each chunk
}
```

```php
// Broadcast streamed events to a channel
use Illuminate\Broadcasting\Channel;

$stream = (new SalesCoach)->stream('Analyze this transcript...');

foreach ($stream as $event) {
    $event->broadcast(new Channel('channel-name'));
}

// Or queue the entire operation and broadcast automatically
(new SalesCoach)->broadcastOnQueue(
    'Analyze this transcript...',
    new Channel('channel-name'),
);
```

```php
// Queue — process in background with then/catch callbacks
use Laravel\Ai\Responses\AgentResponse;

Route::post('/coach', function (Request $request) {
    (new SalesCoach)
        ->queue($request->input('transcript'))
        ->then(function (AgentResponse $response) {
            // Handle response in background
        })
        ->catch(function (Throwable $e) {
            // Handle failure
        });

    return back();
});
```

## Why

- **Better UX**: Streaming shows tokens as they arrive — no waiting for full response
- **Scalable**: Queueing offloads long prompts to background workers
- **Real-time**: Broadcasting pushes updates to WebSocket clients
- **Protocol support**: Vercel AI SDK protocol for seamless React/Next.js integration

Reference: [Laravel AI SDK — Streaming](https://laravel.com/docs/13.x/ai-sdk#streaming)

---

title: Agent Middleware
impact: MEDIUM
impactDescription: Intercept and modify prompts before they reach the provider
tags: agent, middleware, logging, pipeline
---

## Agent Middleware

**Impact: MEDIUM (Intercept and modify prompts before they reach the provider)**

Agent middleware intercepts prompts before they are sent to the AI provider. Use middleware for logging, token tracking, prompt modification, or response post-processing.

## Bad Example

```php
// Logging scattered inside the agent or controller
class SalesCoach implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        Log::info('SalesCoach prompted'); // Logging in wrong place
        return 'You are a sales coach.';
    }
}
```

## Good Example

```php
// php artisan make:agent-middleware LogPrompts

namespace App\Ai\Middleware;

use Closure;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;

class LogPrompts
{
    public function handle(AgentPrompt $prompt, Closure $next)
    {
        Log::info('Prompting agent', ['prompt' => $prompt->prompt]);

        return $next($prompt)->then(function (AgentResponse $response) {
            Log::info('Agent responded', ['text' => $response->text]);
        });
    }
}
```

```php
// Attach middleware to agent via HasMiddleware interface
use App\Ai\Middleware\LogPrompts;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Promptable;

class SalesCoach implements Agent, HasMiddleware
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a sales coach.';
    }

    public function middleware(): array
    {
        return [
            new LogPrompts,
        ];
    }
}
```

## Why

- **Separation of concerns**: Logging, tracking, and modification outside agent logic
- **Reusable**: Same middleware applied to multiple agents
- **Pipeline**: Stack multiple middleware for complex requirements
- **Post-processing**: `then()` on the response for after-prompt logic

Reference: [Laravel AI SDK — Middleware](https://laravel.com/docs/13.x/ai-sdk#middleware)

---

title: Anonymous Agents
impact: MEDIUM
impactDescription: Quick AI interactions without dedicated agent classes
tags: agent, anonymous, inline, ad-hoc
---

## Anonymous Agents

**Impact: MEDIUM (Quick AI interactions without dedicated agent classes)**

Use the `agent()` function for one-off prompts that don't warrant a dedicated agent class. Supports instructions, messages, tools, and structured output inline.

## Bad Example

```php
// Creating a full agent class for a single-use, trivial prompt
// app/Ai/Agents/QuickTranslator.php — overkill for a one-liner
class QuickTranslator implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You translate text to French.';
    }
}

// Used exactly once
$response = (new QuickTranslator)->prompt('Hello, how are you?');
```

## Good Example

```php
use function Laravel\Ai\{agent};

// Simple anonymous agent
$response = agent(
    instructions: 'You are an expert at software development.',
)->prompt('Tell me about Laravel');
```

```php
// Anonymous agent with structured output
use Illuminate\Contracts\JsonSchema\JsonSchema;
use function Laravel\Ai\{agent};

$response = agent(
    schema: fn (JsonSchema $schema) => [
        'number' => $schema->integer()->required(),
    ],
)->prompt('Generate a random number less than 100');

$number = $response['number']; // int
```

```php
// Anonymous agent with tools and messages
use function Laravel\Ai\{agent};

$response = agent(
    instructions: 'You are a helpful assistant.',
    messages: $previousMessages,
    tools: [new MyCustomTool],
)->prompt('Search for relevant documents');
```

## Why

- **No boilerplate**: Skip creating a class for trivial, one-off prompts
- **Full-featured**: Supports instructions, tools, messages, and structured output
- **Inline**: Keep simple AI calls close to where they're used
- **Upgrade path**: Extract to a dedicated agent class when complexity grows

Reference: [Laravel AI SDK — Anonymous Agents](https://laravel.com/docs/13.x/ai-sdk#anonymous-agents)

---

title: Create Custom Tools
impact: HIGH
impactDescription: Give agents abilities beyond text generation
tags: tool, create, schema, handle, agent
---

## Create Custom Tools

**Impact: HIGH (Give agents abilities beyond text generation)**

Tools let agents call your application code — generate random numbers, query databases, send emails. Each tool has a description, input schema, and handle method.

## Bad Example

```php
// Putting tool logic inside the agent — unstructured and untestable
class SalesCoach implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        // Trying to describe tools in prose — model can't actually call them
        return 'You are a sales coach. When asked for random numbers, generate one between 1-100.';
    }
}
```

## Good Example

```php
// php artisan make:tool RandomNumberGenerator

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class RandomNumberGenerator implements Tool
{
    public function description(): string
    {
        return 'Generate a cryptographically secure random number within a range.';
    }

    public function handle(Request $request): string
    {
        return (string) random_int($request['min'], $request['max']);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'min' => $schema->integer()->min(0)->required(),
            'max' => $schema->integer()->required(),
        ];
    }
}
```

```php
// Register tools on an agent
use App\Ai\Tools\RandomNumberGenerator;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

class SalesCoach implements Agent, HasTools
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a sales coach.';
    }

    public function tools(): iterable
    {
        return [
            new RandomNumberGenerator,
        ];
    }
}
```

```php
// Tool with dependency injection — constructor and handle method
namespace App\Ai\Tools;

use App\Repositories\WeatherRepository;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetWeather implements Tool
{
    public function __construct(
        private readonly WeatherRepository $weather,
    ) {}

    public function description(): string
    {
        return 'Get current weather for a location.';
    }

    public function handle(Request $request, WeatherRepository $weather): string
    {
        return $weather->getForecastFor($request['location']);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'location' => $schema->string()
                ->description('City name or coordinates')
                ->required(),
        ];
    }
}
```

## Why

- **Structured**: JSON schema defines exact inputs the model can send
- **Testable**: Tools are independent classes with clear inputs and outputs
- **DI support**: Laravel container resolves constructor and handle dependencies
- **Composable**: Mix and match tools across different agents

Reference: [Laravel AI SDK — Tools](https://laravel.com/docs/13.x/ai-sdk#tools)

---

title: Provider Tools
impact: HIGH
impactDescription: Built-in web search, URL fetching, file search, and similarity search
tags: tool, provider, web-search, web-fetch, file-search, similarity-search, rag
---

## Provider Tools

**Impact: HIGH (Built-in web search, URL fetching, file search, and similarity search)**

Provider tools are executed by the AI provider itself — web searching, URL fetching, and file searching. SimilaritySearch runs locally against your database for RAG.

## Bad Example

```php
// Building custom web search tool from scratch
class WebSearchTool implements Tool
{
    public function handle(Request $request): string
    {
        // Manual Google API integration — unnecessary
        $client = new \GuzzleHttp\Client();
        $response = $client->get('https://www.googleapis.com/customsearch/v1', [
            'query' => ['key' => config('services.google.key'), 'q' => $request['query']],
        ]);

        return (string) $response->getBody();
    }
}
```

## Good Example

```php
// WebSearch — provider searches the web for real-time info
use Laravel\Ai\Providers\Tools\WebSearch;

public function tools(): iterable
{
    return [
        new WebSearch,
    ];
}

// With limits and domain restrictions
(new WebSearch)->max(5)->allow(['laravel.com', 'php.net']);

// With location context
(new WebSearch)->location(city: 'New York', region: 'NY', country: 'US');
```

```php
// WebFetch — provider reads web page contents
use Laravel\Ai\Providers\Tools\WebFetch;

public function tools(): iterable
{
    return [
        (new WebFetch)->max(3)->allow(['docs.laravel.com']),
    ];
}
```

```php
// FileSearch — search files in vector stores (RAG)
use Laravel\Ai\Providers\Tools\FileSearch;

public function tools(): iterable
{
    return [
        new FileSearch(stores: ['store_1', 'store_2']),
    ];
}

// With metadata filters
use Laravel\Ai\Providers\Tools\FileSearchQuery;

new FileSearch(stores: ['store_id'], where: fn (FileSearchQuery $query) =>
    $query->where('author', 'Taylor Otwell')
        ->whereNot('status', 'draft')
        ->whereIn('category', ['news', 'updates'])
);
```

```php
// SimilaritySearch — search your own database with vector embeddings
use App\Models\Document;
use Laravel\Ai\Tools\SimilaritySearch;

public function tools(): iterable
{
    return [
        SimilaritySearch::usingModel(Document::class, 'embedding'),
    ];
}

// With similarity threshold and query constraints
SimilaritySearch::usingModel(
    model: Document::class,
    column: 'embedding',
    minSimilarity: 0.7,
    limit: 10,
    query: fn ($query) => $query->where('published', true),
);

// Custom closure for full control
new SimilaritySearch(using: function (string $query) {
    return Document::query()
        ->where('user_id', $this->user->id)
        ->whereVectorSimilarTo('embedding', $query)
        ->limit(10)
        ->get();
});
```

## Why

- **Zero setup**: WebSearch and WebFetch are provider-native — no API keys or configuration
- **Domain safety**: Restrict web search and fetch to trusted domains
- **RAG-ready**: FileSearch + SimilaritySearch enable retrieval-augmented generation
- **Composable**: Combine provider tools with custom tools on the same agent

Reference: [Laravel AI SDK — Provider Tools](https://laravel.com/docs/13.x/ai-sdk#provider-tools)

---

title: Generate and Cache Embeddings
impact: HIGH
impactDescription: Foundation for semantic search and RAG features
tags: embeddings, vector, generate, cache, pgvector, search
---

## Generate and Cache Embeddings

**Impact: HIGH (Foundation for semantic search and RAG features)**

Generate vector embeddings from text, store them in pgvector columns, and cache to avoid redundant API calls.

## Bad Example

```php
// Generating embeddings on every request — wasteful and slow
class SearchController extends Controller
{
    public function search(Request $request)
    {
        // Calls embedding API every time, even for identical queries
        $embedding = Http::post('https://api.openai.com/v1/embeddings', [
            'input' => $request->input('query'),
            'model' => 'text-embedding-3-small',
        ])->json('data.0.embedding');

        // Manual similarity calculation
        // ...
    }
}
```

## Good Example

```php
// Generate embeddings with Stringable helper
use Illuminate\Support\Str;

$embeddings = Str::of('Napa Valley has great wine.')->toEmbeddings();
```

```php
// Batch generation for multiple inputs
use Laravel\Ai\Embeddings;

$response = Embeddings::for([
    'Napa Valley has great wine.',
    'Laravel is a PHP framework.',
])->generate();

$response->embeddings; // [[0.123, 0.456, ...], [0.789, 0.012, ...]]
```

```php
// Specify dimensions and provider
use Laravel\Ai\Enums\Lab;

$response = Embeddings::for(['Napa Valley has great wine.'])
    ->dimensions(1536)
    ->generate(Lab::OpenAI, 'text-embedding-3-small');
```

```php
// Cache embeddings to avoid redundant API calls
$response = Embeddings::for(['Napa Valley has great wine.'])
    ->cache()
    ->generate();

// Cache with custom duration
$response = Embeddings::for(['Napa Valley has great wine.'])
    ->cache(seconds: 3600)
    ->generate();

// Cache via Stringable
$embeddings = Str::of('Napa Valley has great wine.')->toEmbeddings(cache: true);
$embeddings = Str::of('Napa Valley has great wine.')->toEmbeddings(cache: 3600);
```

```php
// Enable global caching in config/ai.php
'caching' => [
    'embeddings' => [
        'cache' => true,
        'store' => env('CACHE_STORE', 'database'),
    ],
],
```

```php
// Store and query embeddings — see laravel-best-practices/eloquent-vector-search
use App\Models\Document;

Document::create([
    'title' => 'Wine Guide',
    'content' => $content,
    'embedding' => Str::of($content)->toEmbeddings(),
]);

$results = Document::query()
    ->whereVectorSimilarTo('embedding', 'best wineries in Napa Valley')
    ->limit(10)
    ->get();
```

## Why

- **Simple API**: `Str::of(...)->toEmbeddings()` — one line to generate
- **Caching**: Avoid paying for identical embedding requests
- **Batch support**: Generate multiple embeddings in a single API call
- **Provider-agnostic**: OpenAI, Gemini, Azure, Cohere, Mistral, Jina, VoyageAI

Reference: [Laravel AI SDK — Embeddings](https://laravel.com/docs/13.x/ai-sdk#embeddings)

---

title: Rerank Documents by Relevance
impact: MEDIUM
impactDescription: Improve search quality with semantic reranking
tags: rerank, search, relevance, collection, cohere, jina
---

## Rerank Documents by Relevance

**Impact: MEDIUM (Improve search quality with semantic reranking)**

Rerank search results using semantic understanding to surface the most relevant documents. Works with arrays, Eloquent collections, and closures.

## Bad Example

```php
// Relying solely on keyword matching — misses semantic relevance
$posts = Post::where('title', 'like', '%PHP%')
    ->orWhere('body', 'like', '%PHP%')
    ->orderBy('created_at', 'desc')
    ->get();
// "PHP frameworks" query returns all posts mentioning PHP, in wrong order
```

## Good Example

```php
// Rerank an array of strings
use Laravel\Ai\Reranking;

$response = Reranking::of([
    'Django is a Python web framework.',
    'Laravel is a PHP web application framework.',
    'React is a JavaScript library for building user interfaces.',
])->rerank('PHP frameworks');

$response->first()->document; // "Laravel is a PHP web application framework."
$response->first()->score;    // 0.95
$response->first()->index;    // 1 (original position)
```

```php
// Limit results
$response = Reranking::of($documents)
    ->limit(5)
    ->rerank('search query');
```

```php
// Rerank Eloquent collections — single field
$posts = Post::all()->rerank('body', 'Laravel tutorials');

// Multiple fields (sent as JSON)
$posts = Post::all()->rerank(['title', 'body'], 'Laravel tutorials');

// Custom closure to build the document string
$posts = Post::all()->rerank(
    fn ($post) => $post->title . ': ' . $post->body,
    'Laravel tutorials',
);
```

```php
// Specify provider and limit
use Laravel\Ai\Enums\Lab;

$reranked = $posts->rerank(
    by: 'content',
    query: 'Laravel tutorials',
    limit: 10,
    provider: Lab::Cohere,
);
```

## Why

- **Semantic relevance**: Reranking understands meaning, not just keywords
- **Collection macro**: Works directly on Eloquent collections — `$posts->rerank(...)`
- **Flexible input**: Single field, multiple fields, or custom closure
- **Provider support**: Cohere and Jina for reranking

Reference: [Laravel AI SDK — Reranking](https://laravel.com/docs/13.x/ai-sdk#reranking)

---

title: Image Generation
impact: MEDIUM
impactDescription: Generate images from text prompts
tags: image, generate, store, queue, openai, gemini
---

## Image Generation

**Impact: MEDIUM (Generate images from text prompts)**

Use `Laravel\Ai\Image` to generate images from text prompts. Supports aspect ratios, quality settings, reference images, storage, and background queueing.

## Bad Example

```php
// Manual API call — provider-specific, no storage helpers
$response = Http::withToken(config('services.openai.key'))
    ->post('https://api.openai.com/v1/images/generations', [
        'prompt' => 'A donut on a kitchen counter',
        'size' => '1024x1024',
    ]);

$imageUrl = $response->json('data.0.url');
// Now manually download and store...
```

## Good Example

```php
use Laravel\Ai\Image;

// Generate an image
$image = Image::of('A donut sitting on the kitchen counter')->generate();
$rawContent = (string) $image;
```

```php
// Aspect ratio and quality
$image = Image::of('A donut sitting on the kitchen counter')
    ->quality('high')
    ->landscape()  // or ->portrait(), ->square()
    ->timeout(120)
    ->generate();
```

```php
// Reference images for style transfer
use Laravel\Ai\Files;

$image = Image::of('Update this photo to impressionist style.')
    ->attachments([
        Files\Image::fromStorage('photo.jpg'),
        Files\Image::fromUrl('https://example.com/reference.jpg'),
    ])
    ->landscape()
    ->generate();
```

```php
// Store to filesystem
$path = $image->store();
$path = $image->storeAs('image.jpg');
$path = $image->storePublicly();
$path = $image->storePubliclyAs('image.jpg');
```

```php
// Queue image generation for background processing
use Laravel\Ai\Responses\ImageResponse;

Image::of('A donut sitting on the kitchen counter')
    ->portrait()
    ->queue()
    ->then(function (ImageResponse $image) {
        $path = $image->store();
    });
```

## Why

- **Fluent API**: Chain aspect ratio, quality, and timeout in one expression
- **Storage built-in**: `store()` and `storeAs()` save to your configured disk
- **Queueable**: Offload generation to background workers
- **Provider support**: OpenAI, Gemini, xAI

Reference: [Laravel AI SDK — Images](https://laravel.com/docs/13.x/ai-sdk#images)

---

title: Audio Generation and Transcription
impact: MEDIUM
impactDescription: Text-to-speech and speech-to-text
tags: audio, tts, transcription, stt, voice, diarize
---

## Audio Generation and Transcription

**Impact: MEDIUM (Text-to-speech and speech-to-text)**

Generate audio from text (TTS) and transcribe audio to text (STT). Supports voice selection, instructions, diarization, storage, and queueing.

## Bad Example

```php
// Manual TTS API call — no storage, no voice control
$response = Http::withToken(config('services.openai.key'))
    ->post('https://api.openai.com/v1/audio/speech', [
        'model' => 'tts-1',
        'input' => 'Hello world',
        'voice' => 'alloy',
    ]);

file_put_contents(storage_path('audio.mp3'), $response->body());
```

## Good Example

```php
// Text-to-speech
use Laravel\Ai\Audio;

$audio = Audio::of('I love coding with Laravel.')->generate();
$rawContent = (string) $audio;
```

```php
// Voice selection and instructions
$audio = Audio::of('I love coding with Laravel.')
    ->female()  // or ->male(), ->voice('voice-id')
    ->instructions('Said like a pirate')
    ->generate();
```

```php
// Store audio
$path = $audio->store();
$path = $audio->storeAs('audio.mp3');
$path = $audio->storePublicly();
```

```php
// Queue audio generation
use Laravel\Ai\Responses\AudioResponse;

Audio::of('I love coding with Laravel.')
    ->queue()
    ->then(function (AudioResponse $audio) {
        $path = $audio->store();
    });
```

```php
// Speech-to-text transcription
use Laravel\Ai\Transcription;

$transcript = Transcription::fromPath('/home/laravel/audio.mp3')->generate();
$transcript = Transcription::fromStorage('audio.mp3')->generate();
$transcript = Transcription::fromUpload($request->file('audio'))->generate();

return (string) $transcript;
```

```php
// Diarization — segment transcript by speaker
$transcript = Transcription::fromStorage('audio.mp3')
    ->diarize()
    ->generate();
```

```php
// Queue transcription
use Laravel\Ai\Responses\TranscriptionResponse;

Transcription::fromStorage('audio.mp3')
    ->queue()
    ->then(function (TranscriptionResponse $transcript) {
        // Process transcript
    });
```

## Why

- **Fluent API**: Voice, instructions, and quality in a single chain
- **Multiple sources**: Generate from text, transcribe from path, storage, or upload
- **Diarization**: Speaker-segmented transcripts for meetings and interviews
- **Queueable**: Offload long audio processing to background workers
- **Provider support**: TTS via OpenAI, ElevenLabs. STT via OpenAI, ElevenLabs, Mistral

Reference: [Laravel AI SDK — Audio](https://laravel.com/docs/13.x/ai-sdk#audio)

---

title: Files and Vector Stores for RAG
impact: MEDIUM
impactDescription: Store files with providers and build searchable knowledge bases
tags: files, vector-stores, rag, document, storage
---

## Files and Vector Stores for RAG

**Impact: MEDIUM (Store files with providers and build searchable knowledge bases)**

Store files with AI providers for repeated use in conversations. Create vector stores to build searchable document collections for retrieval-augmented generation (RAG).

## Bad Example

```php
// Re-uploading the same file on every prompt
$response = (new DocumentAnalyzer)->prompt(
    'Summarize this document.',
    attachments: [
        // This uploads the file every single time
        Files\Document::fromStorage('report.pdf'),
    ],
);
```

## Good Example

```php
// Store a file once with the provider
use Laravel\Ai\Files\Document;
use Laravel\Ai\Files\Image;

$stored = Document::fromPath('/path/to/report.pdf')->put();
$stored = Document::fromStorage('report.pdf', disk: 'local')->put();
$stored = Document::fromUrl('https://example.com/doc.pdf')->put();
$stored = Document::fromUpload($request->file('document'))->put();
$stored = Image::fromPath('/path/to/photo.jpg')->put();

$fileId = $stored->id;
```

```php
// Reference stored file in conversations — no re-upload
$response = (new DocumentAnalyzer)->prompt(
    'Summarize this document.',
    attachments: [
        Document::fromId($fileId),
    ],
);
```

```php
// Delete a stored file
Document::fromId('file-id')->delete();
```

```php
// Create a vector store for RAG
use Laravel\Ai\Stores;

$store = Stores::create(
    name: 'Knowledge Base',
    description: 'Documentation and reference materials.',
    expiresWhenIdleFor: days(30),
);
```

```php
// Add files to a vector store — auto-indexed for searching
$store = Stores::get('store_id');

$document = $store->add(Document::fromPath('/path/to/doc.pdf'));
$document = $store->add(Document::fromStorage('manual.pdf'));
$document = $store->add($request->file('document'));

// Add with metadata for filtering
$store->add(Document::fromPath('/path/to/doc.pdf'), metadata: [
    'author' => 'Taylor Otwell',
    'department' => 'Engineering',
    'year' => 2026,
]);
```

```php
// Remove file from store
$store->remove('file_id');

// Remove and delete permanently
$store->remove('file_id', deleteFile: true);
```

```php
// Use vector store with FileSearch provider tool
use Laravel\Ai\Providers\Tools\FileSearch;

public function tools(): iterable
{
    return [
        new FileSearch(stores: ['store_id']),
    ];
}
```

## Why

- **Efficiency**: Store once, reference by ID — no repeated uploads
- **RAG**: Vector stores + FileSearch enable knowledge-base-powered agents
- **Metadata filtering**: Filter search results by author, date, department, etc.
- **Provider support**: OpenAI, Anthropic, Gemini for file storage

Reference: [Laravel AI SDK — Files](https://laravel.com/docs/13.x/ai-sdk#files)

---

title: Provider Failover
impact: MEDIUM
impactDescription: Automatic fallback when AI providers fail
tags: failover, provider, resilience, fallback
---

## Provider Failover

**Impact: MEDIUM (Automatic fallback when AI providers fail)**

Pass an array of providers to automatically failover when a service is down or rate-limited. Works with agents, images, and all other AI SDK features.

## Bad Example

```php
// Manual try/catch failover — verbose and fragile
try {
    $response = (new SalesCoach)->prompt(
        'Analyze this...',
        provider: Lab::OpenAI,
    );
} catch (\Exception $e) {
    try {
        $response = (new SalesCoach)->prompt(
            'Analyze this...',
            provider: Lab::Anthropic,
        );
    } catch (\Exception $e) {
        // Give up
        throw $e;
    }
}
```

## Good Example

```php
// Automatic failover — pass array of providers
use App\Ai\Agents\SalesCoach;
use Laravel\Ai\Enums\Lab;

$response = (new SalesCoach)->prompt(
    'Analyze this sales transcript...',
    provider: [Lab::OpenAI, Lab::Anthropic],
);
```

```php
// Failover for image generation
use Laravel\Ai\Image;

$image = Image::of('A donut sitting on the kitchen counter')
    ->generate(provider: [Lab::Gemini, Lab::xAI]);
```

```php
// Per-provider options with failover
use Laravel\Ai\Contracts\HasProviderOptions;

class SalesCoach implements Agent, HasProviderOptions
{
    use Promptable;

    public function providerOptions(Lab|string $provider): array
    {
        return match ($provider) {
            Lab::OpenAI => ['reasoning' => ['effort' => 'low']],
            Lab::Anthropic => ['thinking' => ['budget_tokens' => 1024]],
            default => [],
        };
    }
}

// Each fallback provider receives its own configuration
$response = (new SalesCoach)->prompt(
    'Analyze this...',
    provider: [Lab::OpenAI, Lab::Anthropic],
);
```

## Why

- **Resilience**: Automatic failover on service interruptions or rate limits
- **Zero code changes**: Same agent, same prompt — just pass an array
- **Per-provider config**: Each fallback can have its own options via `providerOptions()`
- **Works everywhere**: Agents, images, audio, embeddings — all support failover

Reference: [Laravel AI SDK — Failover](https://laravel.com/docs/13.x/ai-sdk#failover)

---

title: Testing Agents
impact: HIGH
impactDescription: Fake agent responses and assert prompts in tests
tags: testing, agent, fake, assert, mock
---

## Testing Agents

**Impact: HIGH (Fake agent responses and assert prompts in tests)**

Use `Agent::fake()` to prevent real API calls in tests and control agent responses. Assert that agents were prompted with expected inputs.

## Bad Example

```php
// Calling real AI providers in tests — slow, expensive, non-deterministic
test('coach analyzes transcript', function () {
    $response = (new SalesCoach)->prompt('Analyze this...');

    // Flaky: different response every run, costs money, requires API key
    expect((string) $response)->toContain('feedback');
});
```

## Good Example

```php
use App\Ai\Agents\SalesCoach;
use Laravel\Ai\Prompts\AgentPrompt;

// Auto-generate fixed response for every prompt
test('coach analyzes transcript', function () {
    SalesCoach::fake();

    $response = SalesCoach::make()->prompt('Analyze this transcript...');

    // Assert the agent was prompted
    SalesCoach::assertPrompted('Analyze this transcript...');
});
```

```php
// Provide specific responses
test('coach returns expected feedback', function () {
    SalesCoach::fake([
        'Great job on the opening.',
        'Second response for next prompt.',
    ]);

    $response = SalesCoach::make()->prompt('Analyze this...');

    expect((string) $response)->toBe('Great job on the opening.');
});
```

```php
// Dynamic responses based on prompt content
test('coach responds contextually', function () {
    SalesCoach::fake(function (AgentPrompt $prompt) {
        return 'Response for: ' . $prompt->prompt;
    });

    $response = SalesCoach::make()->prompt('Analyze this...');

    expect((string) $response)->toBe('Response for: Analyze this...');
});
```

```php
// Assert with closures for flexible matching
test('coach receives transcript', function () {
    SalesCoach::fake();

    SalesCoach::make()->prompt('Analyze the Q4 sales transcript...');

    SalesCoach::assertPrompted(function (AgentPrompt $prompt) {
        return $prompt->contains('Q4');
    });

    SalesCoach::assertNotPrompted('Missing prompt');
    SalesCoach::assertNeverPrompted(); // fails — was prompted
});
```

```php
// Assert queued agent invocations
use Laravel\Ai\QueuedAgentPrompt;

test('coach is queued', function () {
    SalesCoach::fake();

    SalesCoach::make()->queue('Analyze this...');

    SalesCoach::assertQueued(function (QueuedAgentPrompt $prompt) {
        return $prompt->contains('Analyze');
    });
});
```

```php
// Prevent stray prompts — fail if any agent is called without a fake
test('no unexpected agent calls', function () {
    SalesCoach::fake()->preventStrayPrompts();

    // SalesCoach::make()->prompt('...'); // OK — has fake
    // (new OtherAgent)->prompt('...');   // Would throw — no fake
});
```

**Structured output agents auto-generate fake data matching the schema.**

## Why

- **Fast**: No API calls, no network latency
- **Deterministic**: Same response every run
- **Free**: No API costs in test suites
- **Assertable**: Verify exactly what was prompted and how many times
- **Safe**: `preventStrayPrompts()` catches accidental real API calls

Reference: [Laravel AI SDK — Testing Agents](https://laravel.com/docs/13.x/ai-sdk#testing-agents)

---

title: Testing Images, Audio, and Transcriptions
impact: HIGH
impactDescription: Fake media generation in tests
tags: testing, image, audio, transcription, fake, assert
---

## Testing Images, Audio, and Transcriptions

**Impact: HIGH (Fake media generation in tests)**

Fake image, audio, and transcription generation to avoid real API calls. Assert that media was generated with expected parameters.

## Bad Example

```php
// Real image generation in tests — slow and expensive
test('generates product image', function () {
    $image = Image::of('Product photo')->generate();
    // Real API call, costs money, slow
    expect((string) $image)->not->toBeEmpty();
});
```

## Good Example

```php
use Laravel\Ai\Image;
use Laravel\Ai\Prompts\ImagePrompt;

// Fake image generation
test('generates product image', function () {
    Image::fake();

    Image::of('A sunset over the ocean')->landscape()->generate();

    Image::assertGenerated(function (ImagePrompt $prompt) {
        return $prompt->contains('sunset') && $prompt->isLandscape();
    });

    Image::assertNotGenerated('Missing prompt');
    Image::assertNothingGenerated(); // fails — was generated
});
```

```php
// Fake with specific responses
test('stores generated image', function () {
    Image::fake([
        base64_encode($firstImage),
        base64_encode($secondImage),
    ]);

    $image = Image::of('A donut')->generate();
    $path = $image->store();
});
```

```php
// Assert queued image generations
use Laravel\Ai\Prompts\QueuedImagePrompt;

test('queues image generation', function () {
    Image::fake();

    Image::of('A donut')->queue();

    Image::assertQueued(fn (QueuedImagePrompt $prompt) => $prompt->contains('donut'));
});
```

```php
// Prevent stray image generations
test('no unexpected generations', function () {
    Image::fake()->preventStrayImages();
});
```

```php
use Laravel\Ai\Audio;
use Laravel\Ai\Prompts\AudioPrompt;

// Fake audio generation
test('generates welcome audio', function () {
    Audio::fake();

    Audio::of('Welcome to our app.')->female()->generate();

    Audio::assertGenerated(function (AudioPrompt $prompt) {
        return $prompt->contains('Welcome') && $prompt->isFemale();
    });
});
```

```php
use Laravel\Ai\Transcription;
use Laravel\Ai\Prompts\TranscriptionPrompt;

// Fake transcription
test('transcribes uploaded audio', function () {
    Transcription::fake(['Transcribed text from the audio file.']);

    $transcript = Transcription::fromStorage('audio.mp3')->diarize()->generate();

    Transcription::assertGenerated(function (TranscriptionPrompt $prompt) {
        return $prompt->isDiarized();
    });
});
```

## Why

- **Fast**: No image/audio generation — tests run in milliseconds
- **Deterministic**: Controlled fake responses every run
- **Assertable**: Verify prompts, aspect ratios, voices, and diarization settings
- **Prevent leaks**: `preventStrayImages()` / `preventStrayAudio()` catch accidental real calls

Reference: [Laravel AI SDK — Testing](https://laravel.com/docs/13.x/ai-sdk#testing)

---

title: Testing Embeddings, Reranking, Files, and Vector Stores
impact: HIGH
impactDescription: Fake data operations in tests
tags: testing, embeddings, reranking, files, vector-stores, fake, assert
---

## Testing Embeddings, Reranking, Files, and Vector Stores

**Impact: HIGH (Fake data operations in tests)**

Fake embeddings, reranking, file storage, and vector store operations to avoid real API calls and provider dependencies in tests.

## Bad Example

```php
// Real embedding generation in tests — slow, costs money, needs API key
test('stores document with embedding', function () {
    $embedding = Str::of($content)->toEmbeddings(); // Real API call
    Document::create(['content' => $content, 'embedding' => $embedding]);
});
```

## Good Example

```php
use Laravel\Ai\Embeddings;
use Laravel\Ai\Prompts\EmbeddingsPrompt;

// Fake embeddings — auto-generates vectors of proper dimensions
test('generates document embeddings', function () {
    Embeddings::fake();

    $response = Embeddings::for(['Laravel is great.'])->generate();

    Embeddings::assertGenerated(function (EmbeddingsPrompt $prompt) {
        return $prompt->contains('Laravel');
    });
});
```

```php
// Fake with specific vectors
test('generates specific embeddings', function () {
    Embeddings::fake([
        [$firstEmbeddingVector],
        [$secondEmbeddingVector],
    ]);

    $response = Embeddings::for(['Input text.'])->generate();
});
```

```php
// Prevent stray embedding calls
test('no unexpected embeddings', function () {
    Embeddings::fake()->preventStrayEmbeddings();
});
```

```php
use Laravel\Ai\Reranking;
use Laravel\Ai\Prompts\RerankingPrompt;
use Laravel\Ai\Responses\Data\RankedDocument;

// Fake reranking
test('reranks search results', function () {
    Reranking::fake();

    $response = Reranking::of(['Doc A', 'Doc B'])->rerank('query');

    Reranking::assertReranked(function (RerankingPrompt $prompt) {
        return $prompt->contains('query');
    });
});

// Fake with specific ranked results
test('returns expected ranking', function () {
    Reranking::fake([
        [
            new RankedDocument(index: 0, document: 'First', score: 0.95),
            new RankedDocument(index: 1, document: 'Second', score: 0.80),
        ],
    ]);
});
```

```php
use Laravel\Ai\Files;
use Laravel\Ai\Contracts\Files\StorableFile;
use Laravel\Ai\Files\Document;

// Fake file operations
test('stores document with provider', function () {
    Files::fake();

    Document::fromString('Hello, Laravel!', mimeType: 'text/plain')
        ->as('hello.txt')
        ->put();

    Files::assertStored(fn (StorableFile $file) =>
        (string) $file === 'Hello, Laravel!' &&
        $file->mimeType() === 'text/plain'
    );

    Files::assertNothingDeleted();
});
```

```php
use Laravel\Ai\Stores;

// Fake vector store operations (also fakes file operations)
test('creates knowledge base store', function () {
    Stores::fake();

    $store = Stores::create('Knowledge Base');

    Stores::assertCreated('Knowledge Base');

    // Assert files added to store
    $store->add(Document::fromString('Content', 'text/plain')->as('doc.txt'));
    $store->assertAdded(fn (StorableFile $file) => $file->name() === 'doc.txt');
});
```

## Why

- **Fast**: No embedding API calls or provider connections
- **Deterministic**: Controlled fake vectors and rankings
- **Comprehensive**: Fake files, stores, and all data operations in one test
- **Assertable**: Verify what was generated, stored, added, and removed

Reference: [Laravel AI SDK — Testing](https://laravel.com/docs/13.x/ai-sdk#testing)

---

## How to Use This Guide

1. **For AI Agents**: Reference specific rules by category and rule name when generating or reviewing code
2. **For Developers**: Use as a comprehensive reference for Laravel AI SDK best practices
3. **For Code Review**: Check implementations against these patterns
4. **For Testing**: Use faking patterns to test all AI features without real API calls
