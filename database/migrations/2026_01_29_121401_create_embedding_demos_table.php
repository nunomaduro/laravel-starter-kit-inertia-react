<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        Schema::create('embedding_demos', function (Blueprint $table): void {
            $table->id();
            $table->string('content');
            $table->vector('embedding', 3)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        Schema::dropIfExists('embedding_demos');
    }
};
