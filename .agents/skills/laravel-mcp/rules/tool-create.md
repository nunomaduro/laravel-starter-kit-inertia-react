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
