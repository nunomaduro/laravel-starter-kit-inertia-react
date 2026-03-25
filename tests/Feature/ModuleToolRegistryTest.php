<?php

declare(strict_types=1);

use App\Ai\Contracts\ModuleAiTool;
use App\Ai\Tools\SemanticSearchTool;
use App\Models\Organization;
use App\Modules\Contracts\ProvidesAITools;
use App\Support\ModuleToolRegistry;
use Laravel\Ai\Contracts\Tool;
use Laravel\Pennant\Feature;

beforeEach(function (): void {
    $this->registry = new ModuleToolRegistry;
});

it('returns only base tools when no module tools are registered', function (): void {
    $this->registry->registerBaseTool(SemanticSearchTool::class);

    $org = Organization::factory()->create();
    $tools = $this->registry->getToolsForOrganization($org);

    expect($tools)->toHaveCount(1)
        ->and($tools[0])->toBeInstanceOf(SemanticSearchTool::class);
});

it('returns empty array when no tools are registered', function (): void {
    $org = Organization::factory()->create();
    $tools = $this->registry->getToolsForOrganization($org);

    expect($tools)->toBeEmpty();
});

it('collects tools from providers implementing ProvidesAITools', function (): void {
    $fakeToolClass = get_class(new class implements Tool
    {
        public function description(): string
        {
            return 'Fake tool';
        }

        public function schema(Illuminate\Contracts\JsonSchema\JsonSchema $schema): array
        {
            return [];
        }

        public function handle(Laravel\Ai\Tools\Request $request): string
        {
            return 'ok';
        }
    });

    $provider = new class($fakeToolClass) implements ProvidesAITools
    {
        public function __construct(private readonly string $toolClass) {}

        public function registerAiTools(): array
        {
            return [$this->toolClass];
        }
    };

    app()->tag([get_class($provider)], 'module.ai_tools');
    app()->instance(get_class($provider), $provider);

    $registry = new ModuleToolRegistry;
    $org = Organization::factory()->create();

    $tools = $registry->getToolsForOrganization($org);

    expect($tools)->toHaveCount(1)
        ->and($tools[0])->toBeInstanceOf($fakeToolClass);
});

it('filters module tools by feature flag', function (): void {
    // Create a module tool that requires a feature flag
    $toolClass = get_class(new class implements ModuleAiTool, Tool
    {
        public static function requiredFeature(): ?string
        {
            return 'crm';
        }

        public function description(): string
        {
            return 'CRM tool';
        }

        public function schema(Illuminate\Contracts\JsonSchema\JsonSchema $schema): array
        {
            return [];
        }

        public function handle(Laravel\Ai\Tools\Request $request): string
        {
            return 'ok';
        }
    });

    $provider = new class($toolClass) implements ProvidesAITools
    {
        public function __construct(private readonly string $toolClass) {}

        public function registerAiTools(): array
        {
            return [$this->toolClass];
        }
    };

    app()->tag([get_class($provider)], 'module.ai_tools');
    app()->instance(get_class($provider), $provider);

    $org = Organization::factory()->create();

    // Feature is inactive — tool should be excluded
    Feature::for($org)->deactivate('crm');

    $registry = new ModuleToolRegistry;
    $tools = $registry->getToolsForOrganization($org);

    expect($tools)->toBeEmpty();
});

it('includes module tools when feature flag is active', function (): void {
    $toolClass = get_class(new class implements ModuleAiTool, Tool
    {
        public static function requiredFeature(): ?string
        {
            return 'crm';
        }

        public function description(): string
        {
            return 'CRM tool';
        }

        public function schema(Illuminate\Contracts\JsonSchema\JsonSchema $schema): array
        {
            return [];
        }

        public function handle(Laravel\Ai\Tools\Request $request): string
        {
            return 'ok';
        }
    });

    $provider = new class($toolClass) implements ProvidesAITools
    {
        public function __construct(private readonly string $toolClass) {}

        public function registerAiTools(): array
        {
            return [$this->toolClass];
        }
    };

    app()->tag([get_class($provider)], 'module.ai_tools');
    app()->instance(get_class($provider), $provider);

    $org = Organization::factory()->create();

    Feature::for($org)->activate('crm');

    $registry = new ModuleToolRegistry;
    $tools = $registry->getToolsForOrganization($org);

    expect($tools)->toHaveCount(1);
});

it('always includes base tools alongside filtered module tools', function (): void {
    $this->registry->registerBaseTool(SemanticSearchTool::class);

    $gatedToolClass = get_class(new class implements ModuleAiTool, Tool
    {
        public static function requiredFeature(): ?string
        {
            return 'disabled-feature';
        }

        public function description(): string
        {
            return 'Gated tool';
        }

        public function schema(Illuminate\Contracts\JsonSchema\JsonSchema $schema): array
        {
            return [];
        }

        public function handle(Laravel\Ai\Tools\Request $request): string
        {
            return 'ok';
        }
    });

    $provider = new class($gatedToolClass) implements ProvidesAITools
    {
        public function __construct(private readonly string $toolClass) {}

        public function registerAiTools(): array
        {
            return [$this->toolClass];
        }
    };

    app()->tag([get_class($provider)], 'module.ai_tools');
    app()->instance(get_class($provider), $provider);

    $org = Organization::factory()->create();
    Feature::for($org)->deactivate('disabled-feature');

    $tools = $this->registry->getToolsForOrganization($org);

    expect($tools)->toHaveCount(1)
        ->and($tools[0])->toBeInstanceOf(SemanticSearchTool::class);
});

it('caches tools per organization per request', function (): void {
    $this->registry->registerBaseTool(SemanticSearchTool::class);

    $org = Organization::factory()->create();

    $first = $this->registry->getToolsForOrganization($org);
    $second = $this->registry->getToolsForOrganization($org);

    expect($first)->toBe($second);
});

it('does not duplicate base tools on repeated registration', function (): void {
    $this->registry->registerBaseTool(SemanticSearchTool::class);
    $this->registry->registerBaseTool(SemanticSearchTool::class);

    expect($this->registry->getBaseTools())->toHaveCount(1);
});
