<?php

declare(strict_types=1);

namespace Modules\BotStudio\Policies;

use App\Models\User;
use App\Services\TenantContext;
use Modules\BotStudio\Models\AgentDefinition;

final class AgentDefinitionPolicy
{
    public function viewAny(User $user): bool
    {
        return TenantContext::id() !== null;
    }

    public function view(User $user, AgentDefinition $agentDefinition): bool
    {
        return $agentDefinition->canBeViewedBy($user);
    }

    public function create(User $user): bool
    {
        return TenantContext::id() !== null;
    }

    public function update(User $user, AgentDefinition $agentDefinition): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $agentDefinition->canBeEditedBy($user);
    }

    public function delete(User $user, AgentDefinition $agentDefinition): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $agentDefinition->canBeEditedBy($user);
    }

    public function restore(User $user, AgentDefinition $agentDefinition): bool
    {
        return $this->delete($user, $agentDefinition);
    }

    public function forceDelete(User $user, AgentDefinition $agentDefinition): bool
    {
        return $user->isSuperAdmin();
    }
}
