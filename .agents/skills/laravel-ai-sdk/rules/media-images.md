---
title: Image Generation
impact: MEDIUM
impactDescription: Generate images from text prompts
tags: image, generate, store, queue, openai, gemini
---

## Image Generation

**Impact: MEDIUM (Generate images from text prompts)**

Use `Laravel\Ai\Image` to generate images from text prompts. Supports aspect ratios, quality settings, reference images, storage, and background queueing.

## Bad Example

```php
// Manual API call — provider-specific, no storage helpers
$response = Http::withToken(config('services.openai.key'))
    ->post('https://api.openai.com/v1/images/generations', [
        'prompt' => 'A donut on a kitchen counter',
        'size' => '1024x1024',
    ]);

$imageUrl = $response->json('data.0.url');
// Now manually download and store...
```

## Good Example

```php
use Laravel\Ai\Image;

// Generate an image
$image = Image::of('A donut sitting on the kitchen counter')->generate();
$rawContent = (string) $image;
```

```php
// Aspect ratio and quality
$image = Image::of('A donut sitting on the kitchen counter')
    ->quality('high')
    ->landscape()  // or ->portrait(), ->square()
    ->timeout(120)
    ->generate();
```

```php
// Reference images for style transfer
use Laravel\Ai\Files;

$image = Image::of('Update this photo to impressionist style.')
    ->attachments([
        Files\Image::fromStorage('photo.jpg'),
        Files\Image::fromUrl('https://example.com/reference.jpg'),
    ])
    ->landscape()
    ->generate();
```

```php
// Store to filesystem
$path = $image->store();
$path = $image->storeAs('image.jpg');
$path = $image->storePublicly();
$path = $image->storePubliclyAs('image.jpg');
```

```php
// Queue image generation for background processing
use Laravel\Ai\Responses\ImageResponse;

Image::of('A donut sitting on the kitchen counter')
    ->portrait()
    ->queue()
    ->then(function (ImageResponse $image) {
        $path = $image->store();
    });
```

## Why

- **Fluent API**: Chain aspect ratio, quality, and timeout in one expression
- **Storage built-in**: `store()` and `storeAs()` save to your configured disk
- **Queueable**: Offload generation to background workers
- **Provider support**: OpenAI, Gemini, xAI

Reference: [Laravel AI SDK — Images](https://laravel.com/docs/13.x/ai-sdk#images)
