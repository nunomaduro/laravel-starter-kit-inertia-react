<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('help_articles', function (Blueprint $table): void {
            $table->dropIndex(['is_featured']);
            $table->dropColumn('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('help_articles', function (Blueprint $table): void {
            $table->boolean('is_featured')->default(false)->after('is_published');
            $table->index('is_featured');
        });
    }
};
