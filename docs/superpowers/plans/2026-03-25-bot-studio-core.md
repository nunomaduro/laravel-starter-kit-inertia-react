# Bot Studio Core Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a Bot Studio module that lets users create custom AI agents via a guided wizard, upload knowledge files for RAG, and chat with their agents — all org-scoped and plan-gated.

**Architecture:** Module at `modules/bot-studio/` following existing module patterns (CRM as reference). AgentRunner service configures OrgScopedAgent from DB-stored definitions at runtime. Knowledge pipeline extracts text from files, chunks, and embeds into existing `model_embeddings` table. 5 vertical slices: data layer → builder UI → knowledge pipeline → templates → plan-gating.

**Tech Stack:** Laravel 13, Inertia.js v2 + React 19, Tailwind CSS v4, Pest 4, laravel/ai, pgvector, Spatie Media Library, spatie/pdf-to-text, phpoffice/phpword, spatie/laravel-settings, Laravel Pennant

**Spec:** `docs/superpowers/specs/2026-03-25-bot-studio-core-design.md`

---

## File Structure

### Module Scaffold

```
modules/bot-studio/
├── module.json
├── composer.json
├── src/
│   ├── Providers/
│   │   └── BotStudioModuleServiceProvider.php
│   ├── Models/
│   │   ├── AgentDefinition.php
│   │   └── AgentKnowledgeFile.php
│   ├── Features/
│   │   └── BotStudioFeature.php
│   ├── Services/
│   │   ├── AgentRunner.php
│   │   ├── KnowledgeProcessor.php
│   │   ├── DocumentChunker.php
│   │   └── PromptWizardService.php
│   ├── Ai/
│   │   └── Tools/
│   │       └── KnowledgeSearchTool.php
│   ├── Jobs/
│   │   └── ProcessKnowledgeFileJob.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AgentDefinitionController.php
│   │   │   ├── AgentChatController.php
│   │   │   └── KnowledgeFileController.php
│   │   └── Requests/
│   │       ├── StoreAgentDefinitionRequest.php
│   │       └── UpdateAgentDefinitionRequest.php
│   ├── Contracts/
│   │   └── ProvidesAgentTemplates.php
│   └── Policies/
│       └── AgentDefinitionPolicy.php
├── database/
│   ├── migrations/
│   │   ├── 2026_03_25_100000_create_agent_definitions_table.php
│   │   ├── 2026_03_25_100001_create_agent_knowledge_files_table.php
│   │   └── 2026_03_25_100002_add_agent_definition_id_to_agent_conversations_table.php
│   └── factories/
│       ├── AgentDefinitionFactory.php
│       └── AgentKnowledgeFileFactory.php
├── routes/
│   └── web.php
├── resources/js/pages/
│   └── bot-studio/
│       ├── index.tsx
│       ├── create.tsx
│       ├── edit.tsx
│       └── templates.tsx
├── tests/
│   ├── Feature/
│   │   ├── AgentDefinitionTest.php
│   │   ├── AgentRunnerTest.php
│   │   ├── KnowledgePipelineTest.php
│   │   ├── AgentChatTest.php
│   │   └── TemplatesTest.php
│   └── Unit/
│       ├── DocumentChunkerTest.php
│       └── PromptWizardServiceTest.php
└── database/seeders/
    └── BotStudioTemplateSeeder.php
```

### App-Level Files (outside module)

```
app/Settings/BotStudioSettings.php                          (create)
database/settings/2026_03_25_100000_create_bot_studio_settings.php  (create)
app/Filament/System/Pages/ManageBotStudio.php               (create)
app/Providers/SettingsOverlayServiceProvider.php             (modify — add OVERLAY_MAP entry)
config/modules.php                                           (modify — add bot-studio)
config/feature-flags.php                                     (modify — add bot_studio metadata)
```

---

## Slice 1: Data Layer + AgentRunner (~2 days)

### Task 1: Module Scaffold

**Files:**
- Create: `modules/bot-studio/module.json`
- Create: `modules/bot-studio/composer.json`
- Create: `modules/bot-studio/src/Providers/BotStudioModuleServiceProvider.php`
- Create: `modules/bot-studio/src/Features/BotStudioFeature.php`
- Modify: `config/modules.php`
- Modify: `composer.json` (add PSR-4 autoload path)

**Context:**
- Reference: `modules/crm/` for exact patterns. Read `modules/crm/module.json`, `modules/crm/composer.json`, `modules/crm/src/Providers/CrmModuleServiceProvider.php`.
- The module.json needs: `name`, `label`, `description`, `provider` (FQCN).
- composer.json needs PSR-4 autoload for `Modules\\BotStudio\\` namespace.
- Service provider extends `ModuleProvider`, implements `ProvidesAIContext`.
- Feature class uses `WithFeatureResolver` trait with `$defaultValue = true`.
- Add `'bot-studio' => true` to `config/modules.php`.
- Add PSR-4 mapping to root `composer.json` autoload and run `composer dump-autoload`.

- [ ] **Step 1: Create module.json**

```json
{
    "name": "bot-studio",
    "label": "Bot Studio",
    "description": "Create, customize, and deploy custom AI agents",
    "provider": "Modules\\BotStudio\\Providers\\BotStudioModuleServiceProvider"
}
```

- [ ] **Step 2: Create composer.json for the module**

Follow the CRM module's composer.json pattern exactly.

- [ ] **Step 3: Create BotStudioFeature**

```php
<?php

declare(strict_types=1);

namespace Modules\BotStudio\Features;

use Stephenjude\FilamentFeatureFlag\Traits\WithFeatureResolver;

final class BotStudioFeature
{
    use WithFeatureResolver;

    public bool $defaultValue = true;
}
```

