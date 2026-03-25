<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_table_saved_views', function (Blueprint $table): void {
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('is_shared')->default(false);
            $table->boolean('is_system')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['organization_id', 'is_shared']);
        });
    }

    public function down(): void
    {
        Schema::table('data_table_saved_views', function (Blueprint $table): void {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['created_by']);
            $table->dropIndex(['organization_id', 'is_shared']);
            $table->dropColumn(['organization_id', 'is_shared', 'is_system', 'created_by']);
        });
    }
};
