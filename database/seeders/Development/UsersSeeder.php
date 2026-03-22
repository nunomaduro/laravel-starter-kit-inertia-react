<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Organization;
use App\Models\User;
use App\Support\AssignRoleViaDb;
use Database\Seeders\Concerns\LoadsJsonData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

final class UsersSeeder extends Seeder
{
    use LoadsJsonData;

    private const string PASSWORD = 'password';

    private array $dependencies = ['RolesAndPermissionsSeeder'];

    public function run(): void
    {
        // Standalone `db:seed --class=UsersSeeder` must also suppress UserCreated-driven attach
        // that can mis-bind role name into role_id on PostgreSQL.
        $wasSeedInProgress = config('tenancy.seed_in_progress', false);
        config(['tenancy.seed_in_progress' => true]);
        try {
            // Avoid listener-driven personal org creation + assignRole until JSON users exist;
            // re-enabled after JSON seed so seedOrganizationScenarios/addMember run with correct context.
            $this->seedFromJson();
            $this->seedOrganizationScenarios();
            $this->seedFromFactory();
            $this->ensureSuperAdminHasGlobalRole();
        } finally {
            config(['tenancy.seed_in_progress' => $wasSeedInProgress]);
        }
    }

    private function seedFromJson(): void
    {
        $autoCreate = config('tenancy.auto_create_personal_organization');
        $autoCreateAdmins = config('tenancy.auto_create_personal_organization_for_admins');
        $autoCreateMembers = config('tenancy.auto_create_personal_organization_for_members');
        config([
            'tenancy.auto_create_personal_organization' => false,
            'tenancy.auto_create_personal_organization_for_admins' => false,
            'tenancy.auto_create_personal_organization_for_members' => false,
        ]);
        try {
            $data = $this->loadJson('users.json');

            if (! isset($data['users']) || ! is_array($data['users'])) {
                return;
            }

            foreach ($data['users'] as $userData) {
                $factoryState = $userData['_factory_state'] ?? null;
                $role = $userData['role'] ?? null;
                $roles = $userData['roles'] ?? [];
                $skipPersonalOrg = ! empty($userData['_skip_personal_org']);
                unset(
                    $userData['_factory_state'],
                    $userData['role'],
                    $userData['roles'],
                    $userData['_skip_personal_org'],
                    $userData['_comment']
                );

                if (! empty($userData['email'])) {
                    if (! isset($userData['password'])) {
                        $userData['password'] = Hash::make(self::PASSWORD);
                    }

                    if (! array_key_exists('email_verified_at', $userData)) {
                        $userData['email_verified_at'] = now();
                    }

                    if (! array_key_exists('onboarding_completed', $userData)) {
                        $userData['onboarding_completed'] = true;
                    }

                    $willBeSuperAdmin = ($role === 'super-admin') || in_array('super-admin', $roles, true);
                    if ($willBeSuperAdmin) {
                        config(['tenancy.auto_create_personal_organization' => false]);
                        // Avoid a second super-admin when installer already created one (e.g. different email).
                        if (User::query()->whereHas('roles', fn ($q) => $q->where('name', 'super-admin'))->exists()) {
                            $existing = User::query()->where('email', $userData['email'])->first();
                            if ($existing === null) {
                                config(['tenancy.auto_create_personal_organization' => true]);

                                continue;
                            }
                            // Same email: update existing (e.g. superadmin@example.com from prior run).
                        }
                    }

                    if ($skipPersonalOrg) {
                        config(['tenancy.auto_create_personal_organization' => false]);
                    }

                    $user = User::query()->updateOrCreate(
                        ['email' => $userData['email']],
                        $userData
                    );

                    if ($willBeSuperAdmin || $skipPersonalOrg) {
                        config(['tenancy.auto_create_personal_organization' => true]);
                    }

                    if ($role !== null) {
                        AssignRoleViaDb::assignGlobal($user, [$role]);
                    } elseif ($roles !== []) {
                        AssignRoleViaDb::assignGlobal($user, $roles);
                    }

                    if ($role === 'super-admin' || in_array('super-admin', $roles, true)) {
                        $user->update(['onboarding_completed' => true]);
                    }

                    if (in_array($role, ['admin', 'super-admin'], true)) {
                        $user->update(['onboarding_completed' => true]);
                    }
                } else {
                    $factory = User::factory();
                    if ($factoryState !== null && method_exists($factory, $factoryState)) {
                        $factory = $factory->{$factoryState}();
                    }

                    User::withoutEvents(static fn () => $factory->create($userData));
                }
            }
        } catch (RuntimeException) {
            // JSON file doesn't exist or is invalid - skip silently
        } finally {
            config([
                'tenancy.auto_create_personal_organization' => $autoCreate,
                'tenancy.auto_create_personal_organization_for_admins' => $autoCreateAdmins,
                'tenancy.auto_create_personal_organization_for_members' => $autoCreateMembers,
            ]);
        }
    }

