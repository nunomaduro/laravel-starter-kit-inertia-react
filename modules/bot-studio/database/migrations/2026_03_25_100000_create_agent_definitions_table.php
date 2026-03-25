<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_definitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->text('description')->nullable();
            $table->string('avatar_path', 255)->nullable();
            $table->text('system_prompt');
            $table->string('model', 50)->default('gpt-4o-mini');
            $table->decimal('temperature', 2, 1)->default(0.7);
            $table->integer('max_tokens')->default(4096);
            $table->json('enabled_tools')->default('[]');
            $table->json('knowledge_config')->default('{}');
            $table->json('conversation_starters')->default('[]');
            $table->json('wizard_answers')->nullable();
            $table->string('visibility')->default('organization');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_template')->default(false);
            $table->unsignedBigInteger('total_conversations')->default(0);
            $table->unsignedBigInteger('total_messages')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'slug']);
            $table->index('is_template');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_definitions');
    }
};
