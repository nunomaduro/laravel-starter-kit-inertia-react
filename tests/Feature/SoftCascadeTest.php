<?php

declare(strict_types=1);

use App\Enums\TermsType;
use App\Models\NotificationPreference;
use App\Models\Organization;
use App\Models\OrganizationDomain;
use App\Models\OrganizationInvitation;
use App\Models\SocialAccount;
use App\Models\TermsVersion;
use App\Models\User;
use App\Models\UserTermsAcceptance;

it('cascade soft-deletes from user to owned organizations, social accounts, terms acceptances, and notification preferences', function (): void {
    $user = User::withoutEvents(function () {
        return User::factory()->withoutTwoFactor()->create();
    });

    $org = Organization::factory()->create(['owner_id' => $user->id]);
    SocialAccount::query()->create([
        'user_id' => $user->id,
        'provider' => 'github',
        'provider_id' => (string) fake()->numberBetween(10000, 99999),
    ]);
    $termsVersion = TermsVersion::query()->create([
        'title' => 'Test Terms',
        'slug' => 'test-terms-'.fake()->uuid(),
        'body' => 'Body',
        'type' => TermsType::Terms,
        'effective_at' => now()->toDateString(),
        'is_required' => false,
    ]);
    UserTermsAcceptance::query()->create([
        'user_id' => $user->id,
        'terms_version_id' => $termsVersion->id,
        'accepted_at' => now(),
    ]);
    NotificationPreference::query()->create([
        'user_id' => $user->id,
        'notification_type' => 'test',
        'via_database' => true,
        'via_email' => true,
    ]);

    $user->delete();

    expect($user->trashed())->toBeTrue();
    expect($org->fresh()->trashed())->toBeTrue();
    expect(SocialAccount::withTrashed()->where('user_id', $user->id)->first()->trashed())->toBeTrue();
    expect(UserTermsAcceptance::withTrashed()->where('user_id', $user->id)->first()->trashed())->toBeTrue();
    expect(NotificationPreference::withTrashed()->where('user_id', $user->id)->first()->trashed())->toBeTrue();
});

it('cascade soft-deletes from organization to domains and invitations', function (): void {
    $owner = User::withoutEvents(fn () => User::factory()->withoutTwoFactor()->create());
    $org = Organization::factory()->forOwner($owner)->create();
    $domain = OrganizationDomain::factory()->create(['organization_id' => $org->id]);
    $invitation = OrganizationInvitation::factory()->create([
        'organization_id' => $org->id,
        'invited_by' => $owner->id,
    ]);

    $org->delete();

    expect($org->trashed())->toBeTrue();
    expect($domain->fresh()->trashed())->toBeTrue();
    expect($invitation->fresh()->trashed())->toBeTrue();
});
