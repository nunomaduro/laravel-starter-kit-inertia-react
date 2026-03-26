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
