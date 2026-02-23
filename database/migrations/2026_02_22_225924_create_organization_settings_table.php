<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('group');
            $table->string('name');
            $table->json('payload');
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();

            $table->unique(['organization_id', 'group', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_settings');
    }
};
