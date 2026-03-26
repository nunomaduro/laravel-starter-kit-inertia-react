<?php

declare(strict_types=1);

namespace Modules\BotStudio\Http\Controllers;

use App\Services\TenantContext;
use App\Settings\BotStudioSettings;
use App\Support\ModuleToolRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\BotStudio\Http\Requests\StoreAgentDefinitionRequest;
use Modules\BotStudio\Http\Requests\UpdateAgentDefinitionRequest;
use Modules\BotStudio\Models\AgentDefinition;

final readonly class AgentDefinitionController
{
    public function __construct(
        private ModuleToolRegistry $toolRegistry,
        private BotStudioSettings $settings,
    ) {}

    public function index(Request $request): Response
    {
        $agents = AgentDefinition::query()
            ->where('is_template', false)
            ->with('creator')
            ->withCount('conversations')
            ->latest()
            ->paginate(15);

        $currentCount = AgentDefinition::query()
            ->where('is_template', false)
            ->where('organization_id', TenantContext::id())
            ->count();

        return Inertia::render('bot-studio/index', [
            'agents' => $agents,
            'currentCount' => $currentCount,
            'maxCount' => (int) config('bot-studio.max_agents_per_org', 50),
        ]);
    }

    public function templates(): Response
    {
        $templates = AgentDefinition::query()
            ->where('is_template', true)
            ->latest()
            ->paginate(15);

        return Inertia::render('bot-studio/templates', [
            'templates' => $templates,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('bot-studio/create', [
            'availableTools' => $this->getAvailableToolNames(),
            'allowedModels' => $this->getAllowedModels(),
        ]);
    }

    public function store(StoreAgentDefinitionRequest $request): RedirectResponse
    {
        $org = TenantContext::organization();
        $activePlan = $org?->activePlan();
        $planSlug = $activePlan?->slug ?? 'basic';
        $planFeatures = config("billing.plan_features.{$planSlug}", []);
        $isProOrHigher = in_array('bot_studio', $planFeatures, true);

        $maxAgents = $isProOrHigher
            ? $this->settings->max_agents_pro
            : $this->settings->max_agents_basic;

        if ($maxAgents > 0) {
            $currentCount = AgentDefinition::query()
                ->where('is_template', false)
                ->where('organization_id', TenantContext::id())
                ->count();

            if ($currentCount >= $maxAgents) {
                return back()->withErrors([
                    'limit' => __("You've reached your agent limit. Upgrade your plan for more."),
                ]);
            }
        }

        $definition = AgentDefinition::query()->create($request->validated());

        return to_route('bot-studio.edit', $definition)
            ->with('status', __('Agent created.'));
    }

    public function edit(AgentDefinition $agentDefinition): Response
    {
        $agentDefinition->load('knowledgeFiles', 'creator', 'embedTokens');

        return Inertia::render('bot-studio/edit', [
            'definition' => $agentDefinition,
            'availableTools' => $this->getAvailableToolNames(),
            'allowedModels' => $this->getAllowedModels(),
        ]);
    }

    public function update(UpdateAgentDefinitionRequest $request, AgentDefinition $agentDefinition): RedirectResponse
    {
        $agentDefinition->update($request->validated());

        return to_route('bot-studio.edit', $agentDefinition)
            ->with('status', __('Agent updated.'));
    }

    public function destroy(AgentDefinition $agentDefinition): RedirectResponse
    {
        $agentDefinition->delete();

        return to_route('bot-studio.index')
            ->with('status', __('Agent deleted.'));
    }

    public function duplicate(AgentDefinition $agentDefinition): RedirectResponse
    {
        $copy = $agentDefinition->replicate([
            'slug',
            'is_template',
            'is_published',
            'is_featured',
            'total_conversations',
            'total_messages',
        ]);

        $copy->fill([
            'name' => $agentDefinition->name.' (Copy)',
            'organization_id' => TenantContext::id(),
            'created_by' => auth()->id(),
            'is_template' => false,
            'is_published' => false,
            'is_featured' => false,
            'cloned_from' => $agentDefinition->id,
        ]);

        $copy->save();

        return to_route('bot-studio.edit', $copy)
            ->with('status', __('Agent duplicated.'));
    }

    /**
     * Get tool names available to the current organization.
     *
     * @return array<int, array{class: string, name: string}>
     */
    private function getAvailableToolNames(): array
    {
        $org = TenantContext::organization();

        if ($org === null) {
            return [];
        }

        $tools = $this->toolRegistry->getToolsForOrganization($org);

        return array_values(array_map(
            fn (object $tool): array => [
                'class' => $tool::class,
                'name' => method_exists($tool, 'name') ? $tool->name() : class_basename($tool),
            ],
            $tools,
        ));
    }

    /**
     * @return array<int, string>
     */
    private function getAllowedModels(): array
    {
        /** @var array<int, string> $models */
        $models = config('bot-studio.allowed_models', ['gpt-4o-mini', 'gpt-4o', 'claude-sonnet-4-5', 'claude-haiku-4-5']);

        return $models;
    }
}
