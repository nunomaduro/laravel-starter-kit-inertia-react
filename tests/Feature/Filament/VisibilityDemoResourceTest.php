<?php

declare(strict_types=1);

use App\Enums\VisibilityEnum;
use App\Models\VisibilityDemo;
use App\Settings\SetupWizardSettings;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Tests\TestCase;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);

    $settings = resolve(SetupWizardSettings::class);
    $settings->setup_completed = true;
    $settings->save();

    actsAsFilamentAdmin($this, 'super-admin');
});

it('allows super-admin to open visibility demos create page', function (): void {
    /** @var TestCase $this */
    $response = $this->get('/system/visibility-demos/create');

    $response->assertOk();
});

it('creates visibility demo as global when share_to_all_orgs is true', function (): void {
    $record = VisibilityDemo::query()->create([
        'title' => 'Global demo item',
        'visibility' => VisibilityEnum::Organization,
        'organization_id' => 1,
    ]);

    $record->visibility = VisibilityEnum::Global;
    $record->organization_id = null;
    $record->save();

    $record->refresh();
    expect($record->visibility)->toBe(VisibilityEnum::Global)
        ->and($record->organization_id)->toBeNull()
        ->and($record->isGlobal())->toBeTrue();
});
