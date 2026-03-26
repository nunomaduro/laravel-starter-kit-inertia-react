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
