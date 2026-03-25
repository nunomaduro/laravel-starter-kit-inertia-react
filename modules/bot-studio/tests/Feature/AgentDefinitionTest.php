<?php

declare(strict_types=1);

use App\Enums\VisibilityEnum;
use App\Models\Organization;
use App\Services\TenantContext;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Models\AgentKnowledgeFile;

/*
|--------------------------------------------------------------------------
| Bot Studio: AgentDefinition & AgentKnowledgeFile Tests
|--------------------------------------------------------------------------
*/

// ── Creation ─────────────────────────────────────────────────────────────

it('can create an agent definition with all fields', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $agent = AgentDefinition::query()->withoutGlobalScopes()->create([
        'organization_id' => $org->id,
        'created_by' => $user->id,
        'name' => 'Support Bot',
        'slug' => 'support-bot',
        'description' => 'A helpful support agent',
        'system_prompt' => 'You are a helpful support agent.',
        'model' => 'gpt-4o-mini',
        'temperature' => 0.7,
        'max_tokens' => 4096,
        'enabled_tools' => ['web_search'],
        'knowledge_config' => ['strategy' => 'rag'],
        'conversation_starters' => ['Hello!', 'How can I help?'],
        'is_published' => true,
        'is_featured' => false,
        'is_template' => false,
    ]);

    expect($agent)->toBeInstanceOf(AgentDefinition::class)
        ->and($agent->name)->toBe('Support Bot')
        ->and($agent->slug)->toBe('support-bot')
        ->and($agent->organization_id)->toBe($org->id)
        ->and($agent->created_by)->toBe($user->id)
        ->and($agent->enabled_tools)->toBe(['web_search'])
        ->and($agent->knowledge_config)->toBe(['strategy' => 'rag'])
        ->and($agent->conversation_starters)->toBe(['Hello!', 'How can I help?'])
        ->and($agent->is_published)->toBeTrue()
        ->and($agent->is_template)->toBeFalse()
        ->and($agent->temperature)->toBe('0.7')
        ->and($agent->max_tokens)->toBe(4096);
});

// ── Slug Auto-Generation ─────────────────────────────────────────────────

it('auto-generates slug from name on creating', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $agent = AgentDefinition::create([
        'name' => 'My Cool Agent',
        'system_prompt' => 'You are cool.',
    ]);

    expect($agent->slug)->toBe('my-cool-agent');
});

it('does not overwrite slug if explicitly provided', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $agent = AgentDefinition::create([
        'name' => 'My Cool Agent',
        'slug' => 'custom-slug',
        'system_prompt' => 'You are cool.',
    ]);

    expect($agent->slug)->toBe('custom-slug');
});

// ── HasVisibility ────────────────────────────────────────────────────────

it('defaults visibility to organization when created within a tenant', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $agent = AgentDefinition::create([
        'name' => 'Org Agent',
        'system_prompt' => 'Org scoped.',
    ]);

    expect($agent->visibility)->toBe(VisibilityEnum::Organization)
        ->and($agent->organization_id)->toBe($org->id);
});

it('scopes agent definitions to current organization via visibility scope', function (): void {
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();

    // Create agents for both orgs without scopes
    AgentDefinition::query()->withoutGlobalScopes()->create([
        'organization_id' => $orgA->id,
        'name' => 'Agent A',
        'slug' => 'agent-a',
        'system_prompt' => 'Org A agent.',
        'visibility' => VisibilityEnum::Organization,
    ]);

    AgentDefinition::query()->withoutGlobalScopes()->create([
        'organization_id' => $orgB->id,
        'name' => 'Agent B',
        'slug' => 'agent-b',
        'system_prompt' => 'Org B agent.',
        'visibility' => VisibilityEnum::Organization,
    ]);

    TenantContext::set($orgA);

    $agents = AgentDefinition::all();

    expect($agents)->toHaveCount(1)
        ->and($agents->first()->name)->toBe('Agent A');
});

// ── Relationships ────────────────────────────────────────────────────────

it('loads knowledge files relationship', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $agent = AgentDefinition::create([
        'name' => 'Knowledge Bot',
        'system_prompt' => 'I know things.',
    ]);

    AgentKnowledgeFile::query()->withoutGlobalScopes()->create([
        'agent_definition_id' => $agent->id,
        'organization_id' => $org->id,
        'filename' => 'guide.pdf',
        'mime_type' => 'application/pdf',
        'status' => 'pending',
    ]);

    $agent->refresh();

    expect($agent->knowledgeFiles)->toHaveCount(1)
        ->and($agent->knowledgeFiles->first()->filename)->toBe('guide.pdf');
});

