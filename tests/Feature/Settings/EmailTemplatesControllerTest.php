<?php

declare(strict_types=1);

use App\Actions\CreateOrganizationAction;
use App\Services\Organization\OrganizationRoleService;
use App\Services\TenantContext;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use MartinPetricko\LaravelDatabaseMail\Models\MailTemplate;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->user = createTestUser();
    $this->organization = resolve(CreateOrganizationAction::class)->handle($this->user, 'Test Org');
    resolve(OrganizationRoleService::class)->syncRolePermissions($this->organization);
    resolve(PermissionRegistrar::class)->forgetCachedPermissions();

    TenantContext::set($this->organization);
    setPermissionsTeamId(null);

    $this->withoutMiddleware(App\Http\Middleware\HandleInertiaRequests::class);
    $this->actingAs($this->user);

    // Ensure a default template exists for the first registered event
    $firstEvent = collect(config('database-mail.events', []))->first();
    $this->firstEvent = $firstEvent;
    $this->eventBasename = class_basename($firstEvent);

    if ($firstEvent && ! MailTemplate::query()->where('event', $firstEvent)->whereNull('organization_id')->where('is_default', true)->exists()) {
        MailTemplate::query()->create([
            'event' => $firstEvent,
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body' => '<p>Test body</p>',
            'recipients' => ['user'],
            'attachments' => [],
            'is_active' => true,
            'is_default' => true,
            'organization_id' => null,
        ]);
    }

    $this->defaultTemplate = MailTemplate::query()
        ->where('event', $firstEvent)
        ->whereNull('organization_id')
        ->where('is_default', true)
        ->first();
});

afterEach(function (): void {
    TenantContext::flush();
    setPermissionsTeamId(0);
});

it('renders the email templates index page with registered events', function (): void {
    $response = $this->get(route('settings.email-templates.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/email-templates/index')
            ->has('templates')
        );
});

it('renders the edit page with template subject, body, and variables', function (): void {
    $response = $this->get(route('settings.email-templates.edit', $this->eventBasename));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/email-templates/edit')
            ->has('subject')
            ->has('body')
            ->has('variables')
        );
});

it('creates an org-scoped copy on first edit without modifying the default', function (): void {
    $originalSubject = $this->defaultTemplate->subject;

    $response = $this->put(route('settings.email-templates.update', $this->eventBasename), [
        'subject' => 'Custom Org Subject',
        'body' => '<p>Custom org body</p>',
    ]);

    $response->assertRedirect(route('settings.email-templates.index'))
        ->assertSessionHas('success');

    // Org-scoped template created
    $this->assertDatabaseHas('mail_templates', [
        'organization_id' => $this->organization->id,
        'event' => $this->firstEvent,
        'subject' => 'Custom Org Subject',
        'is_default' => false,
    ]);

    // Default template unchanged
    $this->assertDatabaseHas('mail_templates', [
        'organization_id' => null,
        'event' => $this->firstEvent,
        'subject' => $originalSubject,
        'is_default' => true,
    ]);
});

it('updates an existing org template in place', function (): void {
    // Create an existing org template
    MailTemplate::query()->create([
        'organization_id' => $this->organization->id,
        'event' => $this->firstEvent,
        'name' => $this->defaultTemplate->name,
        'subject' => 'Old Org Subject',
        'body' => '<p>Old body</p>',
        'recipients' => $this->defaultTemplate->recipients,
        'attachments' => [],
        'is_active' => true,
        'is_default' => false,
    ]);

    $response = $this->put(route('settings.email-templates.update', $this->eventBasename), [
        'subject' => 'Updated Org Subject',
        'body' => '<p>Updated body</p>',
    ]);

    $response->assertRedirect(route('settings.email-templates.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('mail_templates', [
        'organization_id' => $this->organization->id,
        'event' => $this->firstEvent,
        'subject' => 'Updated Org Subject',
    ]);

    // Only one org template exists (updated in place)
    $count = MailTemplate::query()
        ->where('event', $this->firstEvent)
        ->where('organization_id', $this->organization->id)
        ->count();

    expect($count)->toBe(1);
});

it('resets by deleting the org template and leaving the default intact', function (): void {
    // Create an org template to reset
    MailTemplate::query()->create([
        'organization_id' => $this->organization->id,
        'event' => $this->firstEvent,
        'name' => $this->defaultTemplate->name,
        'subject' => 'Org Custom Subject',
        'body' => '<p>Org custom body</p>',
        'recipients' => $this->defaultTemplate->recipients,
        'attachments' => [],
        'is_active' => true,
        'is_default' => false,
    ]);

    $response = $this->delete(route('settings.email-templates.reset', $this->eventBasename));

    $response->assertRedirect(route('settings.email-templates.index'))
        ->assertSessionHas('success');

    // Org template deleted
    $this->assertDatabaseMissing('mail_templates', [
        'organization_id' => $this->organization->id,
        'event' => $this->firstEvent,
    ]);

    // Default template still exists
    $this->assertDatabaseHas('mail_templates', [
        'organization_id' => null,
        'event' => $this->firstEvent,
        'is_default' => true,
    ]);
});

it('returns a JSON preview with rendered subject and body', function (): void {
    $response = $this->postJson(route('settings.email-templates.preview', $this->eventBasename));

    $response->assertOk()
        ->assertJsonStructure(['subject', 'body']);
});

it('redirects unauthenticated users to login', function (): void {
    TenantContext::flush();
    auth()->logout();

    $response = $this->get(route('settings.email-templates.index'));

    $response->assertRedirect(route('login'));
});
