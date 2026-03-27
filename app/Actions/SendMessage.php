<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

final readonly class SendMessage
{
    public function handle(Conversation $conversation, User $sender, string $body): Message
    {
        return Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'body' => $body,
        ]);
    }
}
