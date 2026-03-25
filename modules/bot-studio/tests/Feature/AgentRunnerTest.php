<?php

declare(strict_types=1);

use App\Ai\Tools\SemanticSearchTool;
use App\Ai\Tools\UsersIndexAiTool;
use App\Models\Organization;
use App\Services\TenantContext;
use App\Support\ModuleToolRegistry;
use Modules\BotStudio\Ai\Tools\KnowledgeSearchTool;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Models\AgentKnowledgeFile;
use Modules\BotStudio\Services\AgentRunner;

/*
|--------------------------------------------------------------------------
| Bot Studio: AgentRunner Service Tests
|--------------------------------------------------------------------------
*/

// ── Prompt Variable Resolution ──────────────────────────────────────────

it('resolves prompt variables with real values', function (): void {
    $org = Organization::factory()->create(['name' => 'Acme Corp']);
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $agent = AgentDefinition::factory()
        ->forOrganization($org)
        ->create([
            'system_prompt' => 'You work for {{org_name}}. The user is {{user_name}}. Today is {{current_date}}.',
        ]);

    $runner = app(AgentRunner::class)
        ->forDefinition($agent)
        ->withUser($user)
        ->withOrganization($org);

    $resolved = $runner->resolvePromptVariables();

    expect($resolved)
        ->toContain('Acme Corp')
        ->toContain($user->name)
        ->toContain(now()->toDateString())
        ->not->toContain('{{org_name}}')
        ->not->toContain('{{user_name}}')
        ->not->toContain('{{current_date}}');
});

it('handles prompts without variables gracefully', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $agent = AgentDefinition::factory()
        ->forOrganization($org)
        ->create([
            'system_prompt' => 'You are a helpful assistant with no variables.',
        ]);

    $runner = app(AgentRunner::class)
        ->forDefinition($agent)
        ->withUser($user)
        ->withOrganization($org);

    $resolved = $runner->resolvePromptVariables();

    expect($resolved)->toBe('You are a helpful assistant with no variables.');
});

// ── Tool Filtering ──────────────────────────────────────────────────────

it('filters enabled tools against the tool registry', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    // Register tools in the registry
    $registry = app(ModuleToolRegistry::class);
    $registry->registerBaseTool(SemanticSearchTool::class);
    $registry->registerBaseTool(UsersIndexAiTool::class);

    $agent = AgentDefinition::factory()
        ->forOrganization($org)
        ->create([
            'enabled_tools' => [SemanticSearchTool::class],
        ]);

    $runner = new AgentRunner($registry);
    $runner->forDefinition($agent)
        ->withUser($user)
        ->withOrganization($org);

    $tools = $runner->resolveTools();

    $toolClasses = array_map(fn (object $tool): string => $tool::class, $tools);

    expect($toolClasses)->toContain(SemanticSearchTool::class)
        ->and($toolClasses)->not->toContain(UsersIndexAiTool::class);
});

it('returns empty tools when none of the enabled tools are available', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $registry = app(ModuleToolRegistry::class);

    $agent = AgentDefinition::factory()
        ->forOrganization($org)
        ->create([
            'enabled_tools' => ['App\\Ai\\Tools\\NonExistentTool'],
        ]);

    $runner = new AgentRunner($registry);
    $runner->forDefinition($agent)
        ->withUser($user)
        ->withOrganization($org);

    $tools = $runner->resolveTools();

    expect($tools)->toBeEmpty();
});

// ── KnowledgeSearchTool Injection ───────────────────────────────────────

it('injects KnowledgeSearchTool when agent has indexed knowledge files', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $registry = app(ModuleToolRegistry::class);

    $agent = AgentDefinition::factory()
        ->forOrganization($org)
        ->create([
            'enabled_tools' => [],
        ]);

    AgentKnowledgeFile::query()->withoutGlobalScopes()->create([
        'agent_definition_id' => $agent->id,
        'organization_id' => $org->id,
        'filename' => 'guide.pdf',
        'status' => 'indexed',
        'chunk_count' => 5,
    ]);

    $runner = new AgentRunner($registry);
    $runner->forDefinition($agent)
        ->withUser($user)
        ->withOrganization($org);

    $tools = $runner->resolveTools();

    $toolClasses = array_map(fn (object $tool): string => $tool::class, $tools);

    expect($toolClasses)->toContain(KnowledgeSearchTool::class);
});

