<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

final readonly class CreatePersonalOrganizationForUserAction
{
    /**
     * Create a personal organization for the user and add them as owner and admin.
     */
    public function handle(User $user): Organization
    {
        return DB::transaction(function () use ($user): Organization {
            $name = str_replace(
                '{name}',
                $user->name,
                config('tenancy.default_organization_name', "{name}'s Workspace")
            );

            $organization = Organization::query()->create([
                'name' => $name,
                'owner_id' => $user->id,
            ]);

            $teamKey = config('permission.column_names.team_foreign_key');
            $guard = 'web';

            Role::query()->create([
                'name' => 'admin',
                'guard_name' => $guard,
                $teamKey => $organization->id,
            ]);
            Role::query()->create([
                'name' => 'member',
                'guard_name' => $guard,
                $teamKey => $organization->id,
            ]);

            $organization->users()->attach($user->id, [
                'is_default' => true,
                'joined_at' => now(),
                'invited_by' => null,
            ]);

            // Assign org admin role by id (insert into model_has_roles) — assignRole() can mis-bind on PostgreSQL.
            $roleModel = Role::query()
                ->where('name', 'admin')
                ->where('guard_name', $guard)
                ->where($teamKey, $organization->id)
                ->first();
            if ($roleModel instanceof Role) {
                $tableNames = config('permission.table_names');
                $pivotRole = config('permission.column_names.role_pivot_key') ?? 'role_id';
                $modelMorphKey = config('permission.column_names.model_morph_key') ?? 'model_id';
                DB::table($tableNames['model_has_roles'])->insertOrIgnore([
                    $pivotRole => $roleModel->getKey(),
                    'model_type' => User::class,
                    $modelMorphKey => $user->getKey(),
                    $teamKey => $organization->id,
                ]);
                $user->unsetRelation('roles');
            }

            return $organization;
        });
    }
}
