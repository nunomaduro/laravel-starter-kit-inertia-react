---
title: Anonymous Agents
impact: MEDIUM
impactDescription: Quick AI interactions without dedicated agent classes
tags: agent, anonymous, inline, ad-hoc
---

## Anonymous Agents

**Impact: MEDIUM (Quick AI interactions without dedicated agent classes)**

Use the `agent()` function for one-off prompts that don't warrant a dedicated agent class. Supports instructions, messages, tools, and structured output inline.

## Bad Example

```php
// Creating a full agent class for a single-use, trivial prompt
// app/Ai/Agents/QuickTranslator.php — overkill for a one-liner
class QuickTranslator implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You translate text to French.';
    }
}

// Used exactly once
$response = (new QuickTranslator)->prompt('Hello, how are you?');
```

## Good Example

```php
use function Laravel\Ai\{agent};

// Simple anonymous agent
$response = agent(
    instructions: 'You are an expert at software development.',
)->prompt('Tell me about Laravel');
```

```php
// Anonymous agent with structured output
use Illuminate\Contracts\JsonSchema\JsonSchema;
use function Laravel\Ai\{agent};

$response = agent(
    schema: fn (JsonSchema $schema) => [
        'number' => $schema->integer()->required(),
    ],
)->prompt('Generate a random number less than 100');

$number = $response['number']; // int
```

```php
// Anonymous agent with tools and messages
use function Laravel\Ai\{agent};

$response = agent(
    instructions: 'You are a helpful assistant.',
    messages: $previousMessages,
    tools: [new MyCustomTool],
)->prompt('Search for relevant documents');
```

## Why

- **No boilerplate**: Skip creating a class for trivial, one-off prompts
- **Full-featured**: Supports instructions, tools, messages, and structured output
- **Inline**: Keep simple AI calls close to where they're used
- **Upgrade path**: Extract to a dedicated agent class when complexity grows

Reference: [Laravel AI SDK — Anonymous Agents](https://laravel.com/docs/13.x/ai-sdk#anonymous-agents)
