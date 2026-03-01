<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_table_audit_log', function (Blueprint $table): void {
            $table->id();
            $table->string('table_name');
            $table->string('action'); // inline_edit, toggle, reorder, bulk_*
            $table->string('row_id')->nullable();
            $table->string('column')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['table_name', 'created_at']);
            $table->index(['table_name', 'row_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_table_audit_log');
    }
};
