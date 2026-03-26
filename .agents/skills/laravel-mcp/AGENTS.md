# Laravel MCP - Complete Guide

**Version:** 1.0.0
**Laravel Version:** 13.x
**PHP Version:** 8.3+
**Organization:** Laravel Community
**Date:** March 2026

## Overview

Comprehensive guide for building MCP (Model Context Protocol) servers with Laravel. Contains 7 rules across 5 categories covering server creation, tool development, prompts, resources, authentication, and testing.

### Key Features

- MCP server creation with web and local transport
- Tool development with JSON input/output schemas
- Tool annotations (IsReadOnly, IsDestructive, IsIdempotent, IsOpenWorld)
- Structured and streaming tool responses
- Prompt templates with arguments and validation
- Resources and resource templates with URI patterns
- OAuth 2.1 authentication via Passport
- Sanctum token-based authentication
- Authorization with request user context
- MCP Inspector for interactive debugging
- Unit tests with assertOk, assertSee, assertHasErrors

## Categories

This guide is organized into 5 categories:

1. **Servers** (CRITICAL) — Creating and registering MCP servers
2. **Tools** (HIGH) — Building tools with schemas, validation, and responses
3. **Prompts & Resources** (MEDIUM) — Prompt templates and data resources
4. **Authentication** (HIGH) — OAuth 2.1, Sanctum, and authorization
5. **Testing** (HIGH) — MCP Inspector and unit tests

### References

