<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Contracts\ContextAwareTool;
use App\Ai\Middleware\WithMemoryUnlessUnavailable;
use App\Models\Organization;
use App\Models\User;
use App\Services\TenantContext;
use App\Support\ModuleToolRegistry;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

/**
 * Organization-scoped agent that injects tenant context into every AI interaction.
 *
 * Resolves tools from ModuleToolRegistry filtered by the current organization's
 * feature flags and passes page context to ContextAwareTool implementations.
 */
final class OrgScopedAgent implements Agent, Conversational, HasMiddleware, HasTools
{
    use Promptable;
    use RemembersConversations;

    /** @var array{page?: string, entity_type?: string, entity_id?: int, entity_name?: string} */
    private array $pageContext = [];

    public function __construct(
        private Organization $organization,
        private User $user,
        private ModuleToolRegistry $toolRegistry,
    ) {}

    /**
     * Create a new agent using the current tenant context and authenticated user.
     */
    public static function make(): self
    {
        $organization = Organization::findOrFail(TenantContext::id());

        /** @var User $user */
        $user = auth()->user();

        return new self(
            $organization,
            $user,
            app(ModuleToolRegistry::class),
        );
    }

    /**
     * Set the page context for this agent interaction.
     *
     * @param  array{page?: string, entity_type?: string, entity_id?: int, entity_name?: string}  $context
     */
    public function withContext(array $context): self
    {
        $this->pageContext = $context;

        return $this;
    }

    public function instructions(): string
    {
        $instructions = "You are an AI assistant for the organization \"{$this->organization->name}\". "
            .'Help the user with their tasks using the available tools.';

        if (isset($this->pageContext['page'])) {
            $instructions .= "\n\nThe user is currently on page: {$this->pageContext['page']}.";

            if (isset($this->pageContext['entity_type'], $this->pageContext['entity_name'])) {
                $instructions .= " They are viewing a {$this->pageContext['entity_type']}"
                    ." named {$this->pageContext['entity_name']}.";
            }
        }

        return $instructions;
    }

    public function tools(): iterable
    {
        $tools = $this->toolRegistry->getToolsForOrganization($this->organization);

        foreach ($tools as $tool) {
            if ($tool instanceof ContextAwareTool) {
                $tool->setContext($this->pageContext);
            }
        }

        return $tools;
    }

    public function middleware(): array
    {
        $context = array_filter([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
        ]);

        return [
            new WithMemoryUnlessUnavailable($context, limit: (int) config('memory.middleware_recall_limit', 5)),
        ];
    }
}
