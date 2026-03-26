---
title: Fake AI Agent Responses
impact: HIGH
impactDescription: Test agent interactions without real API calls
tags: testing, ai, agent, fake, assert, laravel-ai-sdk
---

## Fake AI Agent Responses

**Impact: HIGH (Test agent interactions without real API calls)**

Use `Agent::fake()` to prevent real AI provider calls in tests. Fake responses can be static strings, arrays, or dynamic closures. Assert that agents were prompted with expected inputs.

## Bad Example — Pest

```php
<?php

use App\Ai\Agents\SalesCoach;

test('coach analyzes transcript', function () {
    // Real API call — slow, expensive, non-deterministic
    $response = SalesCoach::make()->prompt('Analyze this...');

    // Different response every run, costs money, requires API key in CI
    expect((string) $response)->toContain('feedback');
});
```

## Good Example — Pest

```php
<?php

use App\Ai\Agents\SalesCoach;
use Laravel\Ai\Prompts\AgentPrompt;

test('coach analyzes transcript', function () {
    SalesCoach::fake(['Great job on the opening.']);

    $response = SalesCoach::make()->prompt('Analyze this transcript...');

    expect((string) $response)->toBe('Great job on the opening.');

    SalesCoach::assertPrompted('Analyze this transcript...');
});

test('coach responds based on prompt content', function () {
    SalesCoach::fake(function (AgentPrompt $prompt) {
        return 'Response for: ' . $prompt->prompt;
    });

    $response = SalesCoach::make()->prompt('Q4 sales data');

    SalesCoach::assertPrompted(fn (AgentPrompt $prompt) => $prompt->contains('Q4'));
    SalesCoach::assertNotPrompted('Missing prompt');
});

test('no unexpected agent calls', function () {
    SalesCoach::fake()->preventStrayPrompts();

    SalesCoach::make()->prompt('Expected call');
    // Any unfaked agent call would throw
});
```

## Good Example — PHPUnit

```php
<?php

namespace Tests\Feature;

use App\Ai\Agents\SalesCoach;
use Laravel\Ai\Prompts\AgentPrompt;
use Tests\TestCase;

class SalesCoachTest extends TestCase
{
    public function test_coach_analyzes_transcript(): void
    {
        SalesCoach::fake(['Great job on the opening.']);

        $response = SalesCoach::make()->prompt('Analyze this transcript...');

        $this->assertEquals('Great job on the opening.', (string) $response);

        SalesCoach::assertPrompted('Analyze this transcript...');
    }

    public function test_no_unexpected_agent_calls(): void
    {
        SalesCoach::fake()->preventStrayPrompts();

        SalesCoach::make()->prompt('Expected call');
    }
}
```

**Structured output agents auto-generate fake data matching the schema — no manual setup needed.**

## Why It Matters

- **Fast**: No API calls — tests run in milliseconds
- **Deterministic**: Same response every run
- **Free**: No API costs in CI/CD pipelines
- **Safe**: `preventStrayPrompts()` catches accidental real calls

Reference: [Laravel AI SDK — Testing Agents](https://laravel.com/docs/13.x/ai-sdk#testing-agents)
