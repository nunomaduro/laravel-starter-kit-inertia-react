<?php

declare(strict_types=1);

use GeneaLabs\LaravelGovernor\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGovernorTeamsTable extends Migration
{
    protected $userInstance;

    public function __construct()
    {
        if (app()->bound("Hyn\Tenancy\Environment")) {
            $this->connection = config("tenancy.db.tenant-connection-name");
        }

        $this->userInstance = app(config('genealabs-laravel-governor.models.auth'));
    }

    public function up(): void
    {
        Schema::create('governor_teams', function (Blueprint $table): void {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("governor_owned_by")
                ->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->string('name');
            $table->text("description")
                ->nullable();

            $table->foreign('governor_owned_by')
                ->references($this->userInstance->getKeyName())
                ->on($this->userInstance->getTable())
                ->onDelete('SET NULL')
                ->onUpdate('CASCADE');
        });

        Schema::table($this->userInstance->getTable(), function (Blueprint $table): void {
            $table->foreignIdFor(Team::class, "current_team_id")
                ->nullable()
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::drop('governor_teams');
    }
}
