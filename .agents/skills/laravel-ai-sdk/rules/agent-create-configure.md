---
title: Create and Configure Agents
impact: CRITICAL
impactDescription: Foundation for all AI interactions in Laravel
tags: agent, create, configure, attributes, provider, model
---

## Create and Configure Agents

**Impact: CRITICAL (Foundation for all AI interactions in Laravel)**

Agents are dedicated PHP classes that encapsulate instructions, tools, and output schemas for interacting with AI providers. Create agents with `make:agent` and configure them with PHP attributes.

## Bad Example

```php
// Hardcoded API calls scattered across controllers
class ChatController extends Controller
{
    public function respond(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => ['Authorization' => 'Bearer ' . config('services.openai.key')],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a sales coach.'],
                    ['role' => 'user', 'content' => $request->input('message')],
                ],
            ],
        ]);

        // Provider-specific, untestable, no structure
        return json_decode($response->getBody(), true);
    }
}
```

## Good Example

```php
// Generate with artisan
// php artisan make:agent SalesCoach
// php artisan make:agent SalesCoach --structured

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class SalesCoach implements Agent
{
    use Promptable;

    public function __construct(public User $user) {}

    public function instructions(): string
    {
        return 'You are a sales coach, analyzing transcripts and providing feedback.';
    }
}
```

```php
// Configure with PHP attributes — provider, model, limits
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;

#[Provider(Lab::Anthropic)]
#[Model('claude-haiku-4-5-20251001')]
#[MaxSteps(10)]
#[MaxTokens(4096)]
#[Temperature(0.7)]
#[Timeout(120)]
class SalesCoach implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a sales coach.';
    }
}
```

```php
// Auto-select cheapest or smartest model
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Attributes\UseSmartestModel;

#[UseCheapestModel]
class SimpleSummarizer implements Agent { /* ... */ }

#[UseSmartestModel]
class ComplexReasoner implements Agent { /* ... */ }
```

```php
// Provider-specific options (reasoning effort, penalties)
use Laravel\Ai\Contracts\HasProviderOptions;
use Laravel\Ai\Enums\Lab;

class SalesCoach implements Agent, HasProviderOptions
{
    use Promptable;

    public function providerOptions(Lab|string $provider): array
    {
        return match ($provider) {
            Lab::OpenAI => ['reasoning' => ['effort' => 'low']],
            Lab::Anthropic => ['thinking' => ['budget_tokens' => 1024]],
            default => [],
        };
    }
}
```

## Why

- **Provider-agnostic**: Switch between OpenAI, Anthropic, Gemini without code changes
- **Testable**: Agents can be faked in tests — raw API calls cannot
- **Declarative**: PHP attributes make configuration visible at the class level
- **Container-resolved**: `make()` injects dependencies automatically

Reference: [Laravel AI SDK — Agents](https://laravel.com/docs/13.x/ai-sdk#agents)
