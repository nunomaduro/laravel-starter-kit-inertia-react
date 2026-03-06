<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        try {
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
        } catch (Throwable $throwable) {
            // Managed Postgres (e.g. Laravel Cloud) may not allow CREATE EXTENSION for the app user.
            // Skip so deploy succeeds; enable the vector extension at the infrastructure level if needed.
            if (str_contains($throwable->getMessage(), 'permission denied') || str_contains($throwable->getMessage(), '42501')) {
                return;
            }

            throw $throwable;
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        try {
            DB::statement('DROP EXTENSION IF EXISTS vector');
        } catch (Throwable $throwable) {
            if (str_contains($throwable->getMessage(), 'permission denied') || str_contains($throwable->getMessage(), '42501')) {
                return;
            }

            throw $throwable;
        }
    }
};
