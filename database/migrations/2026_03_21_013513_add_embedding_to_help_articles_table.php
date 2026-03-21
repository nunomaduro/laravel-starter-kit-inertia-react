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
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        Schema::table('help_articles', function (Blueprint $table): void {
            $table->vector('embedding', 1536)->nullable();
            $table->rawIndex('embedding vector_cosine_ops', 'help_articles_embedding_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        Schema::table('help_articles', function (Blueprint $table): void {
            $table->dropIndex('help_articles_embedding_idx');
            $table->dropColumn('embedding');
        });
    }
};
