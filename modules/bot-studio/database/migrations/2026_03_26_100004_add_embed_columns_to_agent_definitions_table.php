<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_definitions', function (Blueprint $table): void {
            $table->boolean('embed_enabled')->default(false)->after('is_template');
            $table->json('embed_theme')->default('{}')->after('embed_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('agent_definitions', function (Blueprint $table): void {
            $table->dropColumn(['embed_enabled', 'embed_theme']);
        });
    }
};
