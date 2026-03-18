<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGovernorEntitiesTable extends Migration
{
    public function __construct()
    {
        if (app()->bound("Hyn\Tenancy\Environment")) {
            $this->connection = config("tenancy.db.tenant-connection-name");
        }
    }

    public function up(): void
    {
        Schema::create('governor_entities', function (Blueprint $table): void {
            $table->string('name')
                ->unique()
                ->primary();
            $table->string('group_name')->nullable();
            $table->timestamps();
            $table->foreign("group_name")
                ->references("name")
                ->on("governor_groups")
                ->onUpdate("CASCADE")
                ->onDelete("SET NULL");
        });
    }

    public function down(): void
    {
        Schema::drop('governor_entities');
    }
}
