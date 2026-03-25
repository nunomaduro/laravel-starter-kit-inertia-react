<?php

declare(strict_types=1);

use App\Ai\Agents\OrgScopedAgent;
use App\Ai\Contracts\ContextAwareTool;
use App\Ai\Middleware\WithMemoryUnlessUnavailable;
use App\Ai\Tools\SemanticSearchTool;
use App\Models\Organization;
use App\Models\User;
use App\Support\ModuleToolRegistry;
use Laravel\Ai\Contracts\Tool;

it('resolves tools from ModuleToolRegistry for the current org', function (): void {
    $org = Organization::factory()->create();
    $user = User::factory()->create();

    $registry = new ModuleToolRegistry;
    $registry->registerBaseTool(SemanticSearchTool::class);

    $agent = new OrgScopedAgent($org, $user, $registry);

    $tools = iterator_to_array($agent->tools());

    expect($tools)->toHaveCount(1)
        ->and($tools[0])->toBeInstanceOf(SemanticSearchTool::class);
});

it('passes context to tools implementing ContextAwareTool', function (): void {
    $org = Organization::factory()->create();
    $user = User::factory()->create();

    $contextAwareTool = new class implements ContextAwareTool, Tool
    {
        public array $receivedContext = [];

        public function setContext(array $context): void
        {
            $this->receivedContext = $context;
        }

        public function description(): string
        {
            return 'Context-aware test tool';
        }

        public function schema(Illuminate\Contracts\JsonSchema\JsonSchema $schema): array
        {
            return [];
        }

        public function handle(Laravel\Ai\Tools\Request $request): string
        {
            return 'ok';
        }
    };

    // Register the anonymous tool class as a base tool in the registry
    $toolClass = get_class($contextAwareTool);
    app()->instance($toolClass, $contextAwareTool);

    $registry = new ModuleToolRegistry;
    $registry->registerBaseTool($toolClass);

    $pageContext = [
        'page' => 'contacts.show',
        'entity_type' => 'contact',
        'entity_id' => 42,
        'entity_name' => 'Acme Corp',
    ];

    $agent = new OrgScopedAgent($org, $user, $registry);
    $agent->withContext($pageContext);

    iterator_to_array($agent->tools());

    expect($contextAwareTool->receivedContext)->toBe($pageContext);
});

it('includes org name in instructions', function (): void {
    $org = Organization::factory()->create(['name' => 'Cogneiss Labs']);
    $user = User::factory()->create();
    $registry = new ModuleToolRegistry;

    $agent = new OrgScopedAgent($org, $user, $registry);

    expect($agent->instructions())->toContain('Cogneiss Labs');
});

it('includes page context in instructions when set', function (): void {
    $org = Organization::factory()->create(['name' => 'Test Org']);
    $user = User::factory()->create();
    $registry = new ModuleToolRegistry;

    $agent = new OrgScopedAgent($org, $user, $registry);
    $agent->withContext([
        'page' => 'projects.show',
        'entity_type' => 'project',
        'entity_id' => 7,
        'entity_name' => 'Website Redesign',
    ]);

    $instructions = $agent->instructions();

    expect($instructions)
        ->toContain('The user is currently on page: projects.show.')
        ->toContain('They are viewing a project named Website Redesign.');
});

it('omits page context from instructions when not set', function (): void {
    $org = Organization::factory()->create();
    $user = User::factory()->create();
    $registry = new ModuleToolRegistry;

    $agent = new OrgScopedAgent($org, $user, $registry);

    expect($agent->instructions())->not->toContain('currently on page');
});

it('returns WithMemoryUnlessUnavailable middleware with org and user context', function (): void {
    $org = Organization::factory()->create();
    $user = User::factory()->create();
    $registry = new ModuleToolRegistry;

    $agent = new OrgScopedAgent($org, $user, $registry);
    $middleware = $agent->middleware();

    expect($middleware)->toHaveCount(1)
        ->and($middleware[0])->toBeInstanceOf(WithMemoryUnlessUnavailable::class);
});

it('does not pass context to tools that do not implement ContextAwareTool', function (): void {
    $org = Organization::factory()->create();
    $user = User::factory()->create();

    $registry = new ModuleToolRegistry;
    $registry->registerBaseTool(SemanticSearchTool::class);

    $agent = new OrgScopedAgent($org, $user, $registry);
    $agent->withContext([
        'page' => 'dashboard',
        'entity_type' => 'widget',
        'entity_id' => 1,
        'entity_name' => 'Sales Chart',
    ]);

    // Should not throw — SemanticSearchTool does not implement ContextAwareTool
    $tools = iterator_to_array($agent->tools());

    expect($tools)->toHaveCount(1);
});
