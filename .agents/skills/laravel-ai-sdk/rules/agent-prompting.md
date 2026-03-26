---
title: Prompting Agents and Conversation Context
impact: CRITICAL
impactDescription: Core interaction pattern for all AI agent usage
tags: agent, prompt, conversation, context, remember
---

## Prompting Agents and Conversation Context

**Impact: CRITICAL (Core interaction pattern for all AI agent usage)**

Prompt agents with `prompt()`, pass conversation history with the `Conversational` interface, or use `RemembersConversations` for automatic persistence.

## Bad Example

```php
// No conversation history — agent has no memory between requests
class ChatController extends Controller
{
    public function respond(Request $request)
    {
        // Every request starts fresh — no context from previous messages
        $response = (new SalesCoach)->prompt($request->input('message'));

        return response()->json(['reply' => (string) $response]);
    }
}
```

## Good Example

```php
// Prompt with make() for dependency injection
use App\Ai\Agents\SalesCoach;

$agent = SalesCoach::make(user: $user);
$response = $agent->prompt('Analyze this sales transcript...');

return (string) $response;
```

```php
// Override provider or model per-prompt
use Laravel\Ai\Enums\Lab;

$response = (new SalesCoach)->prompt(
    'Analyze this sales transcript...',
    provider: Lab::Anthropic,
    model: 'claude-haiku-4-5-20251001',
    timeout: 120,
);
```

```php
// Attach files to a prompt
use Laravel\Ai\Files;

$response = (new SalesCoach)->prompt(
    'Analyze the attached sales transcript...',
    attachments: [
        Files\Document::fromStorage('transcript.pdf'),
        Files\Image::fromPath('/home/laravel/chart.png'),
        $request->file('transcript'),
    ],
);
```

```php
// Manual conversation context — implement Conversational
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Messages\Message;

class SalesCoach implements Agent, Conversational
{
    use Promptable;

    public function __construct(public User $user) {}

    public function instructions(): string
    {
        return 'You are a sales coach.';
    }

    public function messages(): iterable
    {
        return History::where('user_id', $this->user->id)
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->map(fn ($msg) => new Message($msg->role, $msg->content))
            ->all();
    }
}
```

```php
// Automatic conversation persistence — RemembersConversations
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Conversational;

class SalesCoach implements Agent, Conversational
{
    use Promptable, RemembersConversations;

    public function instructions(): string
    {
        return 'You are a sales coach.';
    }
}

// Start a new conversation
$response = (new SalesCoach)->forUser($user)->prompt('Hello!');
$conversationId = $response->conversationId;

// Continue an existing conversation
$response = (new SalesCoach)
    ->continue($conversationId, as: $user)
    ->prompt('Tell me more about that.');
```

## Why

- **Stateful conversations**: Users can have multi-turn conversations with agents
- **Automatic persistence**: `RemembersConversations` stores messages to database
- **Flexible context**: Implement `messages()` for custom storage (Redis, external API, etc.)
- **Attachments**: Send documents and images alongside prompts

Reference: [Laravel AI SDK — Prompting](https://laravel.com/docs/13.x/ai-sdk#prompting)
