---
title: Streaming, Broadcasting, and Queueing
impact: HIGH
impactDescription: Real-time and background AI processing
tags: agent, streaming, broadcast, queue, sse, vercel
---

## Streaming, Broadcasting, and Queueing

**Impact: HIGH (Real-time and background AI processing)**

Stream agent responses for real-time UX, broadcast to channels for live updates, or queue prompts for background processing.

## Bad Example

```php
// Blocking request — user waits for entire response before seeing anything
Route::post('/coach', function (Request $request) {
    $response = (new SalesCoach)->prompt($request->input('message'));

    // User stares at spinner for 10+ seconds
    return response()->json(['reply' => (string) $response]);
});
```

## Good Example

```php
// Stream — return SSE directly from route
use App\Ai\Agents\SalesCoach;

Route::get('/coach', function () {
    return (new SalesCoach)->stream('Analyze this sales transcript...');
});
```

```php
// Stream with completion callback
use Laravel\Ai\Responses\StreamedAgentResponse;

Route::get('/coach', function () {
    return (new SalesCoach)
        ->stream('Analyze this sales transcript...')
        ->then(function (StreamedAgentResponse $response) {
            // $response->text, $response->events, $response->usage
        });
});
```

```php
// Stream using Vercel AI SDK protocol (for Next.js/React frontends)
Route::get('/coach', function () {
    return (new SalesCoach)
        ->stream('Analyze this sales transcript...')
        ->usingVercelDataProtocol();
});
```

```php
// Iterate through stream events manually
$stream = (new SalesCoach)->stream('Analyze this transcript...');

foreach ($stream as $event) {
    // Process each chunk
}
```

```php
// Broadcast streamed events to a channel
use Illuminate\Broadcasting\Channel;

$stream = (new SalesCoach)->stream('Analyze this transcript...');

foreach ($stream as $event) {
    $event->broadcast(new Channel('channel-name'));
}

// Or queue the entire operation and broadcast automatically
(new SalesCoach)->broadcastOnQueue(
    'Analyze this transcript...',
    new Channel('channel-name'),
);
```

```php
// Queue — process in background with then/catch callbacks
use Laravel\Ai\Responses\AgentResponse;

Route::post('/coach', function (Request $request) {
    (new SalesCoach)
        ->queue($request->input('transcript'))
        ->then(function (AgentResponse $response) {
            // Handle response in background
        })
        ->catch(function (Throwable $e) {
            // Handle failure
        });

    return back();
});
```

## Why

- **Better UX**: Streaming shows tokens as they arrive — no waiting for full response
- **Scalable**: Queueing offloads long prompts to background workers
- **Real-time**: Broadcasting pushes updates to WebSocket clients
- **Protocol support**: Vercel AI SDK protocol for seamless React/Next.js integration

Reference: [Laravel AI SDK — Streaming](https://laravel.com/docs/13.x/ai-sdk#streaming)
