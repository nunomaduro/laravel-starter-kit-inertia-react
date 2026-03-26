---
title: Provider Failover
impact: MEDIUM
impactDescription: Automatic fallback when AI providers fail
tags: failover, provider, resilience, fallback
---

## Provider Failover

**Impact: MEDIUM (Automatic fallback when AI providers fail)**

Pass an array of providers to automatically failover when a service is down or rate-limited. Works with agents, images, and all other AI SDK features.

## Bad Example

```php
// Manual try/catch failover — verbose and fragile
try {
    $response = (new SalesCoach)->prompt(
        'Analyze this...',
        provider: Lab::OpenAI,
    );
} catch (\Exception $e) {
    try {
        $response = (new SalesCoach)->prompt(
            'Analyze this...',
            provider: Lab::Anthropic,
        );
    } catch (\Exception $e) {
        // Give up
        throw $e;
    }
}
```

## Good Example

```php
// Automatic failover — pass array of providers
use App\Ai\Agents\SalesCoach;
use Laravel\Ai\Enums\Lab;

$response = (new SalesCoach)->prompt(
    'Analyze this sales transcript...',
    provider: [Lab::OpenAI, Lab::Anthropic],
);
```

```php
// Failover for image generation
use Laravel\Ai\Image;

$image = Image::of('A donut sitting on the kitchen counter')
    ->generate(provider: [Lab::Gemini, Lab::xAI]);
```

```php
// Per-provider options with failover
use Laravel\Ai\Contracts\HasProviderOptions;

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

// Each fallback provider receives its own configuration
$response = (new SalesCoach)->prompt(
    'Analyze this...',
    provider: [Lab::OpenAI, Lab::Anthropic],
);
```

## Why

- **Resilience**: Automatic failover on service interruptions or rate limits
- **Zero code changes**: Same agent, same prompt — just pass an array
- **Per-provider config**: Each fallback can have its own options via `providerOptions()`
- **Works everywhere**: Agents, images, audio, embeddings — all support failover

Reference: [Laravel AI SDK — Failover](https://laravel.com/docs/13.x/ai-sdk#failover)