    /**
     * Create shared organizations (Acme, Beta Co) and attach users for multi-org and role scenarios.
     */
    private function seedOrganizationScenarios(): void
    {
        $owner = User::query()->where('email', 'owner@example.com')->first();
        if (! $owner instanceof User) {
            return;
        }

        $acme = Organization::query()->firstOrCreate(
            ['name' => 'Acme'],
            ['owner_id' => $owner->id]
        );
        if ($acme->wasRecentlyCreated) {
            $acme->addMember($owner, 'admin');
        }

        $betaCo = Organization::query()->firstOrCreate(
            ['name' => 'Beta Co'],
            ['owner_id' => $owner->id]
        );
        if ($betaCo->wasRecentlyCreated) {
            $betaCo->addMember($owner, 'admin');
        }

        $multi = User::query()->where('email', 'multi@example.com')->first();
        if ($multi instanceof User && ! $multi->organizations()->where('organizations.id', $acme->id)->exists()) {
            $acme->addMember($multi, 'admin');
        }

        $memberOnly = User::query()->where('email', 'member@example.com')->first();
        if ($memberOnly instanceof User) {
            if (! $memberOnly->organizations()->where('organizations.id', $acme->id)->exists()) {
                $acme->addMember($memberOnly, 'member');
            }

            $memberOnly->organizations()->updateExistingPivot($acme->id, ['is_default' => true]);
        }

        $mixed = User::query()->where('email', 'mixed@example.com')->first();
        if ($mixed instanceof User) {
            if (! $mixed->organizations()->where('organizations.id', $acme->id)->exists()) {
                $acme->addMember($mixed, 'admin');
            }

            if (! $mixed->organizations()->where('organizations.id', $betaCo->id)->exists()) {
                $betaCo->addMember($mixed, 'member');
            }
        }
    }

