<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

it('renders reset password page', function (): void {
    $response = $this->fromRoute('home')
        ->get(route('password.reset', ['token' => 'fake-token']));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('user-password/create')
            ->has('email')
            ->has('token'));
});

it('may reset password', function (): void {
    Event::fake([PasswordReset::class]);

    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $token = Password::createToken($user);

    $response = $this->fromRoute('password.reset', ['token' => $token])
        ->post(route('password.store'), [
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'token' => $token,
        ]);

    $response->assertRedirectToRoute('login')
        ->assertSessionHas('status');

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();

    Event::assertDispatched(PasswordReset::class);
});

it('fails with invalid token', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $response = $this->fromRoute('password.reset', ['token' => 'invalid-token'])
        ->post(route('password.store'), [
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'token' => 'invalid-token',
        ]);

    $response->assertRedirect(route('password.reset', ['token' => 'invalid-token']))
        ->assertSessionHasErrors('email');
});

it('fails with non-existent email', function (): void {
    $response = $this->fromRoute('password.reset', ['token' => 'fake-token'])
        ->post(route('password.store'), [
            'email' => 'nonexistent@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'token' => 'fake-token',
        ]);

    $response->assertRedirect(route('password.reset', ['token' => 'fake-token']))
        ->assertSessionHasErrors('email');
});

it('requires email', function (): void {
    $response = $this->fromRoute('password.reset', ['token' => 'fake-token'])
        ->post(route('password.store'), [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'token' => 'fake-token',
        ]);

    $response->assertRedirect(route('password.reset', ['token' => 'fake-token']))
        ->assertSessionHasErrors('email');
});

it('requires password', function (): void {
    $response = $this->fromRoute('password.reset', ['token' => 'fake-token'])
        ->post(route('password.store'), [
            'email' => 'test@example.com',
            'token' => 'fake-token',
        ]);

    $response->assertRedirect(route('password.reset', ['token' => 'fake-token']))
        ->assertSessionHasErrors('password');
});

it('requires password confirmation', function (): void {
    $response = $this->fromRoute('password.reset', ['token' => 'fake-token'])
        ->post(route('password.store'), [
            'email' => 'test@example.com',
            'password' => 'new-password',
            'token' => 'fake-token',
        ]);

    $response->assertRedirect(route('password.reset', ['token' => 'fake-token']))
        ->assertSessionHasErrors('password');
});

it('requires matching password confirmation', function (): void {
    $response = $this->fromRoute('password.reset', ['token' => 'fake-token'])
        ->post(route('password.store'), [
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
            'token' => 'fake-token',
        ]);

    $response->assertRedirect(route('password.reset', ['token' => 'fake-token']))
        ->assertSessionHasErrors('password');
});

it('renders edit password page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->get(route('password.edit'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page->component('user-password/edit'));
});

it('may update password', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user)
        ->fromRoute('password.edit')
        ->put(route('password.update'), [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertRedirectToRoute('password.edit');

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

it('requires current password to update', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('password.edit')
        ->put(route('password.update'), [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertRedirectToRoute('password.edit')
        ->assertSessionHasErrors('current_password');
});

it('requires correct current password to update', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user)
        ->fromRoute('password.edit')
        ->put(route('password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response->assertRedirectToRoute('password.edit')
        ->assertSessionHasErrors('current_password');
});

it('requires new password to update', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user)
        ->fromRoute('password.edit')
        ->put(route('password.update'), [
            'current_password' => 'old-password',
        ]);

    $response->assertRedirectToRoute('password.edit')
        ->assertSessionHasErrors('password');
});

it('redirects authenticated users away from reset password', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->get(route('password.reset', ['token' => 'fake-token']));

    $response->assertRedirectToRoute('dashboard');
});
