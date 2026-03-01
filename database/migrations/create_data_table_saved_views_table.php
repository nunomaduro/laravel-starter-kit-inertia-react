<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_table_saved_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('table_name');
            $table->string('name');
            $table->json('filters')->nullable();
            $table->string('sort')->nullable();
            $table->json('columns')->nullable();
            $table->json('column_order')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'table_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_table_saved_views');
    }
};
