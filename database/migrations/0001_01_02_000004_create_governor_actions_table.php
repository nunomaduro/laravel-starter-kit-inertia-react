<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGovernorActionsTable extends Migration
{
    public function __construct()
    {
        if (app()->bound("Hyn\Tenancy\Environment")) {
            $this->connection = config("tenancy.db.tenant-connection-name");
        }
    }

    public function up(): void
    {
        Schema::create('governor_actions', function (Blueprint $table): void {
            $table->string('name')
                ->unique()
                ->primary();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('governor_actions');
    }
}
