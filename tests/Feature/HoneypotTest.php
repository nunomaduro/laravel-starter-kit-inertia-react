<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Sleep;
use Spatie\Honeypot\ProtectAgainstSpam;

beforeEach(function (): void {
    config([
        'honeypot.enabled' => true,
        'honeypot.name_field_name' => 'my_name',
        'honeypot.randomize_name_field_name' => false,
        'honeypot.valid_from_field_name' => 'valid_from',
        'honeypot.valid_from_timestamp' => true,
        'honeypot.amount_of_seconds' => 1,
        'honeypot.honeypot_fields_required_for_all_forms' => false,
    ]);

    Route::post('/test-form', fn () => response()->json(['success' => true]))
        ->middleware(ProtectAgainstSpam::class);
});

it('allows legitimate form submissions with valid honeypot fields', function (): void {
    Sleep::sleep(2);

    $response = $this->post('/test-form', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'my_name' => '',
        'valid_from' => encrypt(now()->subSeconds(2)->timestamp),
    ]);

    $response->assertSuccessful();

    expect($response->json('success'))->toBeTrue();
});

it('blocks submissions that are too fast', function (): void {
    // valid_from in the future = form "valid from" not yet reached = too fast
    $response = $this->post('/test-form', [
        'name' => 'Bot Name',
        'email' => 'bot@example.com',
        'my_name' => '',
        'valid_from' => encrypt(now()->addSeconds(10)->timestamp),
    ]);

    $response->assertStatus(200);

    expect($response->getContent())->toBeEmpty();
});

it('blocks submissions with filled honeypot field', function (): void {
    Sleep::sleep(2);

    $response = $this->post('/test-form', [
        'name' => 'Bot Name',
        'email' => 'bot@example.com',
        'my_name' => 'I am a bot',
        'valid_from' => encrypt(now()->subSeconds(2)->timestamp),
    ]);

    $response->assertStatus(200);

    expect($response->getContent())->toBeEmpty();
});

it('can be disabled via configuration', function (): void {
    config(['honeypot.enabled' => false]);

    $response = $this->post('/test-form', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $response->assertSuccessful();

    expect($response->json('success'))->toBeTrue();
});

it('respects route without honeypot middleware', function (): void {
    Route::post('/unprotected-form', fn () => response()->json(['success' => true]));

    $response = $this->post('/unprotected-form', [
        'name' => 'Bot',
        'my_name' => 'filled',
    ]);

    $response->assertSuccessful();

    expect($response->json('success'))->toBeTrue();
});
