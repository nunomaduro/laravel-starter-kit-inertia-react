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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('color', 7)->nullable()->after('avatar')->comment('Hex color label e.g. #4f46e5');
            $table->json('tags')->nullable()->after('color')->comment('Array of string tags');
            $table->unsignedBigInteger('position')->nullable()->after('onboarding_completed')->comment('Sort order for drag-to-reorder');
        });

        // Initialise position to current id so reorder works immediately
        DB::statement('UPDATE users SET position = id WHERE position IS NULL');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['phone', 'color', 'tags', 'position']);
        });
    }
};
