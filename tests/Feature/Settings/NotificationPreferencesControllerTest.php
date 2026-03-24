<?php

declare(strict_types=1);

use App\Models\User;

it('rejects unauthenticated requests to notification preferences', function (): void {
    $response = $this->get(route('settings.notifications.show'));

    $response->assertRedirect(route('login'));
});

it('renders the notification preferences page for authenticated user', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $response = $this->actingAs($user)->get(route('settings.notifications.show'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/notifications')
            ->has('preferences')
        );
});

it('rejects notification update with invalid data', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $response = $this->actingAs($user)->patch(route('settings.notifications.update'), [
        'preferences' => 'not-an-array',
    ]);

    $response->assertSessionHasErrors(['preferences']);
});

it('rejects notification update with invalid preference key', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $response = $this->actingAs($user)->patch(route('settings.notifications.update'), [
        'preferences' => [
            ['key' => 'nonexistent_type', 'via_database' => true, 'via_email' => false],
        ],
    ]);

    $response->assertSessionHasErrors(['preferences.0.key']);
});

it('updates notification preferences with valid data', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $response = $this->actingAs($user)->patch(route('settings.notifications.update'), [
        'preferences' => [
            ['key' => 'invoice_paid', 'via_database' => true, 'via_email' => false],
            ['key' => 'org_invitation', 'via_database' => false, 'via_email' => true],
        ],
    ]);

    $response->assertRedirect()
        ->assertSessionHas('success');

    expect($user->notificationPreferences()->where('notification_type', 'invoice_paid')->first())
        ->via_database->toBeTrue()
        ->via_email->toBeFalse();
});
