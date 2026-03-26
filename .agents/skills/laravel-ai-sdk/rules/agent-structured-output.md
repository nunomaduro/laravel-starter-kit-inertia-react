---
title: Structured Output with JSON Schema
impact: HIGH
impactDescription: Type-safe, parseable AI responses
tags: agent, structured-output, schema, json
---

## Structured Output with JSON Schema

**Impact: HIGH (Type-safe, parseable AI responses)**

Implement `HasStructuredOutput` to force agents to return data matching a defined JSON schema. Access the response like an array instead of parsing free-form text.

## Bad Example

```php
// Parsing free-form text — fragile and error-prone
$response = (new SalesCoach)->prompt('Rate this transcript 1-10 and give feedback.');

// Trying to extract structured data from prose
preg_match('/score[:\s]*(\d+)/i', (string) $response, $matches);
$score = (int) ($matches[1] ?? 0); // Unreliable
```

## Good Example

```php
// php artisan make:agent SalesCoach --structured

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class SalesCoach implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a sales coach. Analyze transcripts and return structured feedback.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'feedback' => $schema->string()->required(),
            'score' => $schema->integer()->min(1)->max(10)->required(),
        ];
    }
}
```

```php
// Access structured response like an array
$response = (new SalesCoach)->prompt('Analyze this sales transcript...');

$score = $response['score'];       // int — guaranteed 1-10
$feedback = $response['feedback'];  // string — guaranteed present
```

## Why

- **Reliable parsing**: No regex or string matching — response is always valid JSON
- **Type guarantees**: Schema enforces types, ranges, and required fields
- **Testable**: Faking structured agents auto-generates matching fake data
- **API-ready**: Return structured responses directly from API endpoints

Reference: [Laravel AI SDK — Structured Output](https://laravel.com/docs/13.x/ai-sdk#structured-output)
