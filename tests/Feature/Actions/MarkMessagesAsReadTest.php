<?php

declare(strict_types=1);

use App\Actions\MarkMessagesAsRead;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

it('marks unread messages from other user as read', function (): void {
    $guest = User::factory()->create();
    $host = User::factory()->host()->create();
    $conversation = Conversation::factory()->create([
        'guest_id' => $guest->id,
        'host_id' => $host->id,
    ]);

    Message::factory()->count(3)->create([
        'conversation_id' => $conversation->id,
        'sender_id' => $host->id,
    ]);

    Message::factory()->create([
        'conversation_id' => $conversation->id,
        'sender_id' => $guest->id,
    ]);

    $count = app(MarkMessagesAsRead::class)->handle($conversation, $guest);

    expect($count)->toBe(3);
});

it('does not mark own messages as read', function (): void {
    $guest = User::factory()->create();
    $conversation = Conversation::factory()->create(['guest_id' => $guest->id]);

    Message::factory()->count(2)->create([
        'conversation_id' => $conversation->id,
        'sender_id' => $guest->id,
    ]);

    $count = app(MarkMessagesAsRead::class)->handle($conversation, $guest);

    expect($count)->toBe(0);
});
