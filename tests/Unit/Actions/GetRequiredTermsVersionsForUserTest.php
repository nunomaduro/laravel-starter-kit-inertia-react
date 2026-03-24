<?php

declare(strict_types=1);

use App\Actions\GetRequiredTermsVersionsForUser;
use App\Models\TermsVersion;
use App\Models\User;
use App\Models\UserTermsAcceptance;

it('returns required terms versions the user has not accepted', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $terms = TermsVersion::factory()->required()->create([
        'effective_at' => now()->subDay(),
    ]);

    $action = resolve(GetRequiredTermsVersionsForUser::class);
    $result = $action->handle($user);

    expect($result)->toHaveCount(1)
        ->and($result->first()->id)->toBe($terms->id);
});

it('excludes already accepted terms versions', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $terms = TermsVersion::factory()->required()->create([
        'effective_at' => now()->subDay(),
    ]);

    UserTermsAcceptance::query()->create([
        'user_id' => $user->id,
        'terms_version_id' => $terms->id,
        'accepted_at' => now(),
    ]);

    $action = resolve(GetRequiredTermsVersionsForUser::class);
    $result = $action->handle($user);

    expect($result)->toHaveCount(0);
});

it('excludes non-required terms versions', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    TermsVersion::factory()->optional()->create([
        'effective_at' => now()->subDay(),
    ]);

    $action = resolve(GetRequiredTermsVersionsForUser::class);
    $result = $action->handle($user);

    expect($result)->toHaveCount(0);
});
