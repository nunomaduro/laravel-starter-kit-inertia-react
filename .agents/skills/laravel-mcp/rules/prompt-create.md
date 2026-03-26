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
