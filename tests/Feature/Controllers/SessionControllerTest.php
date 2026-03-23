<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('renders login page', function (): void {
    $response = $this->fromRoute('home')
        ->get(route('login'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('session/create')
            ->has('canResetPassword')
            ->has('status'));
});

it('may create a session', function (): void {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

    $response->assertRedirectToRoute('dashboard');

    $this->assertAuthenticatedAs($user);
});

it('may create a session with remember me', function (): void {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => true,
        ]);

    $response->assertRedirectToRoute('dashboard');

    $this->assertAuthenticatedAs($user);
});

it('redirects to two-factor challenge when enabled', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
        'two_factor_secret' => encrypt('secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => now(),
    ]);

    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

    $response->assertRedirectToRoute('two-factor.login');

    $this->assertGuest();
});

it('fails with invalid credentials', function (): void {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

    $response->assertRedirectToRoute('login')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('requires email', function (): void {
    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'password' => 'password',
        ]);

    $response->assertRedirectToRoute('login')
        ->assertSessionHasErrors('email');
});

it('requires password', function (): void {
    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
        ]);

    $response->assertRedirectToRoute('login')
        ->assertSessionHasErrors('password');
});

it('may destroy a session', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->post(route('logout'));

    $response->assertRedirect('/');

    $this->assertGuest();
});

it('redirects authenticated users away from login', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->get(route('login'));

    $response->assertRedirectToRoute('dashboard');
});

it('throttles login attempts after too many failures', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Make 5 failed login attempts to trigger rate limiting
    for ($i = 0; $i < 5; $i++) {
        $this->fromRoute('login')
            ->post(route('login.store'), [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
    }

    // The 6th attempt should be throttled by the rate limiter middleware (429)
    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

    $response->assertStatus(429);
});

it('clears rate limit after successful login', function (): void {
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Make a few failed attempts
    for ($i = 0; $i < 3; $i++) {
        $this->fromRoute('login')
            ->post(route('login.store'), [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
    }

    // Successful login should clear the rate limit
    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

    $response->assertRedirectToRoute('dashboard');
    $this->assertAuthenticatedAs($user);
});

it('returns 429 when rate limit is reached on login', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Make 5 failed login attempts to exhaust the rate limiter
    for ($i = 0; $i < 5; $i++) {
        $this->fromRoute('login')
            ->post(route('login.store'), [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
    }

    // The 6th attempt should be blocked by the ThrottleRequests middleware
    $response = $this->fromRoute('login')
        ->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

    $response->assertStatus(429);
});
