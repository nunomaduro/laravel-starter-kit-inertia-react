<?php

declare(strict_types=1);

namespace App\Services\Organization;

use App\Models\Organization;
use InvalidArgumentException;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Manages custom org-scoped roles that org admins can create.
 * Custom roles use only org_grantable=true permissions from organization-permissions.json.
 */
final class OrgCustomRoleService
{
    private const string GUARD = 'web';

    /**
     * @return array<string>
     */
    public function getGrantablePermissionNames(): array
    {
        $config = $this->loadConfig();
        $names = [];

        foreach ($config['organization_permissions'] ?? [] as $category) {
            foreach ($category['permissions'] ?? [] as $perm) {
                if (($perm['org_grantable'] ?? false) === true) {
                    $names[] = $perm['name'];
                }
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @return array<array{name: string, label: string, plan_required: string|null, permissions: array<string>}>
     */
    public function getRoleTemplates(): array
    {
        $config = $this->loadConfig();

        return $config['role_templates'] ?? [];
    }

    /**
     * Get all custom roles for an organization.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Role>
     */
    public function getCustomRoles(Organization $organization): \Illuminate\Database\Eloquent\Collection
    {
        $teamKey = config('permission.column_names.team_foreign_key');

        return Role::query()
            ->where('guard_name', self::GUARD)
            ->where($teamKey, $organization->id)
            ->where('name', 'like', 'custom_%')
            ->with('permissions')
            ->get();
    }

    /**
     * Create a custom role for an organization.
     *
     * @param  array<string>  $permissionNames
     *
     * @throws InvalidArgumentException If any permission is not org_grantable
     */
    public function create(Organization $organization, string $name, string $label, array $permissionNames): Role
    {
        $grantable = $this->getGrantablePermissionNames();
        $invalid = array_diff($permissionNames, $grantable);

        if ($invalid !== []) {
            throw new InvalidArgumentException('The following permissions are not org-grantable: '.implode(', ', $invalid));
        }

        $teamKey = config('permission.column_names.team_foreign_key');
        $roleName = 'custom_'.$organization->id.'_'.$name;

        $role = Role::create([
            'name' => $roleName,
            'label' => $label,
            'guard_name' => self::GUARD,
            $teamKey => $organization->id,
        ]);

        $permissions = Permission::query()
            ->where('guard_name', self::GUARD)
            ->whereIn('name', $permissionNames)
            ->get();

        $role->syncPermissions($permissions);

        resolve(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }

    /**
     * Delete a custom role from an organization.
     *
     * @throws InvalidArgumentException If the role is not a custom org role
     */
    public function delete(Organization $organization, Role $role): void
    {
        $teamKey = config('permission.column_names.team_foreign_key');

        throw_if($role->getAttribute($teamKey) !== $organization->id
        || ! str_starts_with($role->name, 'custom_'), InvalidArgumentException::class, 'The role does not belong to this organization or is not a custom role.');

        $role->delete();

        resolve(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return array<string, mixed>
     */
    private function loadConfig(): array
    {
        $path = database_path('seeders/data/organization-permissions.json');
        throw_unless(is_file($path), RuntimeException::class, 'Organization permissions config not found: '.$path);

        $content = file_get_contents($path);
        throw_if($content === false, RuntimeException::class, 'Failed to read organization permissions config');

        $decoded = json_decode($content, true);
        throw_if(json_last_error() !== JSON_ERROR_NONE, RuntimeException::class, 'Invalid JSON in organization permissions config');

        return $decoded;
    }
}
