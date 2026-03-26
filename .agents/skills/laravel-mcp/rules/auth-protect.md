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
