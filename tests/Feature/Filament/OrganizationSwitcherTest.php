<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;
use App\Services\TenantContext;
use App\Settings\SetupWizardSettings;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);

    $settings = resolve(SetupWizardSettings::class);
    $settings->setup_completed = true;
    $settings->save();

    Config::set('tenancy.enabled', true);
});

it('shows organization switcher when admin has multiple organizations', function (): void {
    /** @var TestCase $this */
    $orgA = Organization::factory()->create(['name' => 'Org Alpha']);
    $orgB = Organization::factory()->create(['name' => 'Org Beta']);

    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'admin-multi@filament-test.example',
        'password' => Hash::make('password'),
    ]);
    $user->assignRole('admin');
    $user->organizations()->sync([
        $orgA->id => ['is_default' => true],
        $orgB->id => ['is_default' => false],
    ]);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertOk();
    $response->assertSeeHtml('Org Alpha');
    $response->assertSeeHtml('Org Beta');
});

it('switches organization and redirects back with success', function (): void {
    /** @var TestCase $this */
    $orgA = Organization::factory()->create(['name' => 'Org Alpha']);
    $orgB = Organization::factory()->create(['name' => 'Org Beta']);

    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'admin-switch@filament-test.example',
        'password' => Hash::make('password'),
    ]);
    $user->assignRole('admin');
    $user->organizations()->sync([
        $orgA->id => ['is_default' => true],
        $orgB->id => ['is_default' => false],
    ]);

    TenantContext::set($orgA);

    $response = $this->actingAs($user)
        ->from('/admin')
        ->post(route('organizations.switch'), [
            'organization_id' => $orgB->id,
            '_token' => csrf_token(),
        ]);

    $response->assertRedirect('/admin');
    $response->assertSessionHas('status');
    $response->assertSessionHas('filament_org_switch_message');
});
