<?php

declare(strict_types=1);

use App\Actions\RecordTermsAcceptance;
use App\Models\TermsVersion;
use App\Models\User;
use App\Models\UserTermsAcceptance;

it('records a terms acceptance for the user', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $terms = TermsVersion::factory()->required()->create();

    $action = resolve(RecordTermsAcceptance::class);
    $acceptance = $action->handle($user, $terms);

    expect($acceptance)->toBeInstanceOf(UserTermsAcceptance::class)
        ->and($acceptance->user_id)->toBe($user->id)
        ->and($acceptance->terms_version_id)->toBe($terms->id)
        ->and($acceptance->accepted_at)->not->toBeNull();
});

it('records IP and user agent from the request', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();
    $terms = TermsVersion::factory()->required()->create();

    $request = Illuminate\Http\Request::create('/terms/accept', 'POST', [], [], [], [
        'REMOTE_ADDR' => '192.168.1.100',
        'HTTP_USER_AGENT' => 'TestAgent/1.0',
    ]);

    $action = resolve(RecordTermsAcceptance::class);
    $acceptance = $action->handle($user, $terms, $request);

    expect($acceptance->ip)->toBe('192.168.1.100')
        ->and($acceptance->user_agent)->toBe('TestAgent/1.0');
});
