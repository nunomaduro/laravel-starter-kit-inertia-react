<?php

declare(strict_types=1);

use App\Models\User;
use App\Testing\SeedHelper;
use Database\Seeders\Essential\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
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

        // Ensure Spatie Permission uses teams so assignRole attaches organization_id (NOT NULL on pivot)
        ensurePermissionTeamsForTests();
        setPermissionsTeamId(0);

        // Set seed_in_progress config so CreatePersonalOrganizationOnUserCreated skips
        // (it checks this flag and returns early). This prevents the role assignment
        // cascade that fails on SQLite (organization_id NOT NULL on model_has_roles).
        config()->set('tenancy.seed_in_progress', true);

        // Mark setup as complete so RedirectToInstallerIfNotSetup middleware
        // does not return 503 on every request. The settings table exists (from
        // schema dump) but has no data rows, so setup_completed defaults to false.
        config()->set('settings.setup_completed', true);

        // Disable laravel-governor's CreatedListener which tries to assign a "Member" role
        // via $model->roles()->syncWithoutDetaching('Member') on every User creation.
        // This conflicts with Spatie's model_has_roles.organization_id NOT NULL constraint.
        // Disable laravel-governor's CreatedListener which tries to assign a "Member" role
        // via $model->roles()->syncWithoutDetaching('Member') on every User creation.
        // This conflicts with Spatie's model_has_roles.organization_id NOT NULL constraint.
        config()->set('genealabs-laravel-governor.models.auth', 'disabled');
    })
    ->in('Feature', 'Unit', '../modules/*/tests');

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

        // Mark setup as complete so RedirectToInstallerIfNotSetup middleware
        // does not return 503 on every request.
        config()->set('settings.setup_completed', true);
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
 * Force PermissionRegistrar into teams mode so assignRole/syncRoles attach organization_id (required by migrations).
 */
function ensurePermissionTeamsForTests(): void
{
    $registrar = resolve(PermissionRegistrar::class);
    $registrar->teams = true;
    $registrar->teamsKey = config('permission.column_names.team_foreign_key', 'organization_id');
}

/**
 * Seed roles/permissions, create an admin or super-admin user, and act as that user.
 * Use in Filament feature tests when you need an authenticated panel user.
 */
function actsAsFilamentAdmin(TestCase $test, string $role = 'admin'): User
{
    ensurePermissionTeamsForTests();
    setPermissionsTeamId(0);

    $test->seed(RolesAndPermissionsSeeder::class);
    // Avoid UserCreated → CreatePersonalOrganization (assignRole pivot without organization_id in sqlite)
    $user = User::withoutEvents(static fn (): User => User::factory()->withoutTwoFactor()->create([
        'email' => $role.'@filament-test.example',
        'password' => Hash::make('password'),
    ]));
    // assignRole() pivot attach can omit organization_id; insert pivot row explicitly (global team id 0)
    $roleModel = Role::findByName($role, 'web');
    if ($roleModel !== null) {
        DB::table(config('permission.table_names.model_has_roles'))->insertOrIgnore([
            'role_id' => $roleModel->id,
            'model_id' => $user->id,
            'model_type' => User::class,
            'organization_id' => 0,
        ]);
    }
    $user->unsetRelation('roles');
    resolve(PermissionRegistrar::class)->forgetCachedPermissions();

    $test->actingAs($user);

    return $user;
}

/**
 * Attach a global (team id 0) role without using assignRole() — avoids sqlite pivot missing organization_id.
 * Call after seed(RolesAndPermissionsSeeder) and creating the user with User::withoutEvents.
 */
function assignRoleForTestUser(User $user, string $role = 'super-admin'): void
{
    ensurePermissionTeamsForTests();
    setPermissionsTeamId(0);
    $teamKey = config('permission.column_names.team_foreign_key', 'organization_id');

    // Find or create the role (org-scoped roles like 'member' may not exist globally)
    $roleModel = Role::query()
        ->where('name', $role)
        ->where('guard_name', 'web')
        ->where(fn ($q) => $q->whereNull($teamKey)->orWhere($teamKey, 0))
        ->first();

    if ($roleModel === null) {
        $roleModel = Role::query()->create([
            'name' => $role,
            'guard_name' => 'web',
            $teamKey => 0,
        ]);
    }

    DB::table(config('permission.table_names.model_has_roles'))->insertOrIgnore([
        'role_id' => $roleModel->id,
        'model_id' => $user->id,
        'model_type' => User::class,
        'organization_id' => 0,
    ]);

    $user->unsetRelation('roles');
    resolve(PermissionRegistrar::class)->forgetCachedPermissions();
}

/**
 * Create a test user without firing model events (avoids UserObserver → CreatePersonalOrganization
 * which triggers assignRole on the pivot without organization_id in SQLite).
 *
 * @param  array<string, mixed>  $attributes
 */
function createTestUser(array $attributes = []): User
{
    return User::withoutEvents(static fn (): User => User::factory()->withoutTwoFactor()->create($attributes));
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
