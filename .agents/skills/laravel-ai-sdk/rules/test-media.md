---
title: Testing Images, Audio, and Transcriptions
impact: HIGH
impactDescription: Fake media generation in tests
tags: testing, image, audio, transcription, fake, assert
---

## Testing Images, Audio, and Transcriptions

**Impact: HIGH (Fake media generation in tests)**

Fake image, audio, and transcription generation to avoid real API calls. Assert that media was generated with expected parameters.

## Bad Example

```php
// Real image generation in tests — slow and expensive
test('generates product image', function () {
    $image = Image::of('Product photo')->generate();
    // Real API call, costs money, slow
    expect((string) $image)->not->toBeEmpty();
});
```

## Good Example

```php
use Laravel\Ai\Image;
use Laravel\Ai\Prompts\ImagePrompt;

// Fake image generation
test('generates product image', function () {
    Image::fake();

    Image::of('A sunset over the ocean')->landscape()->generate();

    Image::assertGenerated(function (ImagePrompt $prompt) {
        return $prompt->contains('sunset') && $prompt->isLandscape();
    });

    Image::assertNotGenerated('Missing prompt');
    Image::assertNothingGenerated(); // fails — was generated
});
```

```php
// Fake with specific responses
test('stores generated image', function () {
    Image::fake([
        base64_encode($firstImage),
        base64_encode($secondImage),
    ]);

    $image = Image::of('A donut')->generate();
    $path = $image->store();
});
```

```php
// Assert queued image generations
use Laravel\Ai\Prompts\QueuedImagePrompt;

test('queues image generation', function () {
    Image::fake();

    Image::of('A donut')->queue();

    Image::assertQueued(fn (QueuedImagePrompt $prompt) => $prompt->contains('donut'));
});
```

```php
// Prevent stray image generations
test('no unexpected generations', function () {
    Image::fake()->preventStrayImages();
});
```

```php
use Laravel\Ai\Audio;
use Laravel\Ai\Prompts\AudioPrompt;

// Fake audio generation
test('generates welcome audio', function () {
    Audio::fake();

    Audio::of('Welcome to our app.')->female()->generate();

    Audio::assertGenerated(function (AudioPrompt $prompt) {
        return $prompt->contains('Welcome') && $prompt->isFemale();
    });
});
```

```php
use Laravel\Ai\Transcription;
use Laravel\Ai\Prompts\TranscriptionPrompt;

// Fake transcription
test('transcribes uploaded audio', function () {
    Transcription::fake(['Transcribed text from the audio file.']);

    $transcript = Transcription::fromStorage('audio.mp3')->diarize()->generate();

    Transcription::assertGenerated(function (TranscriptionPrompt $prompt) {
        return $prompt->isDiarized();
    });
});
```

## Why

- **Fast**: No image/audio generation — tests run in milliseconds
- **Deterministic**: Controlled fake responses every run
- **Assertable**: Verify prompts, aspect ratios, voices, and diarization settings
- **Prevent leaks**: `preventStrayImages()` / `preventStrayAudio()` catch accidental real calls

Reference: [Laravel AI SDK — Testing](https://laravel.com/docs/13.x/ai-sdk#testing)
