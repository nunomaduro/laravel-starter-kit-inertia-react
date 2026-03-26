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
