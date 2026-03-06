<?php

declare(strict_types=1);

namespace Database\Seeders\Development;

use App\Models\User;
use Database\Seeders\Concerns\LoadsJsonData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

final class UsersSeeder extends Seeder
{
    use LoadsJsonData;

    private array $dependencies = ['RolesAndPermissionsSeeder'];

    public function run(): void
    {
        $this->seedFromJson();
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
                unset($userData['_factory_state'], $userData['role'], $userData['roles']);

                if (! empty($userData['email'])) {
                    if (! isset($userData['password'])) {
                        $userData['password'] = Hash::make('password');
                    }

                    if (! isset($userData['email_verified_at'])) {
                        $userData['email_verified_at'] = now();
                    }

                    // Prevent personal org creation for super-admins (role assigned after event fires).
                    $willBeSuperAdmin = ($role === 'super-admin') || in_array('super-admin', $roles, true);
                    if ($willBeSuperAdmin) {
                        config(['tenancy.auto_create_personal_organization' => false]);
                    }

                    $user = User::query()->updateOrCreate(
                        ['email' => $userData['email']],
                        $userData
                    );

                    if ($willBeSuperAdmin) {
                        config(['tenancy.auto_create_personal_organization' => true]);
                    }

                    if ($role !== null) {
                        $user->syncRoles([$role]);
                    } elseif ($roles !== []) {
                        $user->syncRoles($roles);
                    }

                    // So demo admin can access dashboard and Filament without completing onboarding.
                    if ($user->hasRole('super-admin')) {
                        $user->update(['onboarding_completed' => true]);
                    }
                } else {
                    // Fallback to factory if no email
                    $factory = User::factory();
                    if ($factoryState !== null && method_exists($factory, $factoryState)) {
                        $factory = $factory->{$factoryState}();
                    }

                    $factory->create($userData);
                }
            }
        } catch (RuntimeException) {
            // JSON file doesn't exist or is invalid - skip silently
            // This allows seeders to work with or without JSON files
        }
    }

    private function seedFromFactory(): void
    {
        $defaultRole = config('permission.default_role', 'user');

        // Admin users: skip onboarding so they can log in directly.
        $adminUsers = User::factory()
            ->admin()
            ->count(2)
            ->create(['onboarding_completed' => true]);

        foreach ($adminUsers as $user) {
            $user->syncRoles(['admin', $defaultRole]);
        }

        // Regular and unverified factory users — assign default role so permissions work.
        User::factory()
            ->count(5)
            ->create()
            ->each(fn (User $user) => $user->assignRole($defaultRole));

        User::factory()
            ->unverified()
            ->count(2)
            ->create()
            ->each(fn (User $user) => $user->assignRole($defaultRole));
    }
}
