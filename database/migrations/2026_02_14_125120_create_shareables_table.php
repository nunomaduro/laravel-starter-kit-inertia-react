<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shareables', function (Blueprint $table): void {
            $table->id();

            $table->morphs('shareable');
            $table->morphs('target');
            $table->string('permission')->default('view');
            $table->foreignId('shared_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['shareable_type', 'shareable_id', 'target_type', 'target_id'],
                'shareables_unique_share'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shareables');
    }
};