- [ ] **Step 4: Create BotStudioModuleServiceProvider**

Follow `CrmModuleServiceProvider` exactly:
- `manifest()` returns ModuleManifest with name 'Bot Studio', models [AgentDefinition, AgentKnowledgeFile], pages, navigation (label: 'Bot Studio', route: 'bot-studio.index', icon: 'bot', group: 'AI')
- `featureClass()` returns `BotStudioFeature::class`
- Implement `ProvidesAIContext` with `systemPrompt()`, `tools()`, `searchableModels()`
- `bootModule()` registers policies

- [ ] **Step 5: Register module in config and autoload**

Add to `config/modules.php` and root `composer.json`. Run `composer dump-autoload`.

- [ ] **Step 6: Commit**

```bash
git add modules/bot-studio/module.json modules/bot-studio/composer.json modules/bot-studio/src/ config/modules.php composer.json
git commit -m "feat(bot-studio): scaffold module with service provider and feature flag"
```

---

### Task 2: Database Migrations + Models

**Files:**
- Create: `modules/bot-studio/database/migrations/2026_03_25_100000_create_agent_definitions_table.php`
- Create: `modules/bot-studio/database/migrations/2026_03_25_100001_create_agent_knowledge_files_table.php`
- Create: `modules/bot-studio/database/migrations/2026_03_25_100002_add_agent_definition_id_to_agent_conversations_table.php`
- Create: `modules/bot-studio/src/Models/AgentDefinition.php`
- Create: `modules/bot-studio/src/Models/AgentKnowledgeFile.php`
- Create: `modules/bot-studio/database/factories/AgentDefinitionFactory.php`
- Create: `modules/bot-studio/database/factories/AgentKnowledgeFileFactory.php`
- Create: `modules/bot-studio/src/Policies/AgentDefinitionPolicy.php`
- Create: `modules/bot-studio/tests/Feature/AgentDefinitionTest.php`

**Context:**
- See spec Section 2 for exact column definitions.
- `AgentDefinition` uses `HasVisibility` trait (not `BelongsToOrganization`). Read `app/Models/Concerns/HasVisibility.php` to understand required columns (organization_id, visibility) and available scopes.
- `AgentKnowledgeFile` uses `BelongsToOrganization` trait. Registers a `deleting` event to bulk-delete associated `model_embeddings`.
- `AgentDefinition` uses `InteractsWithMedia` from Spatie for avatar. Uses `SoftDeletes`. Uses `HasFactory`.
- Factory needs states: `template()`, `published()`, `withKnowledge()`.
- @skill: `pest-testing`, `taylor-otwell-style`, `visibility-sharing`.

- [ ] **Step 1: Write model tests**

Create `modules/bot-studio/tests/Feature/AgentDefinitionTest.php`:

Tests:
- Can create an agent definition with all fields
- Slug is auto-generated from name
- HasVisibility scopes work (private, organization)
- Knowledge files relationship loads
- Soft delete works
- Factory states produce valid models
- AgentKnowledgeFile deleting event clears model_embeddings

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter=AgentDefinitionTest`

- [ ] **Step 3: Create migrations**

**agent_definitions**: All columns from spec Section 2. Use `HasVisibility` compatible columns (the trait expects `organization_id` and `visibility` as an enum cast). Add `softDeletes()`. Unique on `(organization_id, slug)`. Index on `is_template`.

**agent_knowledge_files**: All columns from spec. FK to `agent_definitions` with cascadeOnDelete. FK to `organizations` with cascadeOnDelete. Index on `agent_definition_id`.

**agent_conversations modification**: Add nullable `agent_definition_id` FK to `agent_conversations`.

- [ ] **Step 4: Create AgentDefinition model**

```php
<?php

declare(strict_types=1);

namespace Modules\BotStudio\Models;