- [Laravel MCP Documentation](https://laravel.com/docs/13.x/mcp)
- [Model Context Protocol](https://modelcontextprotocol.io/docs/getting-started/intro)
- [Laravel MCP Repository](https://github.com/laravel/mcp)

---

title: Create and Register MCP Servers
impact: CRITICAL
impactDescription: Foundation for all MCP client interactions
tags: server, create, register, web, local, routes
---

## Create and Register MCP Servers

**Impact: CRITICAL (Foundation for all MCP client interactions)**

MCP servers expose tools, prompts, and resources to AI clients. Create a server class with `make:mcp-server`, then register it in `routes/ai.php` as a web (HTTP) or local (Artisan CLI) server.

## Bad Example

```php
// Raw HTTP endpoints — no MCP protocol, not discoverable by AI clients
Route::post('/api/weather', function (Request $request) {
    $location = $request->input('location');
    $weather = WeatherService::get($location);

    return response()->json(['weather' => $weather]);
});

// AI clients can't discover this endpoint, its parameters, or its purpose
```

## Good Example

```php
// php artisan make:mcp-server WeatherServer

namespace App\Mcp\Servers;

use App\Mcp\Tools\CurrentWeatherTool;
use App\Mcp\Prompts\DescribeWeatherPrompt;
use App\Mcp\Resources\WeatherGuidelinesResource;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Weather Server')]
#[Version('1.0.0')]
#[Instructions('This server provides weather information and forecasts.')]
class WeatherServer extends Server
{
    protected array $tools = [
        CurrentWeatherTool::class,
    ];

    protected array $resources = [
        WeatherGuidelinesResource::class,
    ];

    protected array $prompts = [
        DescribeWeatherPrompt::class,
    ];
}
```

```php
// Register in routes/ai.php (publish with: php artisan vendor:publish --tag=ai-routes)

use App\Mcp\Servers\WeatherServer;
use Laravel\Mcp\Facades\Mcp;

// Web server — accessible via HTTP POST, ideal for remote AI clients
Mcp::web('/mcp/weather', WeatherServer::class);

// With middleware
Mcp::web('/mcp/weather', WeatherServer::class)
    ->middleware(['throttle:mcp']);

// Local server — runs as Artisan command, ideal for local AI assistants
Mcp::local('weather', WeatherServer::class);
```

## Why

- **Discoverable**: AI clients automatically discover tools, prompts, and resources
- **Protocol-compliant**: Follows the MCP specification — works with any MCP client
- **Two transports**: Web (HTTP) for remote clients, local (stdio) for CLI assistants
- **Middleware support**: Apply throttling, auth, and other middleware to web servers
- **Declarative**: PHP attributes configure name, version, and instructions

Reference: [Laravel MCP — Creating Servers](https://laravel.com/docs/13.x/mcp#creating-servers)

---

title: Create Tools with Schemas and Configuration
impact: HIGH
impactDescription: Expose application functionality to AI clients
tags: tool, create, schema, input, output, validation, di, annotations
---

## Create Tools with Schemas and Configuration

**Impact: HIGH (Expose application functionality to AI clients)**

Tools let AI clients call your application code. Each tool has a description, input schema, optional output schema, validation, dependency injection, and annotations.

## Bad Example

```php
// Tool with no schema — AI client doesn't know what arguments to send
class WeatherTool extends Tool
{
    public function handle(Request $request): Response
    {
        // What arguments does this accept? No schema defined.
        $location = $request->get('location'); // AI client guesses
        return Response::text('Sunny');
    }
}
```

## Good Example

```php
// php artisan make:mcp-tool CurrentWeatherTool

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Fetches the current weather forecast for a specified location.')]
class CurrentWeatherTool extends Tool
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'location' => 'required|string|max:100',
            'units' => 'in:celsius,fahrenheit',
        ], [
            'location.required' => 'You must specify a location. For example, "New York City" or "Tokyo".',
            'units.in' => 'You must specify either "celsius" or "fahrenheit" for the units.',
        ]);

        $weather = $this->weather->getForecastFor($validated['location']);

        return Response::text("The weather in {$validated['location']} is {$weather}.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'location' => $schema->string()
                ->description('The location to get the weather for.')
                ->required(),
            'units' => $schema->string()
                ->enum(['celsius', 'fahrenheit'])
                ->description('Temperature units.')
                ->default('celsius'),
        ];
    }

    public function outputSchema(JsonSchema $schema): array
    {
        return [
            'temperature' => $schema->number()
                ->description('Temperature value')
                ->required(),
            'conditions' => $schema->string()
                ->description('Weather conditions')
                ->required(),
        ];
    }
}
```

```php
// Customize name and title with attributes
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;

#[Name('get-weather')]
#[Title('Get Weather Forecast')]
#[Description('Fetches the current weather forecast.')]
class CurrentWeatherTool extends Tool { /* ... */ }
```

```php
// Dependency injection — constructor and handle method
use App\Repositories\WeatherRepository;

class CurrentWeatherTool extends Tool
{
    public function __construct(
        private readonly WeatherRepository $weather,
    ) {}

    public function handle(Request $request, WeatherRepository $weather): Response
    {
        return Response::text($weather->getForecastFor($request->get('location')));
    }
}
```

```php
// Annotations — describe tool behavior to AI clients
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;

#[IsReadOnly]
#[IsIdempotent]
class CurrentWeatherTool extends Tool { /* ... */ }

#[IsDestructive]
#[IsOpenWorld]
class DeleteRecordTool extends Tool { /* ... */ }
```

```php
// Conditional registration — show/hide tools based on state
class PremiumWeatherTool extends Tool
{
    public function shouldRegister(Request $request): bool
    {
        return $request?->user()?->subscribed() ?? false;
    }
}
```

## Why

- **Discoverable**: Input schema tells AI clients exactly what arguments to send
- **Validated**: Laravel validation with clear error messages guides AI clients to fix inputs
- **Type-safe**: Output schema lets AI clients parse structured results
- **Injectable**: Constructor and handle-method DI — no manual service resolution
- **Annotated**: Annotations help AI clients understand side effects before calling

Reference: [Laravel MCP — Tools](https://laravel.com/docs/13.x/mcp#tools)

---

title: Tool Responses
impact: HIGH
impactDescription: Return text, errors, structured data, and streaming content from tools
tags: tool, response, text, error, structured, streaming, image, audio
---

## Tool Responses

**Impact: HIGH (Return text, errors, structured data, and streaming content from tools)**

Tools return `Response` instances with multiple content types: text, errors, images, audio, structured data, and streaming generators.

## Bad Example

```php
// Returning raw strings — no protocol compliance, no error handling
class WeatherTool extends Tool
{
    public function handle(Request $request): string
    {
        // Wrong return type, no error handling
        return 'Sunny, 72°F';
    }
}
```

## Good Example

```php
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;

// Text response
public function handle(Request $request): Response
{
    return Response::text('Weather Summary: Sunny, 72°F');
}
```

```php
// Error response
public function handle(Request $request): Response
{
    if (!$this->weather->isAvailable()) {
        return Response::error('Unable to fetch weather data. Please try again.');
    }

    return Response::text($this->weather->get($request->get('location')));
}
```

```php
// Image and audio responses
public function handle(Request $request): Response
{
    return Response::image(
        file_get_contents(storage_path('weather/radar.png')),
        'image/png'
    );
}

// From filesystem disk — MIME type auto-detected
return Response::fromStorage('weather/radar.png');
return Response::fromStorage('weather/radar.png', disk: 's3');
```

```php
// Multiple content responses
public function handle(Request $request): array
{
    return [
        Response::text('Weather Summary: Sunny, 72°F'),
        Response::text("**Forecast**\n- Morning: 65°F\n- Afternoon: 78°F"),
    ];
}
```

```php
// Structured response — parseable data for AI clients
public function handle(Request $request): Response
{
    return Response::structured([
        'temperature' => 22.5,
        'conditions' => 'Partly cloudy',
        'humidity' => 65,
    ]);
}

// Structured with custom text
return Response::make(
    Response::text('Weather is 22.5°C and sunny')
)->withStructuredContent([
    'temperature' => 22.5,
    'conditions' => 'Sunny',
]);
```

```php
// Streaming response — for long-running operations
use Generator;

public function handle(Request $request): Generator
{
    $locations = $request->array('locations');

    foreach ($locations as $index => $location) {
        yield Response::notification('processing/progress', [
            'current' => $index + 1,
            'total' => count($locations),
            'location' => $location,
        ]);

        yield Response::text($this->forecastFor($location));
    }
}
```

```php
// Metadata on responses
public function handle(Request $request): Response
{
    return Response::text('Sunny, 72°F')
        ->withMeta(['source' => 'weather-api', 'cached' => true]);
}

// Result-level metadata
return Response::make(
    Response::text('Sunny, 72°F')
)->withMeta(['request_id' => '12345']);
```

## Why

- **Protocol-compliant**: `Response` class handles MCP serialization correctly
- **Multiple formats**: Text, images, audio, structured data — all through one API
- **Streaming**: Generators enable progress updates for long-running tools
- **Structured**: AI clients can parse structured responses programmatically
- **Metadata**: Attach source, caching, and tracking info to responses

Reference: [Laravel MCP — Tool Responses](https://laravel.com/docs/13.x/mcp#tool-responses)

---

title: Create Prompts with Arguments
impact: MEDIUM
impactDescription: Reusable prompt templates for AI clients
tags: prompt, create, arguments, validation, response
---

## Create Prompts with Arguments

**Impact: MEDIUM (Reusable prompt templates for AI clients)**

Prompts are reusable templates that AI clients can invoke with arguments. They provide a standardized way to structure common queries.

## Bad Example

```php
// Hardcoded prompt in a tool — not reusable, not discoverable
class WeatherTool extends Tool
{
    public function handle(Request $request): Response
    {
        // Prompt logic mixed into tool — should be a separate prompt
        $tone = $request->get('tone', 'formal');
        $systemMessage = "Describe the weather in a {$tone} tone.";

        return Response::text($systemMessage);
    }
}
```

## Good Example

```php
// php artisan make:mcp-prompt DescribeWeatherPrompt

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Description('Generates a natural-language weather description in a given tone.')]
class DescribeWeatherPrompt extends Prompt
{
    public function arguments(): array
    {
        return [
            new Argument(
                name: 'tone',
                description: 'The tone to use (e.g., formal, casual, humorous).',
                required: true,
            ),
        ];
    }

    public function handle(Request $request): array
    {
        $validated = $request->validate([
            'tone' => 'required|string|max:50',
        ], [
            'tone.*' => 'Specify a tone like "formal", "casual", or "humorous".',
        ]);

        $tone = $validated['tone'];

        return [
            Response::text("You are a weather assistant. Describe the weather in a {$tone} tone.")->asAssistant(),
            Response::text('What is the current weather in New York City?'),
        ];
    }
}
```

```php
// Register in server
class WeatherServer extends Server
{
    protected array $prompts = [
        DescribeWeatherPrompt::class,
    ];
}
```

```php
// Customize name and title
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;

#[Name('weather-assistant')]
#[Title('Weather Assistant Prompt')]
#[Description('Generates a weather description.')]
class DescribeWeatherPrompt extends Prompt { /* ... */ }
```

```php
// Dependency injection in prompts
use App\Repositories\WeatherRepository;

class DescribeWeatherPrompt extends Prompt
{
    public function __construct(
        private readonly WeatherRepository $weather,
    ) {}

    public function handle(Request $request, WeatherRepository $weather): Response
    {
        $isAvailable = $weather->isServiceAvailable();
        // ...
    }
}
```

```php
// Conditional prompt registration
class PremiumWeatherPrompt extends Prompt
{
    public function shouldRegister(Request $request): bool
    {
        return $request?->user()?->subscribed() ?? false;
    }
}
```

## Why

- **Reusable**: Define prompt templates once, invoke from any AI client
- **Typed arguments**: Arguments have names, descriptions, and required flags
- **Validated**: Laravel validation with clear error messages
- **Multi-message**: Return system and user messages with `asAssistant()`
- **Conditional**: Show/hide prompts based on user state or subscription

Reference: [Laravel MCP — Prompts](https://laravel.com/docs/13.x/mcp#prompts)

---

title: Create Resources and Resource Templates
impact: MEDIUM
impactDescription: Expose data and documents for AI client context
tags: resource, create, template, uri, mime, blob
---

## Create Resources and Resource Templates

**Impact: MEDIUM (Expose data and documents for AI client context)**

Resources provide data that AI clients read as context. Static resources have fixed URIs; resource templates use URI patterns with variables for dynamic content.

## Bad Example

```php
// Exposing data through tools instead of resources
// Tools are for actions — resources are for data
class GetGuidelinesTool extends Tool
{
    public function handle(Request $request): Response
    {
        // This is read-only data, not an action — should be a resource
        return Response::text(file_get_contents(storage_path('guidelines.md')));
    }
}
```

## Good Example

```php
// php artisan make:mcp-resource WeatherGuidelinesResource

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Description('Comprehensive guidelines for using the Weather API.')]
#[Uri('weather://resources/guidelines')]
#[MimeType('text/plain')]
class WeatherGuidelinesResource extends Resource
{
    public function handle(Request $request): Response
    {
        $guidelines = file_get_contents(storage_path('weather/guidelines.md'));

        return Response::text($guidelines);
    }
}
```

```php
// Register in server
class WeatherServer extends Server
{
    protected array $resources = [
        WeatherGuidelinesResource::class,
    ];
}
```

```php
// Resource template — dynamic URIs with variables
use Illuminate\Support\Facades\Storage;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Support\UriTemplate;

#[Description('Access user files by ID')]
#[MimeType('text/plain')]
class UserFileResource extends Resource implements HasUriTemplate
{
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('file://users/{userId}/files/{fileId}');
    }

    public function handle(Request $request): Response
    {
        $userId = $request->get('userId');
        $fileId = $request->get('fileId');

        $content = Storage::get("users/{$userId}/files/{$fileId}");

        return Response::text($content);
    }
}
```

```php
// Blob response for binary content (images, PDFs)
#[MimeType('image/png')]
class WeatherRadarResource extends Resource
{
    public function handle(Request $request): Response
    {
        return Response::blob(file_get_contents(storage_path('weather/radar.png')));
    }
}
```

```php
// Resource annotations
use Laravel\Mcp\Enums\Role;
use Laravel\Mcp\Server\Annotations\Audience;
use Laravel\Mcp\Server\Annotations\LastModified;
use Laravel\Mcp\Server\Annotations\Priority;

#[Audience(Role::User)]
#[LastModified('2026-01-12T15:00:58Z')]
#[Priority(0.9)]
class UserDashboardResource extends Resource { /* ... */ }
```

```php
// Conditional registration
class PremiumDataResource extends Resource
{
    public function shouldRegister(Request $request): bool
    {
        return $request?->user()?->subscribed() ?? false;
    }
}
```

## Why

- **Semantic**: Resources are data, tools are actions — AI clients understand the difference
- **Templates**: URI patterns enable dynamic resources without defining each one individually
- **Typed**: MIME type tells AI clients how to interpret the content
- **Annotated**: Priority and audience hints help AI clients choose relevant resources
- **Conditional**: Show resources based on auth state or subscription

Reference: [Laravel MCP — Resources](https://laravel.com/docs/13.x/mcp#resources)

---

title: Protect MCP Servers with Authentication
impact: HIGH
impactDescription: Secure MCP servers from unauthorized AI client access
tags: auth, oauth, sanctum, passport, authorization, middleware
---

## Protect MCP Servers with Authentication

**Impact: HIGH (Secure MCP servers from unauthorized AI client access)**

Protect web MCP servers with OAuth 2.1 (Passport), Sanctum tokens, or custom middleware. Use `$request->user()` for authorization within tools and resources.

## Bad Example

```php
// Unprotected MCP server — anyone can call your tools
Mcp::web('/mcp/admin', AdminServer::class);
// No auth middleware — all tools are publicly accessible
```

## Good Example

```php
// OAuth 2.1 with Passport — recommended for MCP specification compliance
use App\Mcp\Servers\WeatherServer;
use Laravel\Mcp\Facades\Mcp;

// Register OAuth discovery and client registration routes
Mcp::oauthRoutes();

// Protect server with Passport auth
Mcp::web('/mcp/weather', WeatherServer::class)
    ->middleware('auth:api');
```

```php
// Sanctum — simpler, token-based authentication
Mcp::web('/mcp/weather', WeatherServer::class)
    ->middleware('auth:sanctum');

// AI clients send: Authorization: Bearer <token>
```

```php
// Custom middleware for custom token auth
Mcp::web('/mcp/weather', WeatherServer::class)
    ->middleware(['custom-api-auth', 'throttle:mcp']);
```

```php
// Authorization inside tools — check user permissions
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;

public function handle(Request $request): Response
{
    if (!$request->user()->can('read-weather')) {
        return Response::error('Permission denied.');
    }

    return Response::text($this->weather->get($request->get('location')));
}
```

```php
// Passport authorization view setup (AppServiceProvider::boot)
use Laravel\Passport\Passport;

public function boot(): void
{
    Passport::authorizationView(function ($parameters) {
        return view('mcp.authorize', $parameters);
    });
}

// Publish the view:
// php artisan vendor:publish --tag=mcp-views
```

## Why

- **MCP spec compliance**: OAuth 2.1 is the documented MCP authentication mechanism
- **Sanctum fallback**: Use Sanctum if Passport is not already installed
- **Middleware**: Apply throttling, CORS, and custom middleware alongside auth
- **Authorization**: `$request->user()->can()` for fine-grained tool access control
- **Per-tool control**: Return `Response::error()` for unauthorized actions

Reference: [Laravel MCP — Authentication](https://laravel.com/docs/13.x/mcp#authentication)

---

title: Test MCP Servers with Inspector and Unit Tests
impact: HIGH
impactDescription: Verify MCP tools, prompts, and resources work correctly
tags: testing, inspector, unit-test, assert, tool, prompt, resource
---

## Test MCP Servers with Inspector and Unit Tests

**Impact: HIGH (Verify MCP tools, prompts, and resources work correctly)**

Use the MCP Inspector for interactive debugging and write unit tests with assertions for tools, prompts, and resources.

## Bad Example

```php
// Testing MCP tools by making raw HTTP calls — fragile and verbose
test('weather tool works', function () {
    $response = $this->postJson('/mcp/weather', [
        'jsonrpc' => '2.0',
        'method' => 'tools/call',
        'params' => ['name' => 'current-weather', 'arguments' => ['location' => 'NYC']],
        'id' => 1,
    ]);

    // Manual JSON-RPC parsing — error-prone
    $this->assertTrue($response->json('result.content.0.text') !== null);
});
```

## Good Example

```bash
# MCP Inspector — interactive testing and debugging
php artisan mcp:inspector mcp/weather    # Web server
php artisan mcp:inspector weather        # Local server named "weather"
```

```php
// Pest — test a tool
use App\Mcp\Servers\WeatherServer;
use App\Mcp\Tools\CurrentWeatherTool;

test('weather tool returns forecast', function () {
    $response = WeatherServer::tool(CurrentWeatherTool::class, [
        'location' => 'New York City',
        'units' => 'fahrenheit',
    ]);

    $response
        ->assertOk()
        ->assertSee('New York City');
});
```

```php
// PHPUnit — test a tool
use App\Mcp\Servers\WeatherServer;
use App\Mcp\Tools\CurrentWeatherTool;

class WeatherToolTest extends TestCase
{
    public function test_weather_tool_returns_forecast(): void
    {
        $response = WeatherServer::tool(CurrentWeatherTool::class, [
            'location' => 'New York City',
            'units' => 'fahrenheit',
        ]);

        $response
            ->assertOk()
            ->assertSee('The current weather in New York City is 72°F and sunny.');
    }
}
```

```php
// Test prompts and resources
$response = WeatherServer::prompt(DescribeWeatherPrompt::class, [
    'tone' => 'casual',
]);
$response->assertOk()->assertSee('casual');

$response = WeatherServer::resource(WeatherGuidelinesResource::class);
$response->assertOk();
```

```php
// Test as authenticated user
$response = WeatherServer::actingAs($user)->tool(
    CurrentWeatherTool::class,
    ['location' => 'NYC'],
);
$response->assertOk();
```

```php
// Available assertions
$response->assertOk();                          // No errors
$response->assertHasErrors();                   // Has errors
$response->assertHasErrors(['Something went wrong.']);
$response->assertHasNoErrors();
$response->assertSee('expected text');           // Contains text
$response->assertName('current-weather');        // Tool name
$response->assertTitle('Current Weather Tool');  // Tool title
$response->assertDescription('Fetches...');      // Tool description

// Streaming notification assertions
$response->assertSentNotification('processing/progress', [
    'step' => 1,
    'total' => 5,
]);
$response->assertNotificationCount(5);

// Debug — inspect raw response
$response->dd();
$response->dump();
```

## Why

- **Inspector**: Interactive UI to test tools, prompts, and resources without writing code
- **First-class assertions**: `assertOk()`, `assertSee()`, `assertHasErrors()` — clean and readable
- **Auth testing**: `actingAs()` tests authorization logic in tools and resources
- **Streaming**: Assert notifications sent during long-running tool operations
- **Metadata**: Assert tool name, title, and description match expectations

Reference: [Laravel MCP — Testing](https://laravel.com/docs/13.x/mcp#testing-servers)

---

## How to Use This Guide

1. **For AI Agents**: Reference specific rules by category and rule name when generating or reviewing code
2. **For Developers**: Use as a comprehensive reference for Laravel MCP best practices
3. **For Code Review**: Check implementations against these patterns
4. **For Testing**: Use MCP Inspector and unit test patterns to verify all MCP primitives
