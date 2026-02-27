<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_flags', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->morphs('flaggable');
            $table->index(['name', 'flaggable_id', 'flaggable_type']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_flags');
    }
};
