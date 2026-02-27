<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streaks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId(config('level-up.user.foreign_key'))->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained('streak_activities')->cascadeOnDelete();
            $table->integer('count')->default(1);
            $table->timestamp('activity_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streaks');
    }
};
