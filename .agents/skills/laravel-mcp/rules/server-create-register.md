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
