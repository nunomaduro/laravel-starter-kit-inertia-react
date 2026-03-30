<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mail_templates', function (Blueprint $table): void {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
            $table->boolean('is_default')->default(false)->after('is_active');

            $table->index('organization_id');
            $table->index(['event', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::table('mail_templates', function (Blueprint $table): void {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            $table->dropIndex(['event', 'organization_id']);
            $table->dropColumn(['organization_id', 'is_default']);
        });
    }
};
