<?php

declare(strict_types=1);

namespace Modules\BotStudio\Services;

use App\Ai\Agents\OrgScopedAgent;
use App\Models\Organization;
use App\Models\User;
use App\Support\ModuleToolRegistry;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\StreamableAgentResponse;
use Modules\BotStudio\Ai\Tools\KnowledgeSearchTool;
use Modules\BotStudio\Models\AgentDefinition;
use RuntimeException;

/**
 * Fluent service that configures an OrgScopedAgent from an AgentDefinition at runtime.
 *
 * Resolves system prompt variables, filters enabled tools against the organization's
 * available tools, injects KnowledgeSearchTool when indexed knowledge files exist,
 * and returns a streaming response.
 */
final class AgentRunner
{
    private ?AgentDefinition $definition = null;

    private ?User $user = null;

    private ?Organization $organization = null;

    /** @var array{page?: string, entity_type?: string, entity_id?: int, entity_name?: string} */
    private array $context = [];

    public function __construct(
        private ModuleToolRegistry $toolRegistry,
    ) {}

    /**
     * Set the agent definition to configure from.
     */
    public function forDefinition(AgentDefinition $definition): self
    {
        $this->definition = $definition;

        if ($definition->organization !== null) {
            $this->organization = $definition->organization;
        }

        return $this;
    }

    /**
     * Set the user interacting with the agent.
     */
    public function withUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Set the organization context explicitly.
     */
    public function withOrganization(Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Set the page context for the agent interaction.
     *
     * @param  array{page?: string, entity_type?: string, entity_id?: int, entity_name?: string}  $context
     */
    public function withContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Stream a response from the configured agent.
     */
    public function stream(string $prompt): StreamableAgentResponse
    {
        return $this->buildAgent()->stream($prompt);
    }

    /**
     * Get a non-streaming response from the configured agent.
     */
    public function prompt(string $prompt): \Laravel\Ai\Responses\AgentResponse
    {
        return $this->buildAgent()->prompt($prompt);
    }

    /**
     * Build the configured OrgScopedAgent.
     */
    public function buildAgent(): OrgScopedAgent
    {
        if ($this->definition === null) {
            throw new RuntimeException('AgentDefinition is required. Call forDefinition() first.');
        }

        if ($this->user === null) {
            throw new RuntimeException('User is required. Call withUser() first.');
        }

        if ($this->organization === null) {
            throw new RuntimeException('Organization is required. Call withOrganization() or use forDefinition() with an org-scoped definition.');
        }

        $agent = new OrgScopedAgent(
            $this->organization,
            $this->user,
            $this->toolRegistry,
        );

        $agent->withContext($this->context);
        $agent->withCustomPrompt($this->resolvePromptVariables());
        $agent->withCustomTools($this->resolveTools());

        return $agent;
    }

    /**
     * Resolve system prompt variables with real values.
     */
    public function resolvePromptVariables(): string
    {
        if ($this->definition === null || $this->user === null || $this->organization === null) {
            throw new RuntimeException('Definition, user, and organization must be set before resolving prompt variables.');
        }

        $prompt = $this->definition->system_prompt;

        return Str::replace(
            ['{{org_name}}', '{{user_name}}', '{{current_date}}'],
            [$this->organization->name, $this->user->name, now()->toDateString()],
            $prompt,
        );
    }

    /**
     * Resolve the tools available for this agent definition.
     *
     * Filters the definition's enabled_tools against the organization's available
     * tools from the registry, then injects KnowledgeSearchTool if the agent has
     * indexed knowledge files.
     *
     * @return array<int, object>
     */
    public function resolveTools(): array
    {
        if ($this->definition === null || $this->organization === null) {
            throw new RuntimeException('Definition and organization must be set before resolving tools.');
        }

        $enabledToolClasses = $this->definition->enabled_tools ?? [];
        $availableTools = $this->toolRegistry->getToolsForOrganization($this->organization);

        // Filter to only tools that are both enabled in the definition AND available to the org
        $tools = array_values(array_filter(
            $availableTools,
            fn (object $tool): bool => in_array($tool::class, $enabledToolClasses, true),
        ));

        // Inject KnowledgeSearchTool if agent has indexed knowledge files
        $indexedFileIds = $this->getIndexedKnowledgeFileIds();

        if ($indexedFileIds !== []) {
            $tools[] = new KnowledgeSearchTool(
                $indexedFileIds,
                $this->organization->id,
            );
        }

        return $tools;
    }

    /**
     * Get IDs of knowledge files that have been fully indexed.
     *
     * @return array<int, int>
     */
    private function getIndexedKnowledgeFileIds(): array
    {
        if ($this->definition === null) {
            return [];
        }

        /** @var array<int, int> $ids */
        $ids = $this->definition->knowledgeFiles()
            ->where('status', 'indexed')
            ->pluck('id')
            ->all();

        return $ids;
    }
}
