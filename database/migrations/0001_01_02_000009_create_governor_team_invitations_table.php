<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGovernorTeamInvitationsTable extends Migration
{
    public function __construct()
    {
        if (app()->bound("Hyn\Tenancy\Environment")) {
            $this->connection = config("tenancy.db.tenant-connection-name");
        }
    }

    public function up(): void
    {

        Schema::create('governor_team_invitations', function (Blueprint $table): void {
            $user = app(config('genealabs-laravel-governor.models.auth'));

            $table->bigIncrements("id");
            $table->unsignedBigInteger("governor_owned_by")
                ->nullable();
            $table->unsignedBigInteger('team_id');
            $table->timestamps();

            $table->string("email");
            $table->string("token");

            $table->foreign('team_id')
                ->references('id')
                ->on('governor_teams')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });
    }

    public function down(): void
    {
        Schema::drop('governor_team_invitations');
    }
}
