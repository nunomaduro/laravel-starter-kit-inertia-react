<?php

declare(strict_types=1);

use App\Actions\GetRequiredTermsVersionsForUser;
use App\Actions\RecordTermsAcceptance;
use App\Enums\TermsType;
use App\Models\TermsVersion;
use App\Models\User;
use App\Models\UserTermsAcceptance;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function (): void {
    App\Services\TenantContext::forget();
});

test('guest can view legal terms and privacy pages', function (): void {
    get(route('legal.terms'))->assertOk();
    get(route('legal.privacy'))->assertOk();
});

test('required terms version redirects authenticated user to accept page', function (): void {
    TermsVersion::query()->create([
        'title' => 'Terms v1',
        'slug' => 'terms-v1',
        'body' => 'Body',
        'type' => TermsType::Terms,
        'effective_at' => now(),
        'is_required' => true,
    ]);

    $user = User::factory()->withoutTwoFactor()->create(['onboarding_completed' => true]);
    actingAs($user);

    $response = get(route('dashboard'));
    $response->assertRedirect();

    expect(str_contains($response->headers->get('Location') ?? '', 'terms/accept'))->toBeTrue();
});

test('user who accepted required version is not redirected', function (): void {
    $version = TermsVersion::query()->create([
        'title' => 'Terms v1',
        'slug' => 'terms-v1',
        'body' => 'Body',
        'type' => TermsType::Terms,
        'effective_at' => now(),
        'is_required' => true,
    ]);

    $user = User::factory()->withoutTwoFactor()->create(['onboarding_completed' => true]);
    UserTermsAcceptance::query()->create([
        'user_id' => $user->id,
        'terms_version_id' => $version->id,
        'accepted_at' => now(),
    ]);
    actingAs($user);

    get(route('dashboard'))->assertOk();
});

test('get required terms versions for user returns only unaccepted required versions', function (): void {
    $required = TermsVersion::query()->create([
        'title' => 'Required',
        'slug' => 'required',
        'body' => 'Body',
        'type' => TermsType::Terms,
        'effective_at' => now(),
        'is_required' => true,
    ]);

    $user = User::factory()->withoutTwoFactor()->create(['onboarding_completed' => true]);
    $action = resolve(GetRequiredTermsVersionsForUser::class);
    $pending = $action->handle($user);

    expect($pending)->toHaveCount(1)->and($pending->first()->id)->toBe($required->id);

    resolve(RecordTermsAcceptance::class)->handle($user, $required);

    $pending = $action->handle($user);
    expect($pending)->toHaveCount(0);
});

test('record terms acceptance creates record with ip and user_agent', function (): void {
    $version = TermsVersion::query()->create([
        'title' => 'Terms v1',
        'slug' => 'terms-v1',
        'body' => 'Body',
        'type' => TermsType::Terms,
        'effective_at' => now(),
        'is_required' => false,
    ]);
    $user = User::factory()->withoutTwoFactor()->create(['onboarding_completed' => true]);

    $request = Illuminate\Http\Request::create('/terms/accept', 'POST');
    $request->server->set('REMOTE_ADDR', '192.168.1.1');
    $request->headers->set('User-Agent', 'TestAgent/1.0');

    $record = resolve(RecordTermsAcceptance::class)->handle($user, $version, $request);

    expect($record->user_id)->toBe($user->id)
        ->and($record->terms_version_id)->toBe($version->id)
        ->and($record->ip)->toBe('192.168.1.1')
        ->and($record->user_agent)->toBe('TestAgent/1.0');

    assertDatabaseHas('user_terms_acceptances', [
        'user_id' => $user->id,
        'terms_version_id' => $version->id,
    ]);
});

test('terms accept page shows pending versions', function (): void {
    TermsVersion::query()->create([
        'title' => 'Terms v1',
        'slug' => 'terms-v1',
        'body' => 'Body',
        'type' => TermsType::Terms,
        'effective_at' => now(),
        'is_required' => true,
    ]);
    $user = User::factory()->withoutTwoFactor()->create([
        'onboarding_completed' => true,
        'email_verified_at' => now(),
    ]);
    actingAs($user);

    $response = get('/terms/accept');
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('terms/accept')
        ->has('pendingVersions', 1)
        ->where('pendingVersions.0.title', 'Terms v1')
    );
});

test('submitting acceptance records and allows access', function (): void {
    $version = TermsVersion::query()->create([
        'title' => 'Terms v1',
        'slug' => 'terms-v1',
        'body' => 'Body',
        'type' => TermsType::Terms,
        'effective_at' => now(),
        'is_required' => true,
    ]);
    $user = User::factory()->withoutTwoFactor()->create(['onboarding_completed' => true]);

    resolve(RecordTermsAcceptance::class)->handle($user, $version, request());

    assertDatabaseHas('user_terms_acceptances', [
        'user_id' => $user->id,
        'terms_version_id' => $version->id,
    ]);

    actingAs($user);
    get(route('dashboard'))->assertOk();
});

test('post terms accept with all required ids records acceptances and redirects', function (): void {
    $version = TermsVersion::query()->create([
        'title' => 'Terms v1',
        'slug' => 'terms-v1',
        'body' => 'Body',
        'type' => TermsType::Terms,
        'effective_at' => now(),
        'is_required' => true,
    ]);
    $user = User::factory()->withoutTwoFactor()->create(['onboarding_completed' => true]);
    actingAs($user);

    $response = post('/terms/accept', [
        'accepted_ids' => [$version->id],
        'intended' => route('dashboard'),
    ]);

    $response->assertRedirect(route('dashboard'));
    assertDatabaseHas('user_terms_acceptances', [
        'user_id' => $user->id,
        'terms_version_id' => $version->id,
    ]);
});
