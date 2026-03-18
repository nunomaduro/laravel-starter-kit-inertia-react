<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credits', function (Blueprint $table): void {
            $table->schemalessAttributes('extra_attributes');
        });
        Schema::table('organizations', function (Blueprint $table): void {
            $table->schemalessAttributes('extra_attributes');
        });
        Schema::table('pages', function (Blueprint $table): void {
            $table->schemalessAttributes('extra_attributes');
        });
    }

    public function down(): void
    {
        Schema::table('credits', function (Blueprint $table): void {
            $table->dropColumn('extra_attributes');
        });
        Schema::table('organizations', function (Blueprint $table): void {
            $table->dropColumn('extra_attributes');
        });
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn('extra_attributes');
        });
    }
};
