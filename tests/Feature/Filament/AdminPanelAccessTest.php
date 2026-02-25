<?php

declare(strict_types=1);

use App\Models\User;
use App\Settings\SetupWizardSettings;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

beforeEach(function (): void {
    Artisan::call('db:seed', ['--class' => RolesAndPermissionsSeeder::class, '--no-interaction' => true]);

    $settings = resolve(SetupWizardSettings::class);
    $settings->setup_completed = true;
    $settings->save();
});

it('allows admin to access panel', function (): void {
    $test = $this;
    assert($test instanceof TestCase);
    actsAsFilamentAdmin($test);

    $response = $test->get('/admin');

    $response->assertOk();
});

it('allows super-admin to access panel', function (): void {
    $test = $this;
    assert($test instanceof TestCase);
    actsAsFilamentAdmin($test, 'super-admin');

    $response = $test->get('/admin');

    $response->assertOk();
});

it('allows admin to open users list', function (): void {
    $test = $this;
    assert($test instanceof TestCase);
    actsAsFilamentAdmin($test);

    $response = $test->get('/admin/users');

    $response->assertOk();
});

it('denies user without access admin panel permission', function (): void {
    /** @var TestCase $test */
    $test = $this;
    $user = User::withoutEvents(fn (): User => User::factory()->withoutTwoFactor()->create([
        'email' => 'regular@test.example',
        'password' => Hash::make('password'),
    ]));
    $user->assignRole('user');

    $response = $test->actingAs($user)->get('/admin');

    $response->assertForbidden();
});

it('redirects guest to login when visiting admin', function (): void {
    /** @var TestCase $test */
    $test = $this;
    $response = $test->get('/admin');

    $response->assertRedirect('/admin/login');
});
