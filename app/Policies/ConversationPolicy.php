<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Conversation;
use App\Models\Property;
use App\Models\User;

final readonly class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $user->id === $conversation->guest_id
            || $user->id === $conversation->host_id;
    }

    public function create(User $user, Property $property): bool
    {
        return $user->id !== $property->host_id;
    }

    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $user->id === $conversation->guest_id
            || $user->id === $conversation->host_id;
    }
}
