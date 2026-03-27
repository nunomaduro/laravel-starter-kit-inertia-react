<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_type_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->unsignedInteger('price_per_night');
            $table->timestamps();

            $table->unique(['room_type_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_type_prices');
    }
};
