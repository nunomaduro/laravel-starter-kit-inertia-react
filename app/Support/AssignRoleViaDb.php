<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * Assign roles via direct model_has_roles inserts to avoid Spatie attach() mis-binding
 * on PostgreSQL when team context can bind role name strings into bigint columns.
 */
final class AssignRoleViaDb
{
    /**
     * @param  array<string>  $roleNames
     */
    public static function assignGlobal(User $user, array $roleNames): void
    {
        $tableNames = config('permission.table_names');
        $teamKey = config('permission.column_names.team_foreign_key');
        $pivotRole = config('permission.column_names.role_pivot_key') ?? 'role_id';
        $modelMorphKey = config('permission.column_names.model_morph_key') ?? 'model_id';
        foreach (array_unique($roleNames) as $name) {
            $role = Role::query()
                ->where('name', $name)
                ->where('guard_name', 'web')
                ->where(function ($q) use ($teamKey): void {
                    $q->whereNull($teamKey)->orWhere($teamKey, 0);
                })
                ->first();
            if (! $role instanceof Role) {
                $role = Role::findByName($name, 'web');
            }
            if (! $role instanceof Role) {
                continue;
            }
            DB::table($tableNames['model_has_roles'])->insertOrIgnore([
                $pivotRole => $role->getKey(),
                'model_type' => User::class,
                $modelMorphKey => $user->getKey(),
                $teamKey => 0,
            ]);
        }
        $user->unsetRelation('roles');
    }

    /**
     * Assign an organization-scoped role by inserting model_has_roles with explicit role id.
     */
    public static function assignOrg(User $user, int $organizationId, string $roleName = 'admin'): void
    {
        $teamKey = config('permission.column_names.team_foreign_key');
        $roleModel = Role::query()
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->where($teamKey, $organizationId)
            ->first();
        if (! $roleModel instanceof Role) {
            return;
        }
        $tableNames = config('permission.table_names');
        $pivotRole = config('permission.column_names.role_pivot_key') ?? 'role_id';
        $modelMorphKey = config('permission.column_names.model_morph_key') ?? 'model_id';
        DB::table($tableNames['model_has_roles'])->insertOrIgnore([
            $pivotRole => $roleModel->getKey(),
            'model_type' => User::class,
            $modelMorphKey => $user->getKey(),
            $teamKey => $organizationId,
        ]);
        $user->unsetRelation('roles');
    }

    /**
     * Replace the user's roles for one organization only (sync semantics without Spatie syncRoles).
     * Deletes existing model_has_roles rows for this user+org, then inserts the single org role by id.
     */
    public static function syncOrg(User $user, int $organizationId, string $roleName): void
    {
        $tableNames = config('permission.table_names');
        $teamKey = config('permission.column_names.team_foreign_key');
        $modelMorphKey = config('permission.column_names.model_morph_key') ?? 'model_id';
        $table = $tableNames['model_has_roles'];
        DB::table($table)
            ->where('model_type', User::class)
            ->where($modelMorphKey, $user->getKey())
            ->where($teamKey, $organizationId)
            ->delete();
        self::assignOrg($user, $organizationId, $roleName);
    }
}