use App\Models\Concerns\HasVisibility;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class AgentDefinition extends Model implements HasMedia
{
    use HasFactory;
    use HasVisibility;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'organization_id', 'created_by', 'name', 'slug', 'description',
        'avatar_path', 'system_prompt', 'model', 'temperature', 'max_tokens',
        'enabled_tools', 'knowledge_config', 'conversation_starters',
        'wizard_answers', 'is_published', 'is_featured', 'is_template',
        'total_conversations', 'total_messages',
    ];

    protected function casts(): array
    {
        return [
            'enabled_tools' => 'array',
            'knowledge_config' => 'array',
            'conversation_starters' => 'array',
            'wizard_answers' => 'array',
            'temperature' => 'decimal:1',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'is_template' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function knowledgeFiles(): HasMany
    {
        return $this->hasMany(AgentKnowledgeFile::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }
}
```

- [ ] **Step 5: Create AgentKnowledgeFile model**

With `BelongsToOrganization` trait. Register `deleting` event that bulk-deletes from `model_embeddings` where `embeddable_type = self::class` and `embeddable_id = $this->id`.

- [ ] **Step 6: Create factories**

`AgentDefinitionFactory` with states: `template()`, `published()`, `forOrganization($org)`.
`AgentKnowledgeFileFactory` with states: `indexed()`, `processing()`, `failed()`.

- [ ] **Step 7: Create AgentDefinitionPolicy**

Follow existing policies in `app/Policies/`. Check `HasVisibility::isViewableBy()` for view. Check creator or admin for update/delete.

- [ ] **Step 8: Run migrations and tests**

Run: `php artisan migrate`
Run: `php artisan test --compact --filter=AgentDefinitionTest`

- [ ] **Step 9: Run Pint, docs:sync, commit**

```bash
vendor/bin/pint --dirty --format agent
php artisan docs:sync
git add -A
git commit -m "feat(bot-studio): add agent_definitions and agent_knowledge_files with models and tests"
```

---

### Task 3: AgentRunner Service

**Files:**
- Create: `modules/bot-studio/src/Services/AgentRunner.php`
- Create: `modules/bot-studio/src/Ai/Tools/KnowledgeSearchTool.php`
- Create: `modules/bot-studio/tests/Feature/AgentRunnerTest.php`

**Context:**
- AgentRunner configures an `OrgScopedAgent` from an `AgentDefinition`. No new agent class.
- See spec Section 4 for the fluent API.
- `OrgScopedAgent` is at `app/Ai/Agents/OrgScopedAgent.php`. It has `make()`, `withContext()`, `instructions()`, `tools()`.
- AgentRunner needs to: load definition, resolve prompt variables (`{{org_name}}` etc.), filter tools through `ModuleToolRegistry`, inject `KnowledgeSearchTool` if agent has indexed files, configure OrgScopedAgent, return stream.
- `KnowledgeSearchTool` wraps `SemanticSearchService` scoped to specific knowledge file IDs. Check existing tools in `app/Ai/Tools/` for the laravel/ai tool pattern.
- @skill: `pest-testing`, `ai-sdk-development`, `taylor-otwell-style`.

- [ ] **Step 1: Write tests**

Create `modules/bot-studio/tests/Feature/AgentRunnerTest.php`:

Tests (mock AI SDK to avoid real API calls):
- AgentRunner loads definition and resolves system prompt variables
- AgentRunner filters tools from enabled_tools against ModuleToolRegistry
- AgentRunner injects KnowledgeSearchTool when agent has indexed knowledge files
- AgentRunner does not inject KnowledgeSearchTool when no knowledge files
- AgentRunner passes page context to OrgScopedAgent

- [ ] **Step 2: Run tests to verify they fail**

- [ ] **Step 3: Create KnowledgeSearchTool**

A laravel/ai tool that wraps `SemanticSearchService`. Constructed with an array of knowledge file IDs. Scopes searches to `embeddable_type = AgentKnowledgeFile` and `embeddable_id IN (...)`. Returns results with source filenames as citations.

Follow the pattern of existing tools in `app/Ai/Tools/` (check `UsersIndexAiTool.php` or `SemanticSearchTool.php`).

- [ ] **Step 4: Create AgentRunner**

```php
<?php

declare(strict_types=1);

namespace Modules\BotStudio\Services;

use App\Ai\Agents\OrgScopedAgent;
use App\Models\Organization;
use App\Models\User;
use App\Support\ModuleToolRegistry;
use App\Support\TenantContext;
use Illuminate\Support\Str;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Ai\Tools\KnowledgeSearchTool;

final class AgentRunner
{
    private AgentDefinition $definition;
    private User $user;
    private array $context = [];

    public function forDefinition(AgentDefinition $definition): self
    {
        $this->definition = $definition;
        return $this;
    }

    public function withUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function withContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    public function stream(string $prompt): mixed
    {
        $org = Organization::findOrFail($this->definition->organization_id ?? TenantContext::id());
        $registry = app(ModuleToolRegistry::class);

        // Resolve system prompt variables
        $systemPrompt = $this->resolvePromptVariables(
            $this->definition->system_prompt,
            $org,
            $this->user,
        );

        // Filter tools: only those in enabled_tools that the org still has access to
        $allTools = $registry->getToolsForOrganization($org);
        $enabledToolClasses = $this->definition->enabled_tools ?? [];
        $tools = array_filter($allTools, fn (object $tool) =>
            in_array($tool::class, $enabledToolClasses, true)
        );

        // Add KnowledgeSearchTool if agent has indexed knowledge files
        $indexedFileIds = $this->definition->knowledgeFiles()
            ->where('status', 'indexed')
            ->pluck('id')
            ->all();

        if ($indexedFileIds !== []) {
            $tools[] = new KnowledgeSearchTool($indexedFileIds, $org->id);
        }

        // Configure and stream
        $agent = OrgScopedAgent::makeWith($org, $this->user, $registry)
            ->withCustomPrompt($systemPrompt)
            ->withCustomTools($tools)
            ->withContext($this->context);

        return $agent->stream($prompt);
    }

    private function resolvePromptVariables(string $prompt, Organization $org, User $user): string
    {
        return Str::replace(
            ['{{org_name}}', '{{user_name}}', '{{current_date}}'],
            [$org->name, $user->name, now()->toDateString()],
            $prompt,
        );
    }
}
```

Note: `OrgScopedAgent` may need `makeWith()`, `withCustomPrompt()`, and `withCustomTools()` methods added. Check the current implementation and extend if needed. If these methods don't exist, add them to `OrgScopedAgent` as part of this task — they should override the default instructions and tool list.

- [ ] **Step 5: Run tests**

Run: `php artisan test --compact --filter=AgentRunnerTest`

- [ ] **Step 6: Run Pint, docs:sync, commit**

```bash
vendor/bin/pint --dirty --format agent
php artisan docs:sync
git add -A
git commit -m "feat(bot-studio): add AgentRunner service and KnowledgeSearchTool"
```

---

## Slice 2: Builder UI + Preview (~3-4 days)

### Task 4: Routes + Controllers (Backend)

**Files:**
- Create: `modules/bot-studio/routes/web.php`
- Create: `modules/bot-studio/src/Http/Controllers/AgentDefinitionController.php`
- Create: `modules/bot-studio/src/Http/Controllers/AgentChatController.php`
- Create: `modules/bot-studio/src/Http/Requests/StoreAgentDefinitionRequest.php`
- Create: `modules/bot-studio/src/Http/Requests/UpdateAgentDefinitionRequest.php`

**Context:**
- See spec Section 8 for all routes. All under `tenant` + `auth` middleware.
- Feature gating middleware added later in Slice 5.
- `AgentDefinitionController`: CRUD + `templates()` + `duplicate()`. Follow existing controllers.
- `AgentChatController`: `stream()` uses `AgentRunner`, `preview()` is ephemeral (no save, no billing), `conversations()` lists conversations for an agent.
- Form requests validate: name (required, max:100), description, system_prompt (required), model, temperature, enabled_tools (array), conversation_starters (array), wizard_answers (array), visibility.
- Use Inertia `render()` for pages, return JSON for API-like endpoints.
- @skill: `pest-testing`, `inertia-react-development`, `taylor-otwell-style`.

- [ ] **Step 1: Write route/controller tests**

Create `modules/bot-studio/tests/Feature/AgentDefinitionTest.php` (extend existing or create new file for HTTP tests):

Tests:
- GET `/bot-studio` returns agent list page
- GET `/bot-studio/create` returns create page
- POST `/bot-studio` creates agent, validates required fields, enforces slug uniqueness per org
- GET `/bot-studio/{slug}/edit` returns edit page with definition data
- PUT `/bot-studio/{slug}` updates agent
- DELETE `/bot-studio/{slug}` soft deletes
- POST `/bot-studio/{slug}/duplicate` creates a copy
- GET `/bot-studio/templates` returns templates page

- [ ] **Step 2: Create routes file**

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\BotStudio\Http\Controllers\AgentDefinitionController;
use Modules\BotStudio\Http\Controllers\AgentChatController;
use Modules\BotStudio\Http\Controllers\KnowledgeFileController;

Route::middleware(['auth', 'tenant'])->prefix('bot-studio')->name('bot-studio.')->group(function (): void {
    // Agent CRUD
    Route::get('/', [AgentDefinitionController::class, 'index'])->name('index');
    Route::get('/templates', [AgentDefinitionController::class, 'templates'])->name('templates');
    Route::get('/create', [AgentDefinitionController::class, 'create'])->name('create');
    Route::post('/', [AgentDefinitionController::class, 'store'])->name('store');
    Route::get('/{agentDefinition:slug}/edit', [AgentDefinitionController::class, 'edit'])->name('edit');
    Route::put('/{agentDefinition:slug}', [AgentDefinitionController::class, 'update'])->name('update');
    Route::delete('/{agentDefinition:slug}', [AgentDefinitionController::class, 'destroy'])->name('destroy');
    Route::post('/{agentDefinition:slug}/duplicate', [AgentDefinitionController::class, 'duplicate'])->name('duplicate');

    // Agent Chat
    Route::post('/{agentDefinition:slug}/chat', [AgentChatController::class, 'stream'])->name('chat');
    Route::post('/{agentDefinition:slug}/preview', [AgentChatController::class, 'preview'])->name('preview');
    Route::get('/{agentDefinition:slug}/conversations', [AgentChatController::class, 'conversations'])->name('conversations');

    // Knowledge Files
    Route::post('/{agentDefinition:slug}/knowledge', [KnowledgeFileController::class, 'store'])->name('knowledge.store');
    Route::delete('/{agentDefinition:slug}/knowledge/{knowledgeFile}', [KnowledgeFileController::class, 'destroy'])->name('knowledge.destroy');
    Route::post('/{agentDefinition:slug}/knowledge/{knowledgeFile}/retry', [KnowledgeFileController::class, 'retry'])->name('knowledge.retry');
});
```

- [ ] **Step 3: Create form requests**

`StoreAgentDefinitionRequest`: validates name, system_prompt (required), model, temperature, enabled_tools, conversation_starters, wizard_answers, visibility.

`UpdateAgentDefinitionRequest`: same fields, all optional.

- [ ] **Step 4: Create AgentDefinitionController**

Methods: `index`, `create`, `edit`, `store`, `update`, `destroy`, `templates`, `duplicate`.
- `index`: query user's agents (HasVisibility scope), render `bot-studio/index`
- `create`: render `bot-studio/create` with available tools + models
- `edit`: render `bot-studio/edit` with definition, knowledge files, available tools + models
- `store`: create from validated data, set org_id + created_by, redirect to edit
- `update`: update validated fields
- `destroy`: soft delete, redirect to index
- `templates`: query `is_template = true` definitions, render `bot-studio/templates`
- `duplicate`: copy definition to user's org, redirect to edit

- [ ] **Step 5: Create AgentChatController**

Methods: `stream`, `preview`, `conversations`.
- `stream`: uses AgentRunner, saves to agent_conversations with agent_definition_id. Follow existing ChatController streaming pattern (NDJSON).
- `preview`: uses AgentRunner but does NOT save conversation or deduct credits. Returns same NDJSON format.
- `conversations`: query agent_conversations where agent_definition_id = definition.id, scoped to user + org.

- [ ] **Step 6: Run tests**

- [ ] **Step 7: Run Pint, docs:sync, commit**

```bash
git commit -m "feat(bot-studio): add routes, controllers, and form requests"
```

---

### Task 5: PromptWizardService

**Files:**
- Create: `modules/bot-studio/src/Services/PromptWizardService.php`
- Create: `modules/bot-studio/tests/Unit/PromptWizardServiceTest.php`

**Context:**
- Takes structured wizard answers (role, tone, expertise, restrictions) and generates a system prompt.
- Pure PHP service, no AI API calls — template-based generation.
- Wizard answers stored in `wizard_answers` JSON column so they can be re-loaded.

- [ ] **Step 1: Write unit tests**

Tests:
- Generates prompt from full wizard answers (role, tone, expertise, restrictions)
- Handles missing optional fields gracefully
- Includes variable placeholders (`{{org_name}}`, `{{user_name}}`) in generated prompt
- Different tones produce different prompt styles

- [ ] **Step 2: Implement PromptWizardService**

```php
<?php

declare(strict_types=1);

namespace Modules\BotStudio\Services;

final class PromptWizardService
{
    /**
     * Generate a system prompt from structured wizard answers.
     *
     * @param array{role?: string, tone?: string, expertise?: string, restrictions?: string} $answers
     */
    public function generate(array $answers): string
    {
        $parts = [];

        if (! empty($answers['role'])) {
            $parts[] = "You are a {$this->toneAdjective($answers['tone'] ?? 'professional')} {$answers['role']} for {{org_name}}.";
        }

        if (! empty($answers['expertise'])) {
            $parts[] = "Your expertise covers: {$answers['expertise']}.";
        }

        $parts[] = 'When greeting users, address them as {{user_name}}.';

        if (! empty($answers['restrictions'])) {
            $parts[] = "Important restrictions:\n" . collect(explode('.', $answers['restrictions']))
                ->map(fn (string $r) => trim($r))
                ->filter()
                ->map(fn (string $r) => "- {$r}")
                ->implode("\n");
        }

        return implode("\n\n", $parts);
    }

    private function toneAdjective(string $tone): string
    {
        return match ($tone) {
            'friendly' => 'friendly and approachable',
            'casual' => 'casual and conversational',
            'technical' => 'precise and technical',
            'empathetic' => 'empathetic and understanding',
            default => 'professional',
        };
    }
}
```

- [ ] **Step 3: Run tests, Pint, commit**

```bash
git commit -m "feat(bot-studio): add PromptWizardService for guided prompt generation"
```

---

### Task 6: Builder React Pages

**Files:**
- Create: `modules/bot-studio/resources/js/pages/bot-studio/index.tsx`
- Create: `modules/bot-studio/resources/js/pages/bot-studio/create.tsx`
- Create: `modules/bot-studio/resources/js/pages/bot-studio/edit.tsx`
- Create: `modules/bot-studio/resources/js/pages/bot-studio/templates.tsx`

**Context:**
- Follow existing Inertia React page patterns. Check `resources/js/pages/` for layout, breadcrumbs, Head component usage.
- Use `<Form>` from `@inertiajs/react` for form submission.
- Use shadcn components from `resources/js/components/ui/` (Button, Input, Textarea, Select, Switch, Dialog, Tabs, etc.).
- Follow DESIGN.md: dark-first, JetBrains Mono headings, IBM Plex Sans body, muted teal accent, no card shadows.
- See the mockups in `.superpowers/brainstorm/` for visual reference.
- @skill: `inertia-react-development`, `tailwindcss-development`, `frontend-design`.

- [ ] **Step 1: Create index.tsx — Agent List**

Grid layout of agent cards:
- Agent card: avatar, name, tool/knowledge count, description, visibility badge, chat count
- "X of Y agents used" counter (from props: `currentCount`, `maxCount`)
- "Create Agent" button → `/bot-studio/create`
- "Browse Templates" button → `/bot-studio/templates`
- Empty state when no agents

Props from controller: `agents`, `currentCount`, `maxCount`.

- [ ] **Step 2: Create create.tsx — Wizard**

5-step wizard with progress indicator:

**Step 1 (Identity):** Name input (auto-slug preview), description textarea, avatar upload (Spatie), visibility selector
**Step 2 (Persona):** Role input, tone tag picker (Professional/Friendly/Casual/Technical/Empathetic), expertise input, restrictions input. Generated prompt preview below with "Edit manually" link.
**Step 3 (Tools):** Multi-select checkboxes for available tools (from props: `availableTools` array with name + description). Greyed-out tools for unavailable plan features.
**Step 4 (Starters):** Dynamic list — add/remove conversation starter messages.
**Step 5 (Review):** Summary of all choices. Model dropdown (from props: `allowedModels`). Temperature slider. "Create Agent" submit button.

State managed with React `useState`. On submit, POST all data including `wizard_answers` (the structured answers from steps 1-4) to `/bot-studio`.

- [ ] **Step 3: Create edit.tsx — Split-Screen Builder**

Two-panel layout (50/50 split):

**Left panel — Tabbed editor:**
- Tabs: Prompt, Tools, Knowledge, Settings, Starters
- **Prompt tab:** System prompt textarea with variable insertion buttons (`{{org_name}}` etc.), model dropdown, temperature slider. "Re-run Wizard" button opens wizard modal pre-filled with `wizard_answers`.
- **Tools tab:** Same tool multi-select as wizard step 3.
- **Knowledge tab:** (Implemented in Slice 3, show empty state for now: "Knowledge files coming soon")
- **Settings tab:** Visibility selector, max_tokens input.
- **Starters tab:** Same dynamic list as wizard step 4.

**Right panel — Live Preview:**
- "Live Preview" indicator (green dot)
- Agent avatar + name
- Conversation starters as clickable chips
- Chat input — sends to `/bot-studio/{slug}/preview` endpoint
- Streaming response display (same NDJSON parsing as global chat)
- "Clear chat" button resets messages
- "Preview messages are ephemeral" note

Auto-save on field changes (debounced PUT to `/bot-studio/{slug}`), or explicit Save button.

- [ ] **Step 4: Create templates.tsx — Template Browser**

Grid of template cards:
- Template card: name, description, tool/model info
- "Use this template" button → POST `/bot-studio/{slug}/duplicate`
- Grouped by source: "Starter Kit" templates, module-registered templates

Props from controller: `templates` (grouped).

- [ ] **Step 5: Build and verify**

Run: `npm run build`
Fix any TypeScript/build errors.

- [ ] **Step 6: Commit**

```bash
git add modules/bot-studio/resources/
git commit -m "feat(bot-studio): add builder UI with wizard, split-screen editor, and templates page"
```

---

## Slice 3: Knowledge Pipeline (~3 days)

### Task 7: DocumentChunker + Text Extraction

**Files:**
- Create: `modules/bot-studio/src/Services/DocumentChunker.php`
- Create: `modules/bot-studio/tests/Unit/DocumentChunkerTest.php`

**Context:**
- Splits text into ~500 token chunks with 50-token overlap.
- Token counting: simple approximation (1 token ≈ 0.75 words, so 500 tokens ≈ 375 words).
- Returns array of `['text' => string, 'chunk_index' => int]`.
- Pure PHP, no external dependencies for chunking itself.
- Text extraction handled in `ProcessKnowledgeFileJob`, not here.

- [ ] **Step 1: Write unit tests**

Create `modules/bot-studio/tests/Unit/DocumentChunkerTest.php`:

Tests:
- Short text (under 500 tokens) returns single chunk
- Long text splits into multiple chunks
- Overlap of 50 tokens between consecutive chunks
- Empty text returns empty array
- Chunk indices are sequential (0, 1, 2, ...)
- Chunks split at sentence boundaries when possible

- [ ] **Step 2: Implement DocumentChunker**

```php
<?php

declare(strict_types=1);

namespace Modules\BotStudio\Services;

final class DocumentChunker
{
    private int $chunkSize;
    private int $overlap;

    public function __construct(int $chunkSize = 500, int $overlap = 50)
    {
        $this->chunkSize = $chunkSize;
        $this->overlap = $overlap;
    }

    /**
     * Split text into overlapping chunks.
     *
     * @return array<int, array{text: string, chunk_index: int}>
     */
    public function chunk(string $text): array
    {
        $text = trim($text);

        if ($text === '') {
            return [];
        }

        $words = preg_split('/\s+/', $text);
        $wordsPerChunk = (int) round($this->chunkSize * 0.75); // ~0.75 words per token
        $overlapWords = (int) round($this->overlap * 0.75);

        if (count($words) <= $wordsPerChunk) {
            return [['text' => $text, 'chunk_index' => 0]];
        }

        $chunks = [];
        $index = 0;
        $position = 0;

        while ($position < count($words)) {
            $chunkWords = array_slice($words, $position, $wordsPerChunk);
            $chunkText = implode(' ', $chunkWords);

            $chunks[] = ['text' => $chunkText, 'chunk_index' => $index];

            $position += $wordsPerChunk - $overlapWords;
            $index++;
        }

        return $chunks;
    }
}
```

- [ ] **Step 3: Run tests, Pint, commit**

```bash
git commit -m "feat(bot-studio): add DocumentChunker with token-based splitting and overlap"
```

---

### Task 8: ProcessKnowledgeFileJob + KnowledgeProcessor

**Files:**
- Create: `modules/bot-studio/src/Jobs/ProcessKnowledgeFileJob.php`
- Create: `modules/bot-studio/src/Services/KnowledgeProcessor.php`
- Create: `modules/bot-studio/src/Http/Controllers/KnowledgeFileController.php`
- Create: `modules/bot-studio/tests/Feature/KnowledgePipelineTest.php`

**Context:**
- `KnowledgeProcessor` handles upload flow: validates file, stores via Spatie Media, creates `AgentKnowledgeFile` record, dispatches job.
- `ProcessKnowledgeFileJob` extracts text → chunks via `DocumentChunker` → embeds via `Laravel\Ai\Embeddings` → stores in `model_embeddings`.
- Text extraction: PDF via `Spatie\PdfToText\Pdf` (requires poppler-utils), DOCX via `PhpOffice\PhpWord\IOFactory`, TXT/CSV/MD read directly.
- `KnowledgeFileController`: store (upload), destroy (delete file + embeddings), retry (re-dispatch job).
- Rate-limited via `spatie/laravel-rate-limited-job-middleware`.
- @skill: `pest-testing`, `ai-sdk-development`, `taylor-otwell-style`.

- [ ] **Step 1: Write tests**

Tests:
- KnowledgeProcessor validates file type (accept PDF/DOCX/TXT/CSV/MD, reject others)
- KnowledgeProcessor validates file size against BotStudioSettings
- ProcessKnowledgeFileJob extracts text from TXT file (simplest case)
- ProcessKnowledgeFileJob chunks and creates model_embeddings rows (mock Embeddings API)
- ProcessKnowledgeFileJob updates status to 'indexed' with chunk_count on success
- ProcessKnowledgeFileJob updates status to 'failed' with error_message on failure
- Deleting a knowledge file deletes associated model_embeddings
- Retry dispatches a new ProcessKnowledgeFileJob

- [ ] **Step 2: Create KnowledgeProcessor**

Service that handles upload and deletion:
- `upload(AgentDefinition $definition, UploadedFile $file): AgentKnowledgeFile` — validates, stores, creates record, dispatches job
- `delete(AgentKnowledgeFile $file): void` — deletes record (model event handles embeddings), removes media

- [ ] **Step 3: Create ProcessKnowledgeFileJob**

```php
<?php

declare(strict_types=1);

namespace Modules\BotStudio\Jobs;

use App\Models\ModelEmbedding;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Embeddings;
use Modules\BotStudio\Models\AgentKnowledgeFile;
use Modules\BotStudio\Services\DocumentChunker;
use Spatie\RateLimitedMiddleware\RateLimited;
use Throwable;

final class ProcessKnowledgeFileJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 300;

    public function __construct(
        private readonly AgentKnowledgeFile $knowledgeFile,
    ) {}

    /** @return array<int, object> */
    public function middleware(): array
    {
        return [
            (new RateLimited)->allow(30)->everySeconds(60)->releaseAfterSeconds(30),
        ];
    }

    public function handle(DocumentChunker $chunker): void
    {
        $this->knowledgeFile->update(['status' => 'processing']);

        // 1. Extract text based on mime type
        $text = $this->extractText();

        if (trim($text) === '') {
            $this->knowledgeFile->update([
                'status' => 'failed',
                'error_message' => 'No text could be extracted from this file.',
            ]);
            return;
        }

        // 2. Chunk the text
        $chunks = $chunker->chunk($text);

        // 3. Delete any existing embeddings for this file (re-processing)
        ModelEmbedding::where('embeddable_type', AgentKnowledgeFile::class)
            ->where('embeddable_id', $this->knowledgeFile->id)
            ->delete();

        // 4. Generate embeddings for each chunk and store
        foreach ($chunks as $chunk) {
            $response = Embeddings::for([$chunk['text']])->generate();
            $vector = $response->first();

            ModelEmbedding::create([
                'organization_id' => $this->knowledgeFile->organization_id,
                'embeddable_type' => AgentKnowledgeFile::class,
                'embeddable_id' => $this->knowledgeFile->id,
                'chunk_index' => $chunk['chunk_index'],
                'embedding' => $vector,
                'content_hash' => hash('sha256', $chunk['text']),
            ]);
        }

        // 5. Update status
        $this->knowledgeFile->update([
            'status' => 'indexed',
            'chunk_count' => count($chunks),
            'processed_at' => now(),
        ]);
    }

    private function extractText(): string
    {
        $media = $this->knowledgeFile->getFirstMedia('knowledge');
        if ($media === null) {
            return '';
        }

        $path = $media->getPath();
        $mime = $this->knowledgeFile->mime_type;

        return match (true) {
            str_contains($mime, 'pdf') => \Spatie\PdfToText\Pdf::getText($path),
            str_contains($mime, 'wordprocessingml') || str_contains($mime, 'msword') => $this->extractDocx($path),
            default => file_get_contents($path) ?: '',
        };
    }

    private function extractDocx(string $path): string
    {
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($path);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                }
            }
        }

        return $text;
    }

    public function failed(Throwable $exception): void
    {
        $this->knowledgeFile->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);

        Log::error('ProcessKnowledgeFileJob failed', [
            'knowledge_file_id' => $this->knowledgeFile->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

- [ ] **Step 4: Create KnowledgeFileController**

Methods: `store`, `destroy`, `retry`.
- `store`: validates file, calls `KnowledgeProcessor::upload()`, returns JSON with file record
- `destroy`: calls `KnowledgeProcessor::delete()`, returns 204
- `retry`: resets status to 'pending', dispatches `ProcessKnowledgeFileJob`

- [ ] **Step 5: Run tests**

Run: `php artisan test --compact --filter=KnowledgePipelineTest`

- [ ] **Step 6: Update edit.tsx Knowledge tab**

Replace the "coming soon" placeholder with the real Knowledge tab:
- Drag-and-drop upload zone
- File list with status badges (Queued/Processing/Indexed/Failed)
- Progress bars for processing files
- Retry button on failed files
- Delete with confirmation
- Storage usage indicator ("X files · Y MB of Z MB")
- Poll for status updates on processing files

- [ ] **Step 7: Run Pint, build, commit**

```bash
vendor/bin/pint --dirty --format agent
npm run build
git commit -m "feat(bot-studio): add knowledge pipeline with file upload, chunking, and embedding"
```

---

## Slice 4: Templates + Settings (~1-2 days)

### Task 9: BotStudioSettings + Filament Page

**Files:**
- Create: `app/Settings/BotStudioSettings.php`
- Create: `database/settings/2026_03_25_100000_create_bot_studio_settings.php`
- Create: `app/Filament/System/Pages/ManageBotStudio.php`
- Modify: `app/Providers/SettingsOverlayServiceProvider.php`

**Context:**
- Follow existing settings pattern. Read `app/Settings/AiSettings.php` as reference.
- Settings migration creates the settings group with default values.
- Add to `SettingsOverlayServiceProvider::OVERLAY_MAP` with `'orgOverridable' => true`.
- Filament page follows `ManageAi.php` pattern — SettingsPage extending `Filament\Pages\SettingsPage`.
- @skill: `taylor-otwell-style`.

- [ ] **Step 1: Create BotStudioSettings**

```php
<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class BotStudioSettings extends Settings
{
    public int $max_agents_basic = 3;
    public int $max_agents_pro = 0;
    public int $max_knowledge_file_size_mb = 10;
    public int $max_knowledge_total_mb = 100;
    public string $default_model = 'gpt-4o-mini';
    public array $allowed_models = ['gpt-4o-mini', 'gpt-4o', 'claude-sonnet-4-5', 'claude-haiku-4-5'];

    public static function group(): string
    {
        return 'bot-studio';
    }
}
```

- [ ] **Step 2: Create settings migration**

- [ ] **Step 3: Add to SettingsOverlayServiceProvider OVERLAY_MAP**

- [ ] **Step 4: Create Filament ManageBotStudio page**

- [ ] **Step 5: Run migration, Pint, commit**

```bash
git commit -m "feat(bot-studio): add BotStudioSettings with Filament system page"
```

---

### Task 10: Templates Seeder + ProvidesAgentTemplates

**Files:**
- Create: `modules/bot-studio/src/Contracts/ProvidesAgentTemplates.php`
- Create: `modules/bot-studio/database/seeders/BotStudioTemplateSeeder.php`
- Modify: `modules/bot-studio/src/Providers/BotStudioModuleServiceProvider.php`
- Create: `modules/bot-studio/tests/Feature/TemplatesTest.php`

**Context:**
- `ProvidesAgentTemplates` contract for modules to register templates.
- Service provider collects templates from all providers at boot (same pattern as `ModuleToolRegistry`).
- Seeder creates 4 starter templates (General Assistant, Customer Support, Data Analyst, Onboarding Guide) as `is_template = true`, `organization_id = null`.
- @skill: `pest-testing`, `taylor-otwell-style`.

- [ ] **Step 1: Write tests**

Tests:
- Templates page shows seeded templates
- "Use this template" (duplicate) creates a copy in user's org
- Copied agent has is_template=false and user's org_id
- Wizard answers are preserved in the copy

- [ ] **Step 2: Create ProvidesAgentTemplates contract**

```php
<?php

declare(strict_types=1);

namespace Modules\BotStudio\Contracts;

interface ProvidesAgentTemplates
{
    /** @return array<int, array{name: string, description: string, system_prompt: string, wizard_answers: array, conversation_starters: array, enabled_tools: array, model: string, temperature: float}> */
    public function agentTemplates(): array;
}
```

- [ ] **Step 3: Create BotStudioTemplateSeeder**

Seeds 4 templates with realistic system prompts, wizard answers, and conversation starters.

- [ ] **Step 4: Update service provider to collect module templates**

In `bootModule()`, iterate providers implementing `ProvidesAgentTemplates`, ensure their templates exist in DB.

- [ ] **Step 5: Run tests, seed, Pint, commit**

```bash
php artisan db:seed --class=Modules\\BotStudio\\Database\\Seeders\\BotStudioTemplateSeeder
git commit -m "feat(bot-studio): add template seeder and ProvidesAgentTemplates contract"
```

---

## Slice 5: Plan-Gating + Polish (~1-2 days)

### Task 11: Feature Flag + Enforcement

**Files:**
- Modify: `config/feature-flags.php`
- Modify: `modules/bot-studio/routes/web.php` (add feature middleware)
- Modify: `modules/bot-studio/src/Http/Controllers/AgentDefinitionController.php` (add count enforcement)
- Modify: `modules/bot-studio/src/Http/Controllers/KnowledgeFileController.php` (add size enforcement)

**Context:**
- Add `bot_studio` to `feature_metadata` in `config/feature-flags.php` with `plan_required`.
- Add feature middleware to routes: check if `BotStudioFeature` is active.
- In `store()`, check agent count against `BotStudioSettings` limit for the org's plan tier. Return 403 with message if at limit.
- In `KnowledgeFileController::store()`, check file size and total per agent against settings.
- @skill: `pennant-development`, `pest-testing`.

- [ ] **Step 1: Write enforcement tests**

Tests:
- Unauthenticated user cannot access bot-studio routes
- User without bot_studio feature gets 403
- User at agent limit gets 403 on create with upgrade message
- Knowledge file over size limit rejected
- Knowledge total over limit rejected

- [ ] **Step 2: Add feature flag config**

Add to `config/feature-flags.php`:
```php
'bot_studio' => [
    'label' => 'Bot Studio',
    'description' => 'Create custom AI agents',
    'delegate_to_orgs' => true,
    'plan_required' => 'pro',
],
```

- [ ] **Step 3: Add feature middleware to routes**

Update the route group in `web.php` to include feature check middleware.

- [ ] **Step 4: Add count enforcement in controller**

In `AgentDefinitionController::store()`, before creating:
```php
$settings = app(BotStudioSettings::class);
$currentCount = AgentDefinition::where('organization_id', TenantContext::id())
    ->where('is_template', false)
    ->count();
$maxAgents = /* determine from plan tier */;

if ($maxAgents > 0 && $currentCount >= $maxAgents) {
    return back()->withErrors(['limit' => 'You have reached your agent limit. Upgrade your plan for more.']);
}
```

- [ ] **Step 5: Add size enforcement in knowledge controller**

- [ ] **Step 6: Run all module tests**

Run: `php artisan test --compact --filter="modules/bot-studio"`

- [ ] **Step 7: Run Pint, docs:sync, commit**

```bash
git commit -m "feat(bot-studio): add plan-gating, agent count limits, and knowledge file size enforcement"
```

---

### Task 12: Final Integration + Full Test Suite

**Files:**
- All module files (verification pass)

- [ ] **Step 1: Run the full test suite**

Run: `php artisan test --compact`
Fix any failures caused by the new module.

- [ ] **Step 2: Run the build**

Run: `npm run build`
Fix any TypeScript/build errors.

- [ ] **Step 3: Run Pint on all module PHP files**

Run: `vendor/bin/pint modules/bot-studio/ --format agent`

- [ ] **Step 4: Run PHPStan if configured**

Run: `vendor/bin/phpstan analyse modules/bot-studio/src/` (if configured)

- [ ] **Step 5: Run docs:sync**

Run: `php artisan docs:sync`

- [ ] **Step 6: Final commit if needed**

```bash
git commit -m "chore(bot-studio): final integration fixes and test verification"
```

---

## Post-Implementation

After all 12 tasks are complete:

1. Full test suite: `php artisan test --compact`
2. Build: `npm run build`
3. Pint: `vendor/bin/pint --dirty --format agent`
4. Docs: `php artisan docs:sync`
5. Seed templates: `php artisan db:seed --class=Modules\\BotStudio\\Database\\Seeders\\BotStudioTemplateSeeder`
6. Verify: Visit `/bot-studio`, create an agent via wizard, test preview chat, upload a knowledge file

### What's Next

- **Spec B:** Marketplace (browse, install, ratings) — depends on this being done
- **Spec C:** Embed Widget (external deployment via `<script>` tag) — depends on this being done
