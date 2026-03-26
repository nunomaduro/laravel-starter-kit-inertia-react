---
title: Audio Generation and Transcription
impact: MEDIUM
impactDescription: Text-to-speech and speech-to-text
tags: audio, tts, transcription, stt, voice, diarize
---

## Audio Generation and Transcription

**Impact: MEDIUM (Text-to-speech and speech-to-text)**

Generate audio from text (TTS) and transcribe audio to text (STT). Supports voice selection, instructions, diarization, storage, and queueing.

## Bad Example

```php
// Manual TTS API call — no storage, no voice control
$response = Http::withToken(config('services.openai.key'))
    ->post('https://api.openai.com/v1/audio/speech', [
        'model' => 'tts-1',
        'input' => 'Hello world',
        'voice' => 'alloy',
    ]);

file_put_contents(storage_path('audio.mp3'), $response->body());
```

## Good Example

```php
// Text-to-speech
use Laravel\Ai\Audio;

$audio = Audio::of('I love coding with Laravel.')->generate();
$rawContent = (string) $audio;
```

```php
// Voice selection and instructions
$audio = Audio::of('I love coding with Laravel.')
    ->female()  // or ->male(), ->voice('voice-id')
    ->instructions('Said like a pirate')
    ->generate();
```

```php
// Store audio
$path = $audio->store();
$path = $audio->storeAs('audio.mp3');
$path = $audio->storePublicly();
```

```php
// Queue audio generation
use Laravel\Ai\Responses\AudioResponse;

Audio::of('I love coding with Laravel.')
    ->queue()
    ->then(function (AudioResponse $audio) {
        $path = $audio->store();
    });
```

```php
// Speech-to-text transcription
use Laravel\Ai\Transcription;

$transcript = Transcription::fromPath('/home/laravel/audio.mp3')->generate();
$transcript = Transcription::fromStorage('audio.mp3')->generate();
$transcript = Transcription::fromUpload($request->file('audio'))->generate();

return (string) $transcript;
```

```php
// Diarization — segment transcript by speaker
$transcript = Transcription::fromStorage('audio.mp3')
    ->diarize()
    ->generate();
```

```php
// Queue transcription
use Laravel\Ai\Responses\TranscriptionResponse;

Transcription::fromStorage('audio.mp3')
    ->queue()
    ->then(function (TranscriptionResponse $transcript) {
        // Process transcript
    });
```

## Why

- **Fluent API**: Voice, instructions, and quality in a single chain
- **Multiple sources**: Generate from text, transcribe from path, storage, or upload
- **Diarization**: Speaker-segmented transcripts for meetings and interviews
- **Queueable**: Offload long audio processing to background workers
- **Provider support**: TTS via OpenAI, ElevenLabs. STT via OpenAI, ElevenLabs, Mistral

Reference: [Laravel AI SDK — Audio](https://laravel.com/docs/13.x/ai-sdk#audio)
