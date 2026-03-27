<?php

declare(strict_types=1);

use App\Actions\SendMessage;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

it('sends a message in a conversation', function (): void {
    $conversation = Conversation::factory()->create();
    $sender = User::factory()->create();

    $message = app(SendMessage::class)->handle($conversation, $sender, 'Hello!');

    expect($message)->toBeInstanceOf(Message::class)
        ->and($message->conversation_id)->toBe($conversation->id)
        ->and($message->sender_id)->toBe($sender->id)
        ->and($message->body)->toBe('Hello!')
        ->and($message->read_at)->toBeNull();
});
