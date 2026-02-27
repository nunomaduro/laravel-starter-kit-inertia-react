<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LevelUp\Experience\Models\Activity;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streak_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId(config('level-up.user.foreign_key'))->constrained(config('level-up.user.users_table'))->cascadeOnDelete();
            $table->foreignIdFor(Activity::class)->constrained('streak_activities');
            $table->integer('count')->default(1);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streak_histories');
    }
};
