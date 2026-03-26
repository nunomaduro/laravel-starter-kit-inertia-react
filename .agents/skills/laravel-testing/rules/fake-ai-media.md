---
title: Fake AI Image, Audio, and Transcription
impact: HIGH
impactDescription: Test media generation without real API calls
tags: testing, ai, image, audio, transcription, fake, assert, laravel-ai-sdk
---

## Fake AI Image, Audio, and Transcription

**Impact: HIGH (Test media generation without real API calls)**

Use `Image::fake()`, `Audio::fake()`, and `Transcription::fake()` to prevent real generation calls. Assert prompts, aspect ratios, voices, and diarization.

## Bad Example — Pest

```php
<?php

use Laravel\Ai\Image;

test('generates product image', function () {
    // Real API call — slow, expensive, different image every run
    $image = Image::of('Product photo')->landscape()->generate();
    expect((string) $image)->not->toBeEmpty();
});
```

## Good Example — Pest

```php
<?php

use Laravel\Ai\Audio;
use Laravel\Ai\Image;
use Laravel\Ai\Transcription;
use Laravel\Ai\Prompts\AudioPrompt;
use Laravel\Ai\Prompts\ImagePrompt;
use Laravel\Ai\Prompts\TranscriptionPrompt;

test('generates landscape product image', function () {
    Image::fake();

    Image::of('A sunset over the ocean')->landscape()->generate();

    Image::assertGenerated(function (ImagePrompt $prompt) {
        return $prompt->contains('sunset') && $prompt->isLandscape();
    });
});

test('generates welcome audio', function () {
    Audio::fake();

    Audio::of('Welcome to our app.')->female()->generate();

    Audio::assertGenerated(function (AudioPrompt $prompt) {
        return $prompt->contains('Welcome') && $prompt->isFemale();
    });
});

test('transcribes uploaded audio with diarization', function () {
    Transcription::fake(['Meeting transcript text here.']);

    $transcript = Transcription::fromStorage('meeting.mp3')
        ->diarize()
        ->generate();

    expect((string) $transcript)->toBe('Meeting transcript text here.');

    Transcription::assertGenerated(function (TranscriptionPrompt $prompt) {
        return $prompt->isDiarized();
    });
});

test('no unexpected media generation', function () {
    Image::fake()->preventStrayImages();
    Audio::fake()->preventStrayAudio();
    Transcription::fake()->preventStrayTranscriptions();
});
```

## Good Example — PHPUnit

```php
<?php

namespace Tests\Feature;

use Laravel\Ai\Image;
use Laravel\Ai\Prompts\ImagePrompt;
use Tests\TestCase;

class ImageGenerationTest extends TestCase
{
    public function test_generates_landscape_product_image(): void
    {
        Image::fake();

        Image::of('A sunset over the ocean')->landscape()->generate();

        Image::assertGenerated(function (ImagePrompt $prompt) {
            return $prompt->contains('sunset') && $prompt->isLandscape();
        });
    }
}
```

## Why It Matters

- **Fast**: No image/audio generation — tests complete in milliseconds
- **Deterministic**: Controlled fake responses every run
- **Assertable**: Verify prompts, aspect ratios, voices, and diarization settings
- **Prevent leaks**: `preventStray*()` methods catch accidental real API calls

Reference: [Laravel AI SDK — Testing](https://laravel.com/docs/13.x/ai-sdk#testing)
