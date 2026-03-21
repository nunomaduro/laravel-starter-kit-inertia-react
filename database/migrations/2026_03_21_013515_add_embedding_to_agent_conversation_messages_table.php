<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        Schema::table('agent_conversation_messages', function (Blueprint $table): void {
            $table->vector('embedding', 1536)->nullable();
        });

        DB::statement('CREATE INDEX IF NOT EXISTS agent_conversation_messages_embedding_idx ON agent_conversation_messages USING hnsw (embedding vector_cosine_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        Schema::table('agent_conversation_messages', function (Blueprint $table): void {
            $table->dropIndex('agent_conversation_messages_embedding_idx');
            $table->dropColumn('embedding');
        });
    }
};
