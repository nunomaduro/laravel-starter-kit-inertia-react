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

        Schema::create('model_embeddings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('embeddable_type', 50);
            $table->unsignedBigInteger('embeddable_id');
            $table->unsignedInteger('chunk_index')->default(0);
            $table->vector('embedding', (int) config('ai.embeddings.dimensions', 1536));
            $table->string('content_hash', 64);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['embeddable_type', 'embeddable_id', 'chunk_index'], 'model_embeddings_unique');
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        Schema::dropIfExists('model_embeddings');
    }
};
