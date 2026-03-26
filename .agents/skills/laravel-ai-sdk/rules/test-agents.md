---
title: Testing Agents
impact: HIGH
impactDescription: Fake agent responses and assert prompts in tests
tags: testing, agent, fake, assert, mock
---

## Testing Agents

**Impact: HIGH (Fake agent responses and assert prompts in tests)**

Use `Agent::fake()` to prevent real API calls in tests and control agent responses. Assert that agents were prompted with expected inputs.

## Bad Example

```php
// Calling real AI providers in tests — slow, expensive, non-deterministic
test('coach analyzes transcript', function () {
    $response = (new SalesCoach)->prompt('Analyze this...');

    // Flaky: different response every run, costs money, requires API key
    expect((string) $response)->toContain('feedback');
});
```

## Good Example

```php
use App\Ai\Agents\SalesCoach;
use Laravel\Ai\Prompts\AgentPrompt;

// Auto-generate fixed response for every prompt
test('coach analyzes transcript', function () {
    SalesCoach::fake();

    $response = SalesCoach::make()->prompt('Analyze this transcript...');

    // Assert the agent was prompted
    SalesCoach::assertPrompted('Analyze this transcript...');
});
```

```php
// Provide specific responses
test('coach returns expected feedback', function () {
    SalesCoach::fake([
        'Great job on the opening.',
        'Second response for next prompt.',
    ]);

    $response = SalesCoach::make()->prompt('Analyze this...');

    expect((string) $response)->toBe('Great job on the opening.');
});
```

```php
// Dynamic responses based on prompt content
test('coach responds contextually', function () {
    SalesCoach::fake(function (AgentPrompt $prompt) {
        return 'Response for: ' . $prompt->prompt;
    });

    $response = SalesCoach::make()->prompt('Analyze this...');

    expect((string) $response)->toBe('Response for: Analyze this...');
});
```

```php
// Assert with closures for flexible matching
test('coach receives transcript', function () {
    SalesCoach::fake();

    SalesCoach::make()->prompt('Analyze the Q4 sales transcript...');

    SalesCoach::assertPrompted(function (AgentPrompt $prompt) {
        return $prompt->contains('Q4');
    });

    SalesCoach::assertNotPrompted('Missing prompt');
    SalesCoach::assertNeverPrompted(); // fails — was prompted
});
```

```php
// Assert queued agent invocations
use Laravel\Ai\QueuedAgentPrompt;

test('coach is queued', function () {
    SalesCoach::fake();

    SalesCoach::make()->queue('Analyze this...');

    SalesCoach::assertQueued(function (QueuedAgentPrompt $prompt) {
        return $prompt->contains('Analyze');
    });
});
```

```php
// Prevent stray prompts — fail if any agent is called without a fake
test('no unexpected agent calls', function () {
    SalesCoach::fake()->preventStrayPrompts();

    // SalesCoach::make()->prompt('...'); // OK — has fake
    // (new OtherAgent)->prompt('...');   // Would throw — no fake
});
```

**Structured output agents auto-generate fake data matching the schema.**

## Why

- **Fast**: No API calls, no network latency
- **Deterministic**: Same response every run
- **Free**: No API costs in test suites
- **Assertable**: Verify exactly what was prompted and how many times
- **Safe**: `preventStrayPrompts()` catches accidental real API calls

Reference: [Laravel AI SDK — Testing Agents](https://laravel.com/docs/13.x/ai-sdk#testing-agents)
