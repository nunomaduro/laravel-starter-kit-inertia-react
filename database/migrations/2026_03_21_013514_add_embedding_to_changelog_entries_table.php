<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('changelog_entries', function (Blueprint $table): void {
            $table->vector('embedding', 1536)->nullable();
            $table->rawIndex('embedding vector_cosine_ops', 'changelog_entries_embedding_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('changelog_entries', function (Blueprint $table): void {
            $table->dropIndex('changelog_entries_embedding_idx');
            $table->dropColumn('embedding');
        });
    }
};
