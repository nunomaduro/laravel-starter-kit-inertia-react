<?php

declare(strict_types=1);

use App\Actions\CompleteOnboardingAction;
use App\Events\User\UserCreated;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use LevelUp\Experience\Models\Achievement;
use LevelUp\Experience\Models\Level;
use Modules\Gamification\Database\Seeders\GamificationSeeder;

beforeEach(function (): void {
    $this->seed(GamificationSeeder::class);
});

it('dispatches UserCreated when a user is created', function (): void {
    Event::fake([UserCreated::class]);

    $user = User::factory()->create();

    Event::assertDispatched(UserCreated::class, fn (UserCreated $e): bool => $e->user->id === $user->id);
});

it('awards signup XP when user is created and gamification is active', function (): void {
    $user = User::factory()->create();

    expect($user->getPoints())->toBeGreaterThanOrEqual(10)
        ->and($user->getLevel())->toBeGreaterThan(0);
});

it('grants Profile Completed achievement when onboarding is completed', function (): void {
    $user = User::factory()->create([
        'onboarding_completed' => false,
    ]);

    expect($user->getUserAchievements()->pluck('name'))->not->toContain('Profile Completed');

    (new CompleteOnboardingAction)->handle($user);

    $user->refresh();
    expect($user->onboarding_completed)->toBeTrue();

    $achievementNames = $user->getUserAchievements()->pluck('name')->all();
    expect($achievementNames)->toContain('Profile Completed');
});

it('seeder creates 100 levels and Profile Completed achievement', function (): void {
    Level::query()->delete();
    Achievement::query()->delete();

    $this->seed(GamificationSeeder::class);

    expect(Level::query()->count())->toBe(100);

    $profileCompleted = Achievement::query()->where('name', 'Profile Completed')->first();
    expect($profileCompleted)->not->toBeNull()
        ->and($profileCompleted->description)->toContain('onboarding');
});
