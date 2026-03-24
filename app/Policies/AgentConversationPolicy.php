<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AgentConversation;
use App\Models\User;

final class AgentConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AgentConversation $agentConversation): bool
    {
        return $user->id === $agentConversation->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, AgentConversation $agentConversation): bool
    {
        return $user->id === $agentConversation->user_id;
    }

    public function delete(User $user, AgentConversation $agentConversation): bool
    {
        return $user->id === $agentConversation->user_id;
    }

    public function restore(User $user, AgentConversation $agentConversation): bool
    {
        return $user->id === $agentConversation->user_id;
    }

    public function forceDelete(User $user, AgentConversation $agentConversation): bool
    {
        return $user->id === $agentConversation->user_id;
    }
}
