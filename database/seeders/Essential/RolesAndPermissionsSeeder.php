<?php

declare(strict_types=1);

namespace Database\Seeders\Essential;

use App\Services\PermissionCategoryResolver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolesAndPermissionsSeeder extends Seeder
{
    private const string GUARD = 'web';

    public function run(): void
    {
        resolve(PermissionRegistrar::class)->forgetCachedPermissions();

        $corePermissions = [
            'bypass-permissions',
            'access admin panel',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'announcements.manage_global',
        ];

        foreach ($corePermissions as $name) {
            Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => self::GUARD]);
        }

        $superAdmin = Role::query()->firstOrCreate(['name' => 'super-admin', 'guard_name' => self::GUARD]);
        $superAdmin->givePermissionTo(['bypass-permissions', 'announcements.manage_global']);

        $admin = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => self::GUARD]);
        $userRole = Role::query()->firstOrCreate(['name' => 'user', 'guard_name' => self::GUARD]);
        $defaultRolePerms = config('permission.default_role_permissions', []);
        if (is_array($defaultRolePerms) && $defaultRolePerms !== []) {
            $userRole->syncPermissions(
                array_filter($defaultRolePerms, fn (string $name): bool => Permission::query()->where('name', $name)->where('guard_name', self::GUARD)->exists())
            );
        }

        if (config('permission.permission_categories_enabled', false)) {
            $resolver = resolve(PermissionCategoryResolver::class);
            $adminPerms = $resolver->getPermissionsForRole('admin');
            if ($adminPerms !== []) {
                $admin->syncPermissions($adminPerms);
            } else {
                $admin->syncPermissions([
                    'access admin panel',
                    'view users',
                    'create users',
                    'edit users',
                    'delete users',
                ]);
            }
        } else {
            $admin->syncPermissions([
                'access admin panel',
                'view users',
                'create users',
                'edit users',
                'delete users',
            ]);
        }

        // Always sync route permissions so they exist in Filament for assignment to roles.
        Artisan::call('permission:sync-routes', ['--silent' => true]);

        // Sync org permissions from organization-permissions.json so org roles (admin/member) get org.* permissions.
        if (is_file(database_path('seeders/data/organization-permissions.json'))) {
            Artisan::call('permission:sync', ['--silent' => true]);
        }

        resolve(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
