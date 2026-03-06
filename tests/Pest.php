<?php

declare(strict_types=1);

use App\Models\User;
use App\Testing\SeedHelper;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        Str::createRandomStringsNormally();
        Str::createUuidsNormally();
        Http::preventStrayRequests();
        Process::preventStrayProcesses();
        Sleep::fake();

        $this->freezeTime();
        $this->withoutVite();
    })
    ->in('Feature', 'Unit');

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        Str::createRandomStringsNormally();
        Str::createUuidsNormally();
        Http::preventStrayRequests();
        Process::preventStrayProcesses();
        Sleep::fake();

        $this->freezeTime();
        // Do not call withoutVite() so Browser tests load real Vite assets and the app JS runs.
    })
    ->in('Browser');

expect()->extend('toBeOne', function (): mixed {
    /** @var Pest\Expectation $this */
    return $this->toBe(1);
});

/**
 * Seed a model and its relationships for testing.
 *
 * @param  class-string<Illuminate\Database\Eloquent\Model>  $modelClass
 * @return Illuminate\Database\Eloquent\Collection
 */
function seedFor(string $modelClass, int $count = 1)
{
    return SeedHelper::seedFor($modelClass, $count);
}

/**
 * Seed multiple models at once.
 *
 * @param  array<class-string<Illuminate\Database\Eloquent\Model>|array{class: class-string<Illuminate\Database\Eloquent\Model>, count: int}>  $models
 * @return array<string, Illuminate\Database\Eloquent\Collection>
 */
function seedMany(array $models): array
{
    return SeedHelper::seedMany($models);
}

/**
 * Seed using a named scenario.
 *
 * @return array<string, mixed>
 */
function seedScenario(string $scenarioName): array
{
    return SeedHelper::seedScenario($scenarioName);
}

/**
 * Seed roles/permissions, create an admin or super-admin user, and act as that user.
 * Use in Filament feature tests when you need an authenticated panel user.
 */
function actsAsFilamentAdmin(TestCase $test, string $role = 'admin'): User
{
    $test->seed(RolesAndPermissionsSeeder::class);
    $user = User::factory()->withoutTwoFactor()->create([
        'email' => $role.'@filament-test.example',
        'password' => Hash::make('password'),
    ]);
    $user->assignRole($role);

    $test->actingAs($user);

    return $user;
}

function something(): void
{
    // ..
}

/**
 * Assert that an activity was logged with the given description and optional subject.
 *
 * @param  class-string<Illuminate\Database\Eloquent\Model>|null  $subjectType
 */
function assertActivityLogged(string $description, ?string $subjectType = null, ?int $subjectId = null): void
{
    $query = Spatie\Activitylog\Models\Activity::query()
        ->where('description', $description)
        ->latest();

    if ($subjectType !== null) {
        $query->where('subject_type', $subjectType);
    }

    if ($subjectId !== null) {
        $query->where('subject_id', $subjectId);
    }

    $activity = $query->first();
    PHPUnit\Framework\Assert::assertNotNull(
        $activity,
        sprintf("Expected activity '%s' to be logged.", $description)
    );
}
