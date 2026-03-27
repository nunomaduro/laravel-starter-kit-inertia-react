<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_dates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->date('date');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->unique(['room_type_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_dates');
    }
};
