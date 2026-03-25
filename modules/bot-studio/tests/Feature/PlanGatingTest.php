<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Services\TenantContext;
use App\Settings\BotStudioSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Modules\Billing\Models\Plan;
use Modules\BotStudio\Models\AgentDefinition;
use Modules\BotStudio\Models\AgentKnowledgeFile;

/*
|--------------------------------------------------------------------------
| Bot Studio: Plan-Gating, Agent Count Limits, Knowledge Size Enforcement
|--------------------------------------------------------------------------
*/

afterEach(function (): void {
    TenantContext::flush();
});

// ── Agent Count Limit ─────────────────────────────────────────────────────

it('blocks agent creation when user is at the basic plan limit', function (): void {
    $settings = app(BotStudioSettings::class);
    $settings->max_agents_basic = 2;
    $settings->max_agents_pro = 0;
    $settings->save();

    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    // Create agents up to the limit
    AgentDefinition::factory()->count(2)->forOrganization($org)->create(['is_template' => false]);

    $response = $this->withoutMiddleware()
        ->post(route('bot-studio.store'), [
            'name' => 'Agent Over Limit',
            'system_prompt' => 'You are over the limit.',
        ]);

    $response->assertSessionHasErrors('limit');
});

it('allows agent creation when user is under the basic plan limit', function (): void {
    $settings = app(BotStudioSettings::class);
    $settings->max_agents_basic = 3;
    $settings->max_agents_pro = 0;
    $settings->save();

    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    // Create one agent — still under the limit of 3
    AgentDefinition::factory()->forOrganization($org)->create(['is_template' => false]);

    $response = $this->withoutMiddleware()
        ->post(route('bot-studio.store'), [
            'name' => 'Agent Under Limit',
            'system_prompt' => 'You are under the limit.',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
});

it('allows unlimited agents on the pro plan (max_agents_pro = 0)', function (): void {
    $settings = app(BotStudioSettings::class);
    $settings->max_agents_basic = 2;
    $settings->max_agents_pro = 0;
    $settings->save();

    $plan = Plan::factory()->create(['slug' => 'pro']);
    $org = Organization::factory()->create();
    $org->newPlanSubscription('main', $plan);
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    // Create agents well over the basic limit — pro should allow all
    AgentDefinition::factory()->count(5)->forOrganization($org)->create(['is_template' => false]);

    $response = $this->withoutMiddleware()
        ->post(route('bot-studio.store'), [
            'name' => 'Pro Agent',
            'system_prompt' => 'Pro plan unlimited.',
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
});

// ── Knowledge File Size Enforcement ──────────────────────────────────────

it('rejects a knowledge file that exceeds the per-file size limit', function (): void {
    Queue::fake();
    Storage::fake('local');

    $settings = app(BotStudioSettings::class);
    $settings->max_knowledge_file_size_mb = 1;
    $settings->max_knowledge_total_mb = 100;
    $settings->save();

    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $agent = AgentDefinition::factory()->forOrganization($org)->create(['is_template' => false]);

    // Create a fake file larger than 1 MB (1.5 MB)
    $oversizedFile = UploadedFile::fake()->create('large.txt', 1536, 'text/plain');

    $response = $this->withoutMiddleware()
        ->postJson(route('bot-studio.knowledge.store', $agent->slug), [
            'file' => $oversizedFile,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('file');
});

it('allows a knowledge file within the per-file size limit', function (): void {
    Queue::fake();
    Storage::fake('local');

    $settings = app(BotStudioSettings::class);
    $settings->max_knowledge_file_size_mb = 10;
    $settings->max_knowledge_total_mb = 100;
    $settings->save();

    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $agent = AgentDefinition::factory()->forOrganization($org)->create(['is_template' => false]);

    // Create a fake file under 10 MB (500 KB)
    $smallFile = UploadedFile::fake()->create('small.txt', 500, 'text/plain');

    $response = $this->withoutMiddleware()
        ->postJson(route('bot-studio.knowledge.store', $agent->slug), [
            'file' => $smallFile,
        ]);

    $response->assertStatus(201);
});

it('rejects a knowledge file that would push the total over the limit', function (): void {
    Queue::fake();
    Storage::fake('local');

    $settings = app(BotStudioSettings::class);
    $settings->max_knowledge_file_size_mb = 50;
    $settings->max_knowledge_total_mb = 10;
    $settings->save();

    $org = Organization::factory()->create();
    TenantContext::set($org);
    $user = createTestUser();
    $this->actingAs($user);

    $agent = AgentDefinition::factory()->forOrganization($org)->create(['is_template' => false]);

    // Add existing knowledge file that uses 9 MB
    AgentKnowledgeFile::query()->withoutGlobalScopes()->create([
        'agent_definition_id' => $agent->id,
        'organization_id' => $org->id,
        'filename' => 'existing.txt',
        'mime_type' => 'text/plain',
        'status' => 'indexed',
        'file_size' => 9 * 1024 * 1024,
    ]);

    // Try to upload a 2 MB file — total would be 11 MB, over the 10 MB limit
    $file = UploadedFile::fake()->create('new.txt', 2048, 'text/plain');

    $response = $this->withoutMiddleware()
        ->postJson(route('bot-studio.knowledge.store', $agent->slug), [
            'file' => $file,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('file');
});
