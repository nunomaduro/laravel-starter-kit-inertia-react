<?php

declare(strict_types=1);

use App\Models\User;

it('renders auth pages inside the auth layout', function (): void {
    $page = visit(route('login'));

    $page->assertSee('Log in to your account')
        ->assertSee('Enter your email and password below to log in')
        ->assertSee('Sign in with a passkey')
        ->assertNoJavaScriptErrors();

    $page->click('Sign up')
        ->assertSee('Create an account')
        ->assertNoJavaScriptErrors();
});

it('renders settings pages inside the app and settings layouts', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user);

    $page = visit(route('user-profile.edit'));

    $page->assertSee('Settings')
        ->assertSee('Profile information')
        ->assertNoJavaScriptErrors();

    $page->click('Appearance')
        ->assertSee('Settings')
        ->assertSee('Appearance settings')
        ->assertNoJavaScriptErrors();
});

it('renders passkeys page', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user)->session(['auth.password_confirmed_at' => time()]);

    $page = visit(route('user-passkey.index'));

    $page->assertSee('Passkeys')
        ->assertSee('No passkeys yet')
        ->assertSee('Add passkey')
        ->assertNoJavaScriptErrors();
});

it('shows a toast after updating the profile', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user);

    $page = visit(route('user-profile.edit'));

    $page->fill('name', 'Taylor Otwell')
        ->click('Save')
        ->assertSee('Profile updated.')
        ->assertNoJavaScriptErrors();
});

it('may delete the account from the profile settings', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user);

    $page = visit(route('user-profile.edit'));

    $page->click('@delete-user-button')
        ->fill('password', 'password')
        ->click('@confirm-delete-user-button')
        ->assertPathIs('/')
        ->assertNoJavaScriptErrors();

    expect($user->fresh())->toBeNull();
});
