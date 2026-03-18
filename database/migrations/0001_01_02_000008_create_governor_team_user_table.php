<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGovernorTeamUserTable extends Migration
{
    public function __construct()
    {
        if (app()->bound("Hyn\Tenancy\Environment")) {
            $this->connection = config("tenancy.db.tenant-connection-name");
        }
    }

    public function up(): void
    {
        Schema::create('governor_team_user', function (Blueprint $table): void {
            $user = app(config('genealabs-laravel-governor.models.auth'));

            $table->unsignedBigInteger('team_id')
                ->index();
            $table->unsignedBigInteger('user_id')
                ->index();
            $table->timestamps();

            $table->foreign('team_id')
                ->references('id')
                ->on('governor_teams')
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
        Schema::drop('governor_team_user');
    }
}
