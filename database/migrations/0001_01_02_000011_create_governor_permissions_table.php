<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGovernorPermissionsTable extends Migration
{
    public function __construct()
    {
        if (app()->bound("Hyn\Tenancy\Environment")) {
            $this->connection = config("tenancy.db.tenant-connection-name");
        }
    }

    public function up(): void
    {
        Schema::create('governor_permissions', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('role_name')
                ->nullable();
            $table->string('entity_name');
            $table->string('action_name');
            $table->string('ownership_name');
            $table->unsignedBigInteger("team_id")
                ->nullable();
            $table->timestamps();

            $table->unique(['role_name', 'entity_name', 'action_name', 'ownership_name'], 'governor_permissions_unique_key');
            $table->foreign('role_name')
                ->references('name')
                ->on('governor_roles')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('entity_name')
                ->references('name')
                ->on('governor_entities')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('action_name')
                ->references('name')
                ->on('governor_actions')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('ownership_name')
                ->references('name')
                ->on('governor_ownerships')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('team_id')
                ->references('id')
                ->on('governor_teams')
                ->onDelete('SET NULL')
                ->onUpdate('CASCADE');
        });
    }

    public function down(): void
    {
        Schema::drop('governor_permissions');
    }
}
