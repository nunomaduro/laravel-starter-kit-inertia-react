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

        Schema::table('changelog_entries', function (Blueprint $table): void {
            $table->vector('embedding', 1536)->nullable();
        });

        DB::statement('CREATE INDEX IF NOT EXISTS changelog_entries_embedding_idx ON changelog_entries USING hnsw (embedding vector_cosine_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        Schema::table('changelog_entries', function (Blueprint $table): void {
            $table->dropIndex('changelog_entries_embedding_idx');
            $table->dropColumn('embedding');
        });
    }
};
