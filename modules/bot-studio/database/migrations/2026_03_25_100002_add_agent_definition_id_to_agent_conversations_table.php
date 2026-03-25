<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_conversations', function (Blueprint $table): void {
            $table->foreignId('agent_definition_id')
                ->nullable()
                ->after('organization_id')
                ->constrained('agent_definitions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agent_conversations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('agent_definition_id');
        });
    }
};
