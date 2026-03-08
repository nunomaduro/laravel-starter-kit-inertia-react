<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\Organization;
use App\Models\User;
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
        $this->seedFromJson();
        $this->seedOrganizationScenarios();
        $this->seedFromFactory();
    }

    private function seedFromJson(): void
    {
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
                        $user->syncRoles([$role]);
                    } elseif ($roles !== []) {
                        $user->syncRoles($roles);
                    }

                    if ($user->hasRole('super-admin')) {
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

                    $factory->create($userData);
                }
            }
        } catch (RuntimeException) {
            // JSON file doesn't exist or is invalid - skip silently
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
        $defaultRole = config('permission.default_role', 'user');

        $adminUsers = User::factory()
            ->admin()
            ->count(2)
            ->create(['onboarding_completed' => true]);

        foreach ($adminUsers as $user) {
            $user->syncRoles(['admin', $defaultRole]);
        }

        User::factory()
            ->count(5)
            ->create()
            ->each(fn (User $user): User => $user->assignRole($defaultRole));

        User::factory()
            ->unverified()
            ->count(2)
            ->create()
            ->each(fn (User $user): User => $user->assignRole($defaultRole));
    }
}
