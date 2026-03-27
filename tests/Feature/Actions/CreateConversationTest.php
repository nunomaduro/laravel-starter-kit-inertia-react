<?php

declare(strict_types=1);

use App\Actions\CreateConversation;
use App\Models\Conversation;
use App\Models\Property;
use App\Models\User;

it('creates a new conversation', function (): void {
    $host = User::factory()->host()->create();
    $property = Property::factory()->create(['host_id' => $host->id]);
    $guest = User::factory()->create();

    $conversation = app(CreateConversation::class)->handle($guest, $property);

    expect($conversation)->toBeInstanceOf(Conversation::class)
        ->and($conversation->guest_id)->toBe($guest->id)
        ->and($conversation->host_id)->toBe($host->id)
        ->and($conversation->property_id)->toBe($property->id);
});

it('returns existing conversation for same guest and property', function (): void {
    $host = User::factory()->host()->create();
    $property = Property::factory()->create(['host_id' => $host->id]);
    $guest = User::factory()->create();

    $existing = Conversation::factory()->create([
        'guest_id' => $guest->id,
        'host_id' => $host->id,
        'property_id' => $property->id,
    ]);

    $conversation = app(CreateConversation::class)->handle($guest, $property);

    expect($conversation->id)->toBe($existing->id);
    $this->assertDatabaseCount('conversations', 1);
});
