<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('agent_definition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('rating');
            $table->text('review')->nullable();
            $table->timestamps();

            $table->unique(['agent_definition_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_reviews');
    }
};
