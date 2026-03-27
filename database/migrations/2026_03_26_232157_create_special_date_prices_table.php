<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_date_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('price_per_night');
            $table->string('label')->nullable();
            $table->timestamps();

            $table->unique(['room_type_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_date_prices');
    }
};
