<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_installs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_definition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('installed_definition_id')->nullable()->constrained('agent_definitions')->nullOnDelete();
            $table->foreignId('installed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['organization_id', 'agent_definition_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_installs');
    }
};
