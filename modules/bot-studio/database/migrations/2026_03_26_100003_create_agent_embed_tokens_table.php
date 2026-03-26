<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_embed_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('agent_definition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('name', 100);
            $table->json('allowed_domains')->default('[]');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedBigInteger('request_count')->default(0);
            $table->unsignedInteger('rate_limit_per_minute')->default(30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_embed_tokens');
    }
};
