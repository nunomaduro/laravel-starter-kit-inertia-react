<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Carbon;

final readonly class MarkMessagesAsRead
{
    public function handle(Conversation $conversation, User $reader): int
    {
        return Message::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);
    }
}