    private function seedFromFactory(): void
    {
        $autoCreate = config('tenancy.auto_create_personal_organization');
        $autoCreateAdmins = config('tenancy.auto_create_personal_organization_for_admins');
        $autoCreateMembers = config('tenancy.auto_create_personal_organization_for_members');
        config([
            'tenancy.auto_create_personal_organization' => false,
            'tenancy.auto_create_personal_organization_for_admins' => false,
            'tenancy.auto_create_personal_organization_for_members' => false,
        ]);

        $defaultRole = config('permission.default_role', 'user');

        // Factory create fires UserObserver → UserCreated; withoutEvents avoids Spatie attach
        // that can insert role name into role_id when team pivot is missing (PostgreSQL).
        // Create 100+ users total (with 9 from JSON => 95+ here) for demo DataTable testing.
        $adminUsers = User::withoutEvents(function () {
            return User::factory()
                ->admin()
                ->count(3)
                ->create(['onboarding_completed' => true]);
        });
        foreach ($adminUsers as $user) {
            AssignRoleViaDb::assignGlobal($user, ['admin', $defaultRole]);
        }

        $regular = User::withoutEvents(function () {
            return User::factory()
                ->count(70)
                ->sequence(fn (): array => [
                    'created_at' => now()->subDays(fake()->numberBetween(1, 365)),
                    'updated_at' => now()->subDays(fake()->numberBetween(0, 60)),
                ])
                ->create(['onboarding_completed' => true]);
        });
        foreach ($regular as $user) {
            AssignRoleViaDb::assignGlobal($user, [$defaultRole]);
        }

        $needsOnboarding = User::withoutEvents(function () {
            return User::factory()
                ->needsOnboarding()
                ->count(8)
                ->create();
        });
        foreach ($needsOnboarding as $user) {
            AssignRoleViaDb::assignGlobal($user, [$defaultRole]);
        }

        $unverified = User::withoutEvents(function () {
            return User::factory()
                ->unverified()
                ->count(14)
                ->create(['onboarding_completed' => fake()->boolean(70)]);
        });
        foreach ($unverified as $user) {
            AssignRoleViaDb::assignGlobal($user, [$defaultRole]);
        }

        $toSoftDelete = User::withoutEvents(function () {
            return User::factory()
                ->count(5)
                ->sequence(fn (): array => [
                    'created_at' => now()->subDays(fake()->numberBetween(30, 200)),
                    'updated_at' => now()->subDays(fake()->numberBetween(1, 30)),
                ])
                ->create(['onboarding_completed' => true]);
        });
        foreach ($toSoftDelete as $user) {
            AssignRoleViaDb::assignGlobal($user, [$defaultRole]);
            $user->delete();
        }

        $this->attachFactoryUsersToOrganizations();

        config([
            'tenancy.auto_create_personal_organization' => $autoCreate,
            'tenancy.auto_create_personal_organization_for_admins' => $autoCreateAdmins,
            'tenancy.auto_create_personal_organization_for_members' => $autoCreateMembers,
        ]);
    }

    /**
     * Attach a subset of factory-created users to Acme/Beta so organizations_count varies in the DataTable.
     */
    private function attachFactoryUsersToOrganizations(): void
    {
        $acme = Organization::query()->where('name', 'Acme')->first();
        $betaCo = Organization::query()->where('name', 'Beta Co')->first();
        if ($acme === null || $betaCo === null) {
            return;
        }

        $candidates = User::query()
            ->whereDoesntHave('organizations')
            ->whereNull('deleted_at')
            ->whereNotIn('email', [
                'superadmin@example.com',
                'owner@example.com',
                'admin@example.com',
                'member@example.com',
                'multi@example.com',
                'mixed@example.com',
            ])
            ->limit(50)
            ->get();

        foreach ($candidates->take(35) as $user) {
            if (! $user->organizations()->where('organizations.id', $acme->id)->exists()) {
                $acme->addMember($user, 'member');
            }
        }

        foreach ($candidates->slice(10, 20) as $user) {
            if (! $user->organizations()->where('organizations.id', $betaCo->id)->exists()) {
                $betaCo->addMember($user, 'member');
            }
        }
    }

    /**
     * Ensure the demo super-admin user has the global super-admin role (for login and /users access)
     * and belongs to at least one organization so TenantContext can resolve.
     */
    private function ensureSuperAdminHasGlobalRole(): void
    {
        $user = User::query()->where('email', 'superadmin@example.com')->first();
        if ($user === null) {
            return;
        }

        AssignRoleViaDb::assignGlobal($user, ['super-admin']);

        // Attach super-admin to Acme if they have no organizations, so tenant context resolves.
        if (! $user->organizations()->exists()) {
            $acme = Organization::query()->where('name', 'Acme')->first();
            if ($acme instanceof Organization && ! $user->organizations()->where('organizations.id', $acme->id)->exists()) {
                $acme->addMember($user, 'admin');
                $user->organizations()->updateExistingPivot($acme->id, ['is_default' => true]);
            }
        }

        // Ensure a default org is set if they have orgs but no default.
        if ($user->organizations()->exists() && $user->defaultOrganization() === null) {
            $firstOrg = $user->organizations()->first();
            if ($firstOrg !== null) {
                $user->organizations()->updateExistingPivot($firstOrg->id, ['is_default' => true]);
            }
        }
    }
}