it('does not inject KnowledgeSearchTool when agent has no knowledge files', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $registry = app(ModuleToolRegistry::class);

    $agent = AgentDefinition::factory()
        ->forOrganization($org)
        ->create([
            'enabled_tools' => [],
        ]);

    $runner = new AgentRunner($registry);
    $runner->forDefinition($agent)
        ->withUser($user)
        ->withOrganization($org);

    $tools = $runner->resolveTools();

    $toolClasses = array_map(fn (object $tool): string => $tool::class, $tools);

    expect($toolClasses)->not->toContain(KnowledgeSearchTool::class);
});

it('does not inject KnowledgeSearchTool when knowledge files are not indexed', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $registry = app(ModuleToolRegistry::class);

    $agent = AgentDefinition::factory()
        ->forOrganization($org)
        ->create([
            'enabled_tools' => [],
        ]);

    AgentKnowledgeFile::query()->withoutGlobalScopes()->create([
        'agent_definition_id' => $agent->id,
        'organization_id' => $org->id,
        'filename' => 'pending.pdf',
        'status' => 'pending',
    ]);

    AgentKnowledgeFile::query()->withoutGlobalScopes()->create([
        'agent_definition_id' => $agent->id,
        'organization_id' => $org->id,
        'filename' => 'failed.pdf',
        'status' => 'failed',
        'error_message' => 'Processing failed',
    ]);

    $runner = new AgentRunner($registry);
    $runner->forDefinition($agent)
        ->withUser($user)
        ->withOrganization($org);

    $tools = $runner->resolveTools();

    $toolClasses = array_map(fn (object $tool): string => $tool::class, $tools);

    expect($toolClasses)->not->toContain(KnowledgeSearchTool::class);
});

// ── Agent Building ──────────────────────────────────────────────────────

it('builds an OrgScopedAgent with custom prompt and tools', function (): void {
    $org = Organization::factory()->create(['name' => 'Test Org']);
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $registry = app(ModuleToolRegistry::class);
    $registry->registerBaseTool(SemanticSearchTool::class);

    $agent = AgentDefinition::factory()
        ->forOrganization($org)
        ->create([
            'system_prompt' => 'You are an agent for {{org_name}}.',
            'enabled_tools' => [SemanticSearchTool::class],
        ]);

    $runner = new AgentRunner($registry);
    $builtAgent = $runner
        ->forDefinition($agent)
        ->withUser($user)
        ->withOrganization($org)
        ->withContext(['page' => 'dashboard'])
        ->buildAgent();

    expect($builtAgent)->toBeInstanceOf(App\Ai\Agents\OrgScopedAgent::class)
        ->and($builtAgent->instructions())->toContain('Test Org')
        ->and($builtAgent->instructions())->toContain('dashboard')
        ->and($builtAgent->instructions())->not->toContain('{{org_name}}');
});

it('throws RuntimeException when definition is not set', function (): void {
    $runner = app(AgentRunner::class);

    $runner->buildAgent();
})->throws(RuntimeException::class, 'AgentDefinition is required');

it('throws RuntimeException when user is not set', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);

    $agent = AgentDefinition::factory()
        ->forOrganization($org)
        ->create();

    $runner = app(AgentRunner::class);
    $runner->forDefinition($agent)->buildAgent();
})->throws(RuntimeException::class, 'User is required');

// ── Organization Resolution ─────────────────────────────────────────────

it('resolves organization from definition when not explicitly set', function (): void {
    $org = Organization::factory()->create(['name' => 'Auto Org']);
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $registry = app(ModuleToolRegistry::class);

    $agent = AgentDefinition::factory()
        ->forOrganization($org)
        ->create([
            'system_prompt' => 'Hello from {{org_name}}.',
            'enabled_tools' => [],
        ]);

    // Load the organization relationship
    $agent->load('organization');

    $runner = new AgentRunner($registry);
    $builtAgent = $runner
        ->forDefinition($agent)
        ->withUser($user)
        ->buildAgent();

    expect($builtAgent->instructions())->toContain('Auto Org');
});
