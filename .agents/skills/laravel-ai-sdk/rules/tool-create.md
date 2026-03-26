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
