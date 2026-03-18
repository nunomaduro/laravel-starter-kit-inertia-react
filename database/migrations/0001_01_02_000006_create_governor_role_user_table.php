<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGovernorRoleUserTable extends Migration
{
    public function __construct()
    {
        if (app()->bound("Hyn\Tenancy\Environment")) {
            $this->connection = config("tenancy.db.tenant-connection-name");
        }
    }

    public function up(): void
    {
        Schema::create('governor_role_user', function (Blueprint $table): void {
            $user = app()->make(config('genealabs-laravel-governor.models.auth'));
            $table->bigIncrements("id");
            $table->string('role_name');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->unique(["role_name", "user_id"]);
            $table->foreign('role_name')
                ->references('name')
                ->on('governor_roles')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('user_id')
                ->references($user->getKeyName())
                ->on($user->getTable())
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });
    }

    public function down(): void
    {
        Schema::drop('governor_role_user');
    }
}
