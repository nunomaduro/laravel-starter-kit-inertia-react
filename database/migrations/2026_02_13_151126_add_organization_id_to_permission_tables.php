<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $teamForeignKey = config('permission.column_names.team_foreign_key', 'organization_id');

        if (! config('permission.teams')) {
            return;
        }

        $this->disableForeignKeyChecks();

        if (! Schema::hasColumn($tableNames['roles'], $teamForeignKey)) {
            Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamForeignKey): void {
                $table->unsignedBigInteger($teamForeignKey)->nullable()->after('id');
                $table->index($teamForeignKey);
            });
            Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamForeignKey): void {
                $table->dropUnique('roles_name_guard_name_unique');
                $table->unique([$teamForeignKey, 'name', 'guard_name'], 'roles_team_name_guard_unique');
            });
        }

        if (! Schema::hasColumn($tableNames['model_has_permissions'], $teamForeignKey)) {
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamForeignKey): void {
                $table->unsignedBigInteger($teamForeignKey)->nullable()->after('permission_id');
                $table->index($teamForeignKey);
            });
            DB::table($tableNames['model_has_permissions'])->whereNull($teamForeignKey)->update([$teamForeignKey => 0]);
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamForeignKey): void {
                $table->unsignedBigInteger($teamForeignKey)->nullable(false)->default(0)->change();
            });
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamForeignKey, $tableNames): void {
                $table->dropForeign(['permission_id']);
                $table->dropPrimary('model_has_permissions_permission_model_type_primary');
                $table->primary(
                    ['permission_id', 'model_id', 'model_type', $teamForeignKey],
                    'model_has_permissions_primary'
                );
                $table->foreign('permission_id')
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn($tableNames['model_has_roles'], $teamForeignKey)) {
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamForeignKey): void {
                $table->unsignedBigInteger($teamForeignKey)->nullable()->after('role_id');
                $table->index($teamForeignKey);
            });
            DB::table($tableNames['model_has_roles'])->whereNull($teamForeignKey)->update([$teamForeignKey => 0]);
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamForeignKey): void {
                $table->unsignedBigInteger($teamForeignKey)->nullable(false)->default(0)->change();
            });
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamForeignKey, $tableNames): void {
                $table->dropForeign(['role_id']);
                $table->dropPrimary('model_has_roles_role_model_type_primary');
                $table->primary(
                    ['role_id', 'model_id', 'model_type', $teamForeignKey],
                    'model_has_roles_primary'
                );
                $table->foreign('role_id')
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->cascadeOnDelete();
            });
        }

        $this->enableForeignKeyChecks();
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $teamForeignKey = config('permission.column_names.team_foreign_key', 'organization_id');

        if (! config('permission.teams')) {
            return;
        }

        $this->disableForeignKeyChecks();

        if (Schema::hasColumn($tableNames['model_has_roles'], $teamForeignKey)) {
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamForeignKey, $tableNames): void {
                $table->dropForeign(['role_id']);
                $table->dropPrimary('model_has_roles_primary');
                $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
                $table->foreign('role_id')->references('id')->on($tableNames['roles'])->cascadeOnDelete();
                $table->dropColumn($teamForeignKey);
            });
        }

        if (Schema::hasColumn($tableNames['model_has_permissions'], $teamForeignKey)) {
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamForeignKey, $tableNames): void {
                $table->dropForeign(['permission_id']);
                $table->dropPrimary('model_has_permissions_primary');
                $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
                $table->foreign('permission_id')->references('id')->on($tableNames['permissions'])->cascadeOnDelete();
                $table->dropColumn($teamForeignKey);
            });
        }

        if (Schema::hasColumn($tableNames['roles'], $teamForeignKey)) {
            Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamForeignKey): void {
                $table->dropUnique('roles_team_name_guard_unique');
                $table->unique(['name', 'guard_name'], 'roles_name_guard_name_unique');
                $table->dropColumn($teamForeignKey);
            });
        }

        $this->enableForeignKeyChecks();
    }

    private function disableForeignKeyChecks(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        match ($driver) {
            'mysql', 'mariadb' => DB::statement('SET FOREIGN_KEY_CHECKS=0'),
            'sqlite' => DB::statement('PRAGMA foreign_keys = OFF'),
            'pgsql' => null, // Managed Postgres (e.g. Laravel Cloud) does not allow session_replication_role
            default => null,
        };
    }

    private function enableForeignKeyChecks(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        match ($driver) {
            'mysql', 'mariadb' => DB::statement('SET FOREIGN_KEY_CHECKS=1'),
            'sqlite' => DB::statement('PRAGMA foreign_keys = ON'),
            'pgsql' => null, // Managed Postgres (e.g. Laravel Cloud) does not allow session_replication_role
            default => null,
        };
    }
};
