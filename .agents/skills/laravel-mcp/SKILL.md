---
name: laravel-mcp
description: Laravel MCP server development. Use when building MCP servers, tools, prompts, or resources for AI client integration. Triggers on tasks involving laravel/mcp, MCP tools, MCP prompts, MCP resources, or AI client protocols.
license: MIT
metadata:
  author: Laravel Community
  version: "1.0.0"
  laravelVersion: "13.x"
  phpVersion: "8.3+"
---

# Laravel MCP

Comprehensive guide for building MCP (Model Context Protocol) servers with Laravel. Contains 7 rules across 5 categories for exposing tools, prompts, and resources to AI clients.

## When to Apply

Reference these guidelines when:
- Creating MCP servers (web or local)
- Building tools that AI clients can call
- Defining prompts for reusable AI interactions
- Exposing resources for AI context
- Protecting MCP servers with OAuth or Sanctum
- Testing MCP servers, tools, prompts, and resources

## Rule Categories by Priority

| Priority | Category | Impact | Prefix |
|----------|----------|--------|--------|
| 1 | Servers | CRITICAL | `server-` |
| 2 | Tools | HIGH | `tool-` |
| 3 | Prompts & Resources | MEDIUM | `prompt-`, `resource-` |
| 4 | Authentication | HIGH | `auth-` |
| 5 | Testing | HIGH | `test-` |

## Quick Reference

### 1. Servers (CRITICAL)

- `server-create-register` - Create and register web or local MCP servers

### 2. Tools (HIGH)

- `tool-create` - Create tools with input/output schemas, DI, and annotations
- `tool-responses` - Text, error, structured, streaming, and multi-content responses

### 3. Prompts & Resources (MEDIUM)

- `prompt-create` - Create prompts with arguments, validation, and responses
- `resource-create` - Create resources and resource templates with URI patterns

### 4. Authentication (HIGH)

- `auth-protect` - Protect servers with OAuth 2.1, Sanctum, or custom auth

### 5. Testing (HIGH)

- `test-unit` - Test with MCP Inspector and unit tests

## Essential Patterns

### Creating an MCP Server

```php
<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Weather Server')]
#[Version('1.0.0')]
#[Instructions('This server provides weather information.')]
class WeatherServer extends Server
{
    protected array $tools = [
        CurrentWeatherTool::class,
    ];

    protected array $resources = [];

    protected array $prompts = [];
}
```

### Registering in routes/ai.php

```php
use App\Mcp\Servers\WeatherServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/weather', WeatherServer::class);
Mcp::local('weather', WeatherServer::class);
```

### Creating a Tool

```php
<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Fetches the current weather for a location.')]
class CurrentWeatherTool extends Tool
{
    public function handle(Request $request): Response
    {
        $location = $request->get('location');

        return Response::text("The weather in {$location} is sunny, 72°F.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'location' => $schema->string()
                ->description('The location to get the weather for.')
                ->required(),
        ];
    }
}
```

## How to Use

Read individual rule files for detailed explanations and code examples.

Each rule file contains:
- YAML frontmatter with metadata (title, impact, tags)
- Brief explanation of why it matters
- Bad Example with explanation
- Good Example with explanation
- Laravel 13 and PHP 8.3 specific context and references

## Full Compiled Document

For the complete guide with all rules expanded: `AGENTS.md`
