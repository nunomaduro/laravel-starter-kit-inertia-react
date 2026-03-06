<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_user', function (Blueprint $table): void {
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->timestamp('joined_at')->useCurrent();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->primary(['organization_id', 'user_id']);
            $table->index('user_id');
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX organization_user_unique_default ON organization_user(user_id) WHERE is_default = 1');
        } elseif ($driver === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX organization_user_unique_default ON organization_user(user_id) WHERE is_default = true');
        }

        // MySQL: no partial unique index; application logic ensures only one default per user
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        try {
            if ($driver === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS organization_user_unique_default');
            } elseif ($driver === 'pgsql') {
                DB::statement('DROP INDEX IF EXISTS organization_user_unique_default');
            } elseif ($driver === 'mysql' || $driver === 'mariadb') {
                DB::statement('DROP INDEX organization_user_unique_default ON organization_user');
            }
        } catch (Throwable) {
            // Index may not exist
        }

        Schema::dropIfExists('organization_user');
    }
};