it('has a creator relationship', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $agent = AgentDefinition::create([
        'name' => 'Creator Test',
        'system_prompt' => 'Test.',
    ]);

    expect($agent->creator)->not->toBeNull()
        ->and($agent->creator->id)->toBe($user->id);
});

// ── Soft Delete ──────────────────────────────────────────────────────────

it('soft deletes agent definitions', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $agent = AgentDefinition::create([
        'name' => 'Deletable',
        'system_prompt' => 'Delete me.',
    ]);

    $agent->delete();

    expect(AgentDefinition::find($agent->id))->toBeNull()
        ->and(AgentDefinition::withTrashed()->find($agent->id))->not->toBeNull();
});

// ── Factory States ───────────────────────────────────────────────────────

it('factory template state produces valid template', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);

    $agent = AgentDefinition::factory()
        ->forOrganization($org)
        ->template()
        ->create();

    expect($agent->is_template)->toBeTrue()
        ->and($agent->is_published)->toBeTrue();
});

it('factory published state produces published agent', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);

    $agent = AgentDefinition::factory()
        ->forOrganization($org)
        ->published()
        ->create();

    expect($agent->is_published)->toBeTrue();
});

// ── AgentKnowledgeFile Deleting Event ────────────────────────────────────

it('clears model_embeddings when knowledge file is deleted', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $agent = AgentDefinition::create([
        'name' => 'Embedding Bot',
        'system_prompt' => 'Embeddings test.',
    ]);

    $file = AgentKnowledgeFile::query()->withoutGlobalScopes()->create([
        'agent_definition_id' => $agent->id,
        'organization_id' => $org->id,
        'filename' => 'doc.pdf',
        'status' => 'indexed',
    ]);

    // Insert fake embeddings if model_embeddings table exists (postgres only)
    if (Illuminate\Support\Facades\Schema::hasTable('model_embeddings')) {
        Illuminate\Support\Facades\DB::table('model_embeddings')->insert([
            'organization_id' => $org->id,
            'embeddable_type' => AgentKnowledgeFile::class,
            'embeddable_id' => $file->id,
            'chunk_index' => 0,
            'content_hash' => hash('sha256', 'test'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $file->delete();

        $remaining = Illuminate\Support\Facades\DB::table('model_embeddings')
            ->where('embeddable_type', AgentKnowledgeFile::class)
            ->where('embeddable_id', $file->id)
            ->count();

        expect($remaining)->toBe(0);
    } else {
        // On SQLite (CI), just verify the delete event fires without error
        $file->delete();
        expect(AgentKnowledgeFile::query()->withoutGlobalScopes()->find($file->id))->toBeNull();
    }
});

// ── Policy ───────────────────────────────────────────────────────────────

it('policy allows creator to view and edit', function (): void {
    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    // Attach user to org so belongsToOrganization check passes
    $user->organizations()->attach($org->id);

    $agent = AgentDefinition::create([
        'name' => 'Policy Test',
        'system_prompt' => 'Policy test.',
    ]);

    $policy = new Modules\BotStudio\Policies\AgentDefinitionPolicy;

    expect($policy->view($user, $agent))->toBeTrue()
        ->and($policy->update($user, $agent))->toBeTrue()
        ->and($policy->delete($user, $agent))->toBeTrue();
});

it('policy blocks non-member from viewing org-scoped agent', function (): void {
    $orgA = Organization::factory()->create();
    $orgB = Organization::factory()->create();

    $creator = createTestUser();
    $outsider = createTestUser();

    // Creator belongs to orgA
    $creator->organizations()->attach($orgA->id);
    // Outsider belongs to orgB only
    $outsider->organizations()->attach($orgB->id);

    TenantContext::set($orgA);
    $this->actingAs($creator);

    $agent = AgentDefinition::create([
        'name' => 'Private Agent',
        'system_prompt' => 'Private.',
    ]);

    TenantContext::set($orgB);

    $policy = new Modules\BotStudio\Policies\AgentDefinitionPolicy;

    expect($policy->view($outsider, $agent))->toBeFalse();
});
