<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;
use Modules\Announcements\Enums\AnnouncementLevel;
use Modules\Announcements\Enums\AnnouncementScope;
use Modules\Announcements\Models\Announcement;

test('unauthenticated user cannot access announcements table page', function (): void {
    $this->get(route('announcements.table'))
        ->assertRedirect();
});

test('authenticated user can access announcements table and receives tableData', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::withoutEvents(fn (): User => User::factory()->withoutTwoFactor()->create([
        'email' => 'admin@announcements-table-test.example',
        'password' => Hash::make('password'),
    ]));
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)
        ->get(route('announcements.table'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('announcements/table')
        ->has('tableData')
        ->has('tableData.data')
        ->has('tableData.columns')
        ->has('tableData.meta')
        ->has('searchableColumns')
        ->where('tableData.meta.total', fn ($total): bool => $total >= 0)
    );
});

test('authenticated user can reorder announcements', function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $user = User::withoutEvents(fn (): User => User::factory()->withoutTwoFactor()->create([
        'email' => 'admin@announcements-reorder.example',
        'password' => Hash::make('password'),
    ]));
    $user->assignRole('super-admin');

    $a1 = Announcement::query()->create([
        'title' => 'First',
        'body' => 'Body',
        'level' => AnnouncementLevel::Info,
        'scope' => AnnouncementScope::Global,
        'is_active' => true,
        'position' => 1,
    ]);
    $a2 = Announcement::query()->create([
        'title' => 'Second',
        'body' => 'Body',
        'level' => AnnouncementLevel::Info,
        'scope' => AnnouncementScope::Global,
        'is_active' => true,
        'position' => 2,
    ]);

    $response = $this->actingAs($user)
        ->patch(route('data-table.reorder', ['table' => 'announcements']), [
            'ids' => [$a2->id, $a1->id],
        ]);

    $response->assertOk();

    $a1->refresh();
    $a2->refresh();
    expect($a2->position)->toBe(0)
        ->and($a1->position)->toBe(1);
});
