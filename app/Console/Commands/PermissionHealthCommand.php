<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

final class PermissionHealthCommand extends Command
{
    private const string SUPER_ADMIN_ROLE = 'super-admin';

    private const string DEFAULT_ROLE = 'user';

    protected $signature = 'permission:health
                            {--strict : Exit with code 1 on warnings (e.g. users with no roles)}';

    protected $description = 'Check RBAC health: super-admin role exists, users have roles, default role has permissions';

    public function handle(): int
    {
        $strict = $this->option('strict');
        $failed = false;

        if (! $this->checkSuperAdminRoleExists()) {
            $failed = true;
        }

        $usersWithoutRoles = $this->usersWithoutRoles();
        if ($usersWithoutRoles > 0) {
            $this->warn($usersWithoutRoles.' user(s) have no roles assigned.');
            if ($strict) {
                $failed = true;
            }
        }

        if ($this->defaultRoleHasNoPermissions()) {
            $this->warn('The "'.self::DEFAULT_ROLE.'" role has no permissions.');
            if ($strict) {
                $failed = true;
            }
        }

        if (! $failed) {
            $this->info('Permission health check passed.');
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function checkSuperAdminRoleExists(): bool
    {
        $exists = Role::query()->where('name', self::SUPER_ADMIN_ROLE)->exists();
        if (! $exists) {
            $this->error('Critical: "'.self::SUPER_ADMIN_ROLE.'" role does not exist. Run RolesAndPermissionsSeeder.');
        }

        return $exists;
    }

    private function usersWithoutRoles(): int
    {
        return User::query()
            ->whereDoesntHave('roles')
            ->count();
    }

    private function defaultRoleHasNoPermissions(): bool
    {
        $role = Role::query()->where('name', self::DEFAULT_ROLE)->first();
        if ($role === null) {
            return false;
        }

        return $role->permissions()->count() === 0;
    }
}
