<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AgentConversation;
use App\Models\AgentConversationMessage;
use App\Models\User;

final class AgentConversationMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AgentConversationMessage $message): bool
    {
        return $this->ownsConversation($user, $message);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, AgentConversationMessage $message): bool
    {
        return $this->ownsConversation($user, $message);
    }

    public function delete(User $user, AgentConversationMessage $message): bool
    {
        return $this->ownsConversation($user, $message);
    }

    public function restore(User $user, AgentConversationMessage $message): bool
    {
        return $this->ownsConversation($user, $message);
    }

    public function forceDelete(User $user, AgentConversationMessage $message): bool
    {
        return $this->ownsConversation($user, $message);
    }

    /**
     * Check ownership by delegating to the parent conversation.
     */
    private function ownsConversation(User $user, AgentConversationMessage $message): bool
    {
        $conversation = $message->relationLoaded('conversation')
            ? $message->conversation
            : $message->conversation()->first();

        if (! $conversation instanceof AgentConversation) {
            return false;
        }

        return $user->id === $conversation->user_id;
    }
}
