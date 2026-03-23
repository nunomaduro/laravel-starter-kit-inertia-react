<?php

declare(strict_types=1);

use App\Models\User;
use App\Settings\TenancySettings;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Artisan;

beforeEach(function (): void {
    Artisan::call('db:seed', ['--class' => RolesAndPermissionsSeeder::class, '--no-interaction' => true]);
});

it('persists auto_create_personal_org_for_admins and for_members when saving tenancy settings', function (): void {
    $tenancy = resolve(TenancySettings::class);
    $tenancy->auto_create_personal_org_for_admins = true;
    $tenancy->auto_create_personal_org_for_members = false;
    $tenancy->save();

    $tenancy->refresh();
    expect($tenancy->auto_create_personal_org_for_admins)->toBeTrue()
        ->and($tenancy->auto_create_personal_org_for_members)->toBeFalse();
});

it('creates personal org for new user when not invited as member and for_admins is true', function (): void {
    $tenancy = resolve(TenancySettings::class);
    $tenancy->auto_create_personal_org_for_admins = true;
    $tenancy->auto_create_personal_org_for_members = false;
    $tenancy->save();

    // Re-apply overlay so config reflects the saved settings and
    // allow the CreatePersonalOrganizationOnUserCreated listener to run
    // (Pest.php sets seed_in_progress=true by default to prevent cascading role issues).
    App\Providers\SettingsOverlayServiceProvider::applyOverlay();
    config()->set('tenancy.seed_in_progress', false);

    $user = User::withoutEvents(fn (): User => User::factory()->withoutTwoFactor()->create([
        'email' => 'newadmin@test.example',
    ]));

    event(new App\Events\User\UserCreated($user));

    $user->refresh();
    expect($user->organizations()->count())->toBe(1);
});
