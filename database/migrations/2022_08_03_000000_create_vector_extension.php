<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
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

        try {
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
        } catch (Throwable $e) {
            // Managed Postgres (e.g. Laravel Cloud) may not allow CREATE EXTENSION for the app user.
            // Skip so deploy succeeds; enable the vector extension at the infrastructure level if needed.
            if (str_contains($e->getMessage(), 'permission denied') || str_contains($e->getMessage(), '42501')) {
                return;
            }
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        try {
            DB::statement('DROP EXTENSION IF EXISTS vector');
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'permission denied') || str_contains($e->getMessage(), '42501')) {
                return;
            }
            throw $e;
        }
    }
};
