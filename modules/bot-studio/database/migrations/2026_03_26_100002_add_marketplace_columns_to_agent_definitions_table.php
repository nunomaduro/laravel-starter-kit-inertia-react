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
            $table->decimal('average_rating', 2, 1)->default(0)->after('total_messages');
            $table->unsignedInteger('review_count')->default(0)->after('average_rating');
            $table->unsignedInteger('install_count')->default(0)->after('review_count');
            $table->string('category', 50)->nullable()->after('install_count');

            $table->index('category');
            $table->index('average_rating');
            $table->index('install_count');
        });
    }

    public function down(): void
    {
        Schema::table('agent_definitions', function (Blueprint $table): void {
            $table->dropIndex(['category']);
            $table->dropIndex(['average_rating']);
            $table->dropIndex(['install_count']);
            $table->dropColumn(['average_rating', 'review_count', 'install_count', 'category']);
        });
    }
};
